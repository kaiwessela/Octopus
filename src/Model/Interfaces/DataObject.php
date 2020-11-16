<?php


abstract class DataObject {

#			NAME		TYPE	REQUIRED	PATTERN				DB NAME		DB VALUE
	public $id;		#	str		*			[a-f0-9]{8}			=			=
	public $longid;	#	str		*			[a-z0-9-]{9,60}		=			=

	private $new;
	private $empty;


	abstract public function load($data);
	abstract public function export();
	abstract private function db_export();
	abstract private function push_children();


	public function pull(string $identifier) {
#	@action:
#	  - select one object from the database
#	  - call this->load to assign the received data to this object
#	@params:
#	  - $identifier: the id or longid of the requested object

		$pdo = self::open_pdo();
		$this->req('empty');

		$query = $this::PULL_QUERY;
		$values = ['id' => $identifier];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			$this->load($s->fetch());
		}
	}


	public function push() {
#	@action:
#	  - upload (insert/update) this object to the database
#	  - set this->new to false

		$pdo = self::open_pdo();
		$this->req('not empty');

		$this->push_children();

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
#	  - delete this object in database
#	  - set this->new to true

		$pdo = self::open_pdo();
		$this->req('not empty');
		$this->req('not new');

		$query = $this::DELETE_QUERY;
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->set_new();
		}
	}


	public function generate() {
#	@action:
#	  - turn this empty object into a new object
#	  - assign this object an id
#	  - set this->new to true
#	  - set this->empty to false

		$this->req('empty');
		$this->generate_id();
		$this->set_new();
		$this->set_empty(false);
	}


	protected function generate_id() {
#	@action:
#	  - generate a new id
#	  - assign the newly generated id to this object

		$this->id = bin2hex(random_bytes(4));
	}


	public function import($data) {
#

		$errors = new InputFailedException();

		if($this->is_new()){
			try {
				$this->import_longid($data['longid']);
			} catch(InputException $e){
				$errors->push($e);
			}
		} else {
			try {
				$this->import_check_id_and_longid($data['id'], $data['longid']);
			} catch(InputException $e){
				$errorlist->push($e);
			}
		}

		foreach($this::FIELDS as $fieldname => $fielddef){
			$value = $data[$fieldname];
			$required = $fielddef['required'];
			$pattern = $fielddef['pattern'];
			$type = $fielddef['type'];

			if($type instanceof DataObject){
				$obj = $type;

				if(!empty($data[$fieldname . '_id']) || !empty($data[$fieldname]['id'])){
					$id = $data[$fieldname . '_id'] ?? $data[$fieldname]['id'];

					try {
						$obj->pull($id);
					} catch(EmpyResultException $e){
						$error->push(new RelationNonexistentException($fieldname, $id, get_class($obj)));
						continue;
					}

					$this->$fieldname = $obj;
					continue;
				}

				if(empty($value) && $required){
					$errors->push(new MissingValueException($fieldname, get_class($obj)));
					continue;
				}

				try {
					$obj->generate();
					$obj->import($data);
				} catch(InputFailedException $e){
					$errors->merge($e, $fieldname);
					continue;
				}

				$this->fieldname = $obj;
				continue;

			} else if($type === 'array' && $fielddef['object_type'] instanceof DataObjectContainer){
				foreach($value as $val){

				}
			}

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

			} else if($type === 'custom'){
				$this->import_custom($fieldname, $data, &$errors); // TODO is this way of error handling the best one or should we use a try-catch?
				continue;

			} else {
				continue;
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->set_empty(false);
	}

}
?>
