<?php


abstract class DataObjectRelation {
	public $id;
	public $container;
	public $object;

	private $new;
	private $empty;

	const UNIQUE = true;


	function __construct() {
		$this->new = false;
		$this->empty = true;
	}

	public function generate(DatabaseObjectContainer $container) {
		$this->req('empty');

		$this->generate_id();
		$this->container = $container;

		$this->set_new();
		$this->set_empty(false);
	}

	public function load(DatabaseObjectContainer $container, DatabaseObject $object, $data = []) {
		$this->req('empty');

		$this->container = &$container;
		$this->object = &$object;

		$this->set_empty(false);
	}

	public function push() {

	}

	public function import($data) {
#	@params
#	  - data:
#		[
#			'object_id',
#			…
#		]

		// TODO check unique

		$errors = new InputFailedException();

		$object_id = $data['object_id'] ?? $data[$this::OBJECT_ALIAS . '_id'];

		if(!$this->is_new() && empty($data['object_id'])){
			$errors->push(new MissingValueException('object_id', 'DataObjectRelation->id'));

		} else if($this->is_new() && $data['object_id'] != $this->object->id){
			$errors->push(new IdentifierMismatchException('object_id', $data['object_id'], $this->object));

		} else {
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
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	function __get($name) {
#	@action:
#	  - create custom aliases for $object and $container

		if($name == $this::OBJECT_ALIAS){
			return $this->object;
		}

		if($name == $this::CONTAINER_ALIAS){
			return $this->container;
		}
	}


	function __set($name, $value) {
#	@action:
#	  - create custom aliases for $object and $container

		if($name == $this::OBJECT_ALIAS){
			$this->object = $value;
		}

		if($name == $this::CONTAINER_ALIAS){
			$this->container = $value;
		}
	}

}
?>
