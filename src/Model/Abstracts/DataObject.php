<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\DataObjectTrait;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;
use InvalidArgumentException;

abstract class DataObject {

#			NAME		TYPE	REQUIRED	PATTERN				DB NAME		DB VALUE
	public $id;		#	str		*			[a-f0-9]{8}			=			=
	public $longid;	#	str		*			[a-z0-9-]{9,60}		=			=

	public $count;

	private $new;
	private $empty;

	private $relationlist;

	const IGNORE_PULL_LIMIT = false;


	use DataObjectTrait;


	abstract public function load($data);
	abstract public function export();
	abstract protected function db_export();


	function __construct() {
		$this->set_new(false);
		$this->set_empty();
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
		$this->set_empty();
	}


	public function count() {
#	@requirements:
#	  - this object must be configured to contain a list of DatabaseObjects, else return null
#	@action:
#	  - return the number of objects of this type stored in the database
#	@return: integer

		$this->req('not empty');

		if(empty($this::COUNT_QUERY)){
			return null;
		}

		$pdo = self::open_pdo();

		$s = $pdo->prepare($this::COUNT_QUERY);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else {
			$this->count = (int) $s->fetch()[0];
			return $this->count;
		}
	}


	public function pull(string $identifier, /*int*/$limit = null, /*int*/$offset = null) {
#	@action:
#	  - select one object from the database
#	  - call this->load to assign the received data to this object
#	@params:
#	  - $identifier: the id or longid of the requested object
#	  - $limit: the amount of objects to be selected
#	  - $offset: the amount of objects to be skipped at the beginning; ignored if $limit == null

		$this->req('empty');
		$pdo = self::open_pdo();

		$query = $this::PULL_QUERY;
		$values = ['id' => $identifier];

		if($limit != null && !$this::IGNORE_PULL_LIMIT){
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

		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$this->load($s->fetchAll());
		}
	}


	public function push() {
#	@action:
#	  - upload (insert/update) this object and all its children to the database
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

		$this->push_children();
		$this->relationlist->push();
	}


	protected function push_children() {
#	@action:
#	  - placeholder function to be used by objects to push their children

		return;
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


	protected function import_custom() {
		return;
	} // TODO


	public function import($data) {
#	@action:
#	  - import data received as array
#	@params:
#	  - data: array containing the data

		$errors = new InputFailedException();

		try {
			$this->import_id_and_longid($data['id'], $data['longid']);
		} catch(InputFailedException $e){
			$errors->merge($e);
		}

		foreach($this::FIELDS as $fieldname => $fielddef){
			$value = $data[$fieldname];
			$required = $fielddef['required'];
			$pattern = $fielddef['pattern'];
			$type = $fielddef['type'];


			if(empty($value) && !$required && $type !== 'custom'){
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

				$this->$fieldname = $this->relationlist->get_objects(); // TODO this does not work
				continue;

			} else if($type === 'custom'){
				$this->import_custom($fieldname, $data, $errors); // TODO is this way of error handling the best one or should we use a try-catch?
				continue;

			} else {
				try {
					$class = '\Blog\Model\DataObjects\\' . $type;
					$obj = new $class();
				} catch(Exception $e){
					continue;
				}

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

				$this->$fieldname = $obj;
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
