<?php
namespace Blog\Model\Abstract;
use Blog\Model\DataObjectTrait;
use Blog\Model\Abstract\DataObject;
use Blog\Model\Exceptions\DatabaseException;
use Blog\Model\Exceptions\EmptyResultException;
use Blog\Model\Exceptions\InputFailedException;
use Blog\Model\Exceptions\IdentifierMismatchException;
use Blog\Model\Exceptions\IllegalValueException;
use Blog\Model\Exceptions\MissingValueException;
use Blog\Model\Exceptions\RelationNonexistentException;

abstract class DataObjectRelation {
	public $id;
	public $primary_object;
	public $secondary_object;

	private $new;
	private $empty;

	const UNIQUE = true;

	use DataObjectTrait;


	function __construct() {
		$this->new = false;
		$this->empty = true;
	}

	public function generate(DataObject $object) {
		$this->req('empty');

		$this->generate_id();

		$this->set_object($object);

		$this->set_new();
		$this->set_empty(false);
	}

	public function load(DataObject $object1, DataObject $object2, $data = []) {
		$this->req('empty');

		$this->set_object($object1);
		$this->set_object($object2);

		$this->set_empty(false);
	}

	private function already_exists($primary_id, $secondary_id) {
		$pdo = $this->open_pdo();

		$s = $pdo->prepare($this::EXISTS_QUERY);
		if(!$s->execute(['id1' => $primary_id, 'id2' => $secondary_id])){
			throw new DatabaseException($s);
		} else {
			return $s->rowCount() >= 1;
		}
	}

	public function push() {
#	@action:
#	  - upload (insert/update) this object to the database
#	  - set this->new to false

		$this->req('not empty');
		$pdo = self::open_pdo();

		if($this->is_new()){
			$s = $pdo->prepare($this::INSERT_QUERY);
		} else {
			$s = $pdo->prepare($this::UPDATE_QUERY);
		}

		if(!$s->execute($this->db_export())){
			throw new DatabaseException($s);
		} else {
			$this->set_new(false);
		}
	}

	public function delete() {
#	@action:
#	  - delete this object in the database
#	  - set this->new to true

		$this->req('not empty');
		$this->req('not new');
		$pdo = self::open_pdo();

		$s = $pdo->prepare($this::DELETE_QUERY);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else {
			$this->set_new();
		}
	}

	public function import($data) {
		$errors = new InputFailedException();

		$id = $data['id'];
		$primary_id = $data['primary_id'] ?? $data[$this::PRIMARY_ALIAS . '_id'];
		$secondary_id = $data['secondary_id'] ?? $data[$this::SECONDARY_ALIAS . '_id'];

		if(!$this->is_new() && $id != $this->id){
			$errors->push(new IdentifierMismatchException('id', $id, $this));
		}

		if(!empty($this->primary_object) && $primary_id != $this->primary_object->id){
			$errors->push(new IdentifierMismatchException('primary_id', $primary_id, $this));
		}

		if(!empty($this->secondary_object) && $secondary_id != $this->secondary_object->id){
			$errors->push(new IdentifierMismatchException('secondary_id', $secondary_id, $this));
		}

		if($this->is_new()){
			if(empty($this->primary_object)){
				$primary_obj = $this::PRIMARY_PROTOTYPE;

				try {
					$primary_obj->pull($primary_id);
					$this->primary_object = $primary_obj;
				} catch(EmptyResultException $e){
					$errors->push(new RelationNonexistentException($primary_id, get_class($primary_obj), 'primary_id'));
				}
			} else if(empty($this->secondary_object)){
				$secondary_obj = $this::SECONDARY_PROTOTYPE;

				try {
					$secondary_obj->pull($secondary_id);
					$this->secondary_object = $secondary_obj;
				} catch(EmptyResultException $e){
					$errors->push(new RelationNonexistentException($secondary_id, get_class($secondary_obj), 'secondary_id'));
				}
			}
		}

		// TODO check for unique

		foreach($this::FIELDS as $fieldname => $fielddef){
			$value = $data[$fieldname];
			$required = $fielddef['required'];
			$pattern = $fielddef['pattern'];
			$type = $fielddef['type'];

			if(empty($value) && !$required){
				continue;

			} else if(empty($value) && $required){
				$errors->push(new MissingValueException($fieldname, $pattern ?? ''));
				continue;
			}


			if($type === 'string'){
				if(!empty($pattern) && !preg_match("/^$pattern$/", $value)){
					$errors->push(new IllegalValueException($fieldname, $value, $pattern));
					continue;
				}

				$this->$fieldname = $value;
				continue;

			} else if($type === 'integer'){
				if(!is_numeric($value)){
					$errors->push(new IllegalValueException($fieldname, $value, '[Integer]'));
					continue;
				}

				$this->$fieldname = (int) $value;
				continue;

			} else if($type === 'boolean'){
				$this->$fieldname = (bool) $value;
				continue;

			} else {
				continue;
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	function __get($name) {
#	@action:
#	  - create custom aliases for $primary_object and $secondary_object

		if($name == $this::PRIMARY_ALIAS){
			return $this->primary_object;
		}

		if($name == $this::SECONDARY_ALIAS){
			return $this->secondary_object;
		}
	}


	function __set($name, $value) {
#	@action:
#	  - create custom aliases for $primary_object and $secondary_object

		if($name == $this::PRIMARY_ALIAS){
			$this->primary_object = $value;
		}

		if($name == $this::SECONDARY_ALIAS){
			$this->secondary_object = $value;
		}
	}

}
?>
