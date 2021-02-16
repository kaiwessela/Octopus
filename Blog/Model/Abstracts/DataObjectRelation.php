<?php
namespace Blog\Model\Abstracts;
use Blog\Model\DataObjectTrait;
use Blog\Model\Abstracts\DataObject;
use Blog\Model\Exceptions\DatabaseException;
use Blog\Model\Exceptions\EmptyResultException;
use Blog\Model\Exceptions\InputFailedException;
use Blog\Model\Exceptions\IdentifierMismatchException;
use Blog\Model\Exceptions\IllegalValueException;
use Blog\Model\Exceptions\MissingValueException;
use Blog\Model\Exceptions\RelationNonexistentException;

abstract class DataObjectRelation {

	// FIXME fix uninitialized properties

	public string $id;

	private bool $new;
	private bool $empty;
	private bool $disabled;

	const UNIQUE = true;
	const OBJECTS = [];
	const PROPERTIES = [];

	use DataObjectTrait;


	function __construct() {
		$this->new = false;
		$this->empty = true;
		$this->disabled = false;
	}

	public function generate(/*DataObject*/ $object) : void {
		$this->req('empty');

		$this->generate_id();

		$this->set_new();
		$this->set_empty(false);
	}


	abstract public function load(array $data, /*DataObject*/ $object) : void;


	public function push() : void {
#	@action:
#	  - upload (insert/update) this object to the database
#	  - set this->new to false

		$this->req('not empty');
		$pdo = $this->open_pdo();

		if($this->is_new()){
			$s = $pdo->prepare($this::INSERT_QUERY);
		} else if($this::UPDATE_QUERY == null){
			return;
		} else {
			$s = $pdo->prepare($this::UPDATE_QUERY);
		}

		if(!$s->execute($this->db_export())){
			throw new DatabaseException($s);
		} else {
			$this->set_new(false);
		}
	}


	public function delete() : void {
#	@action:
#	  - delete this object in the database
#	  - set this->new to true

		$this->req('not empty');
		$this->req('not new');
		$pdo = $this->open_pdo();

		$s = $pdo->prepare($this::DELETE_QUERY);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else {
			$this->set_new();
		}
	}


	public function import(array $data) : void {
		$errors = new InputFailedException();

		$id = $data['id'] ?? null;
		if(!$this->is_new() && $id != $this->id){
			$errors->push(new IdentifierMismatchException('id', $id, $this));
		}

		foreach($this::OBJECTS as $name => $class){
			if($this->$name?->is_new()){
				continue;
			}

			if($this->is_new() && empty($this->$name)){
				$obj = new $class();

				try {
					$obj->pull($data[$name.'_id'] ?? '');
					$this->$name = $obj;
				} catch(EmptyResultException $e){
					$errors->push(new RelationNonexistentException($data[$name.'_id'], $class, $name.'_id'));
				}

			} else if(!$this->is_new() && $data[$name.'_id'] != $this->$name->id){
				$errors->push(new IdentifierMismatchException($name.'_id', $data[$name.'_id'], $this));
			}
		}

		foreach($this::PROPERTIES as $property => $definition){
			$input = $data[$property] ?? null;

			if($definition == null){ # property is only defined by its PHP type
				$mode = 'as-is';
			} else if(!class_exists($definition)){ # definition is (at least should be) a regex
				$mode = 'regex';
			} else {
				throw new Exception("Invalid definition '$definition' for $property.");
			}

			if(empty($input)){
				try {
					$this->$property = null;
				} catch(TypeError $e){
					$errors->push(new MissingValueException($property, $definition));
				}

				continue;
			}

			if($mode == 'regex'){
				$regex = "/^$definition$/";
				if(preg_match($regex, null) === false){ # check if definition is a valid regex
					throw new Exception("Invalid regex for $property.");
				} else if(!preg_match($regex, $input)){
					$errors->push(new IllegalValueException($property, $input, $definition));
					continue;
				}
			}

			try {
				$this->$property = htmlspecialchars($input);
			} catch(TypeError $e){
				$errors->push(new IllegalValueException($property, $input, $definition));
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	public function export(?string $perspective) : void {
		$this->disabled = true;

		foreach($this::OBJECTS as $property => $class){
			if($perspective == $class){
				$this->$property = null;
			}
		}
	}


	public function get_object(string $class) : DataObject {
		$property = null;
		foreach($this::OBJECTS as $prop => $cls){
			if($class == $cls){
				$property = $prop;
			}
		}

		if(empty($property)){
			throw new Exception('relationlist does not contain this object.');
		}

		$object = $this->$property;

		foreach($this::PROPERTIES as $prop => $def){
			$object->$prop = $this->$prop;
		}

		return $object;
	}
}
?>
