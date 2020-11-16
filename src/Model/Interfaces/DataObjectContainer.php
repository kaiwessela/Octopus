<?php


abstract class DataObjectContainer {

#			NAME		TYPE	REQUIRED	PATTERN				DB NAME		DB VALUE
	public $id;		#	str		*			[a-f0-9]{8}			=			=
	public $longid;	#	str		*			[a-z0-9-]{9,60}		=			=
	public $objects;

	private $new;
	private $empty;

	private $relationlist;


	abstract public function push();
	abstract public function load();
	abstract public function count();
	abstract public function import();
	abstract public function export();

	#--------------

	/* IMPORT DATA AUFBAU (bsp. Column)

	{
		id: string,
		longid: string,
		name: string,
		description: string,
		posts: {
			1: {  // update
				action: edit
				relation_id: string
				post_id: string
			}

			1: {  // insertion
				action: new
				post_id: string
			}

			1: {  // deletion
				action: delete
				relation_id: string
			}

		}
	}

	*/


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
						$errors->push(new RelationNonexistentException($fieldname, $id, get_class($obj)));
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

			} else if($type === 'relationlist'){
				try {
					$this->relationlist->import($value); // TODO do not forget to push
				} catch(InputFailedException $e){
					$errors->merge($e, $fieldname);
					continue;
				}

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




	#--------------


	public function pull($identifier, $limit = null, $offset = null) {
		$this->req('empty');
		$pdo = self::open_pdo();

		$query = $this::PULL_QUERY;
		$values = ['identifier' => $identifier];

		if($limit != null){
			if(!is_int($limit)){
				throw new InvalidArgumentException('Invalid argument: limit must be an integer.');
			}

			if($offset != null){
				if(!is_int($offset)){
					throw new InvalidArgumentException('Invalid argument: offset must be an integer.');
				}

				$query .= " LIMIT $offset, $limit";
			} else {
				$query .= " LIMIT $limit";
			}
		}

		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$this->load($s->fetchAll());
		}
	}

	public function count() {
		$this->req('not empty');
		$pdo = self::open_pdo();

		$s = $pdo->prepare($this::COUNT_QUERY);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	protected function generate_id() {
		$this->id = bin2hex(random_bytes(4));
	}
}
?>
