<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\Traits\DBTrait;
use \Blog\Model\Abstracts\Traits\StateTrait;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\IdentifierMismatchException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;

abstract class DataObjectRelation {
	public string $id;


	const UNIQUE = true;
	const OBJECTS = [];
	const PROPERTIES = [];


	use DBTrait;
	use StateTrait;


	function __construct() {
		private bool $new = false;
		private bool $empty = true;
		private bool $disabled = false;
	}


	public function generate(/*DataObject*/ $object) : void {
		$this->require_empty();
		$this->id = bin2hex(random_bytes(4));
		$this->set_new();
	}


	abstract public function load(array $data, /*DataObject*/ $object) : void;


	public function push() : void {
#	@action:
#	  - upload (insert/update) this object to the database
#	  - set this->new to false

		$this->require_not_empty();
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
			$this->set_not_new();
		}
	}


	public function delete() : void {
#	@action:
#	  - delete this object in the database
#	  - set this->new to true

		$this->require_not_empty();
		$this->require_not_new();
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


		if(!$this->is_new() && $data['id'] != $this->id){
			$errors->push(new IdentifierMismatchException('id', $data['id'], $this));
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


	public function export(string $perspective) : void {
		$this->disabled = true;

		foreach($this::OBJECTS as $property => $class){
			if($perspective == $class){
				$this->$property = null;
			}
		}
	}


	public function staticize(string $perspective) : ?array {
		$result = [
			'id' => $this->id
		];

		foreach($this::OBJECTS as $property => $class){
			if($perspective != $class){
				$result[$property] = $this->$property->staticize(norelations:true);
			}
		}

		foreach($this::PROPERTIES as $property => $definition){
			if(is_object($this->$property)){
				$result[$property] = $this->$property->staticize();
			} else {
				$result[$property] = $this->$property;
			}
		}

		return $result;
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
