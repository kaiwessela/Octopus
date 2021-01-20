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
	public string $id;
	public ?object $primary_object;
	public ?object $secondary_object;

	private bool $new;
	private bool $empty;
	private bool $disabled;

	const UNIQUE = true;

	use DataObjectTrait;


	function __construct() {
		$this->new = false;
		$this->empty = true;
		$this->disabled = false;
	}

	public function generate(DataObject $object) : void {
		$this->req('empty');

		$this->generate_id();

		$this->set_object($object);

		$this->set_new();
		$this->set_empty(false);
	}

	public function load(DataObject $object1, DataObject $object2, array $data = []) : void {
		$this->req('empty');

		$this->set_object($object1);
		$this->set_object($object2);

		$this->set_empty(false);
	}

	private function already_exists(string $primary_id, string $secondary_id) : bool {
		$pdo = $this->open_pdo();

		$s = $pdo->prepare($this::EXISTS_QUERY);
		if(!$s->execute(['id1' => $primary_id, 'id2' => $secondary_id])){
			throw new DatabaseException($s);
		} else {
			return $s->rowCount() >= 1;
		}
	}

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
		$primary_id = $data['primary_id'] ?? $data[$this::PRIMARY_ALIAS . '_id'] ?? null;
		$secondary_id = $data['secondary_id'] ?? $data[$this::SECONDARY_ALIAS . '_id'] ?? null;

		if(!$this->is_new() && $id != $this->id){
			$errors->push(new IdentifierMismatchException('id', $id, $this));
		}

		if(!empty($this->primary_object) && $primary_id != $this->primary_object->id){
			if($this->primary_object->is_new()){ // NOTE this is a hotfix. work out how this can be done better.
				$primary_id = $this->primary_object->id;
			} else {
				$errors->push(new IdentifierMismatchException('primary_id', $primary_id, $this));
			}
		}

		if(!empty($this->secondary_object) && $secondary_id != $this->secondary_object->id){
			if($this->secondary_object->is_new()){
				$secondary_id = $this->secondary_object->id;
			} else {
				$errors->push(new IdentifierMismatchException('secondary_id', $secondary_id, $this));
			}
		}

		if($this->is_new()){
			if(empty($this->primary_object)){
				$primary_obj = $this->get_primary_prototype();

				try {
					$primary_obj->pull($primary_id);
					$this->primary_object = $primary_obj;
				} catch(EmptyResultException $e){
					$errors->push(new RelationNonexistentException($primary_id, get_class($primary_obj), 'primary_id'));
				}
			} else if(empty($this->secondary_object)){
				$secondary_obj = $this->get_secondary_prototype();

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
			$value = $data[$fieldname] ?? null;
			$required = $fielddef['required'] ?? null;
			$pattern = $fielddef['pattern'] ?? null;
			$type = $fielddef['type'] ?? null;

			if(empty($value) && !$required){
				$this->$fieldname = null;
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


	public function export() : DataObjectRelation { // TODO add description
		if($this->is_empty()){
			return null;
		}

		$this->disabled = true;
		return $this;

		// $export = [
		// 	'id' => $this->id,
		// 	'primary_id' => $this->primary_object->id,
		// 	'secondary_id' => $this->secondary_object->id
		// ];
		//
		// $export[$this::PRIMARY_ALIAS . '_id'] = $this->primary_object->id;
		// $export[$this::SECONDARY_ALIAS . '_id'] = $this->secondary_object->id;
		//
		// return $export;
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
