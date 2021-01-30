<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\DataObjectTrait;
use \Blog\Model\Abstracts\DataType;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;
use InvalidArgumentException;
use Exception;
use TypeError;

abstract class DataObject {
	public string $id;
	public string $longid;

	public ?int $count;

	private bool $new;
	private bool $empty;
	private bool $disabled;

	const IGNORE_PULL_LIMIT = false; // TODO why do we need this?

	const PROPERTIES = [];

	// TODO where do we need count queries? check this in all dataobjects


	use DataObjectTrait;


	abstract public function load(array $data) : void;
	abstract protected function db_export() : array;


	function __construct() {
		$this->set_new(false);
		$this->set_empty();
		$this->count = null;
		$this->disabled = false;
	}


	public function generate() : void {
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


	public function count() : int {
#	@requirements:
#	  - this object must be configured to contain a list of DatabaseObjects, else return null
#	@action:
#	  - return the number of objects of this type stored in the database
#	@return: integer

		$this->req('not empty');

		if(empty($this::COUNT_QUERY)){
			return null;
		}

		$pdo = $this->open_pdo();

		$s = $pdo->prepare($this::COUNT_QUERY);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else {
			$this->count = (int) $s->fetch()[0];
			return $this->count;
		}
	}


	public function pull(string $identifier, ?int $limit = null, ?int $offset = null, ?array $options = null) : void {
#	@action:
#	  - select one object from the database
#	  - call this->load to assign the received data to this object
#	@params:
#	  - $identifier: the id or longid of the requested object
#	  - $limit: the amount of objects to be selected
#	  - $offset: the amount of objects to be skipped at the beginning; ignored if $limit == null

		$this->req('empty');
		$pdo = $this->open_pdo();

		$values = ['id' => $identifier];

		if($this::IGNORE_PULL_LIMIT){
			$query = $this->pull_query(options: $options);
		} else {
			$query = $this->pull_query($limit, $offset, $options);
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


	protected function pull_query(?int $limit = null, ?int $offset = null, ?array $options = null) : string {
		$query = $this::PULL_QUERY;
		$query .= ($limit) ? (($offset) ? " LIMIT $offset, $limit" : " LIMIT $limit") : null;
		return $query;
	}


	public function push() : void {
#	@action:
#	  - upload (insert/update) this object and all its children to the database
#	  - set this->new to false

		$this->req('not empty');
		$pdo = $this->open_pdo();

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

		foreach($this::PROPERTIES as $property => $definition){
			if(is_subclass_of($definition, DataObject::class)){
				$this->$property?->push();
			} else if(is_subclass_of($definition, DataObjectRelationList::class)){
				$this->$property?->push();
			}
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


	protected function import_custom(string $property, array $data) : void {}


	public function import(array $data) : void {
#	@action:
#	  - import data received as array
#	@params:
#	  - data: array containing the data

		$errors = new InputFailedException();

		try {
			$this->import_id_and_longid($data['id'] ?? null, $data['longid']);
		} catch(InputFailedException $e){
			$errors->merge($e);
		}

		foreach($this::PROPERTIES as $property => $definition){
			$input = $data[$property] ?? null;

			if($definition == null){ # property is only defined by its PHP type
				$mode = 'as-is';
			} else if($definition == 'custom'){
				$mode = 'custom';
			} else if(!class_exists($definition)){ # definition is (at least should be) a regex
				$mode = 'regex';
			} else if(is_subclass_of($definition, DataType::class)){
				$mode = 'datatype';
			} else if(is_subclass_of($definition, DataObject::class)){
				$mode = 'dataobject';
			} else if(is_subclass_of($definition, DataObjectRelationList::class)){
				$mode = 'relationlist';
			} else if(is_subclass_of($definition, DataObjectList::class)){
				continue;
			} else {
				throw new Exception("Invalid definition '$definition' for $property.");
			}

			if($mode == 'custom'){
				try {
					$this->import_custom($property, $data);
				} catch(InputFailedException $e){
					$errors->merge($e, $property);
				} catch(InputException $e){
					$errors->push($e);
				}

				continue;
			}

			if(empty($input) && in_array($mode, ['as-is', 'regex', 'datatype'])){
				try {
					$this->$property = null;
				} catch(TypeError $e){
					$errors->push(new MissingValueException($property, $definition));
				}

				continue;
			}

			if($mode == 'regex' || $mode == 'as-is'){
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
					$this->$property = $input;
				} catch(TypeError $e){
					$errors->push(new IllegalValueException($property, $input, $definition));
				}

				continue;
			}

			if($mode == 'datatype'){
				try {
					$this->$property = $definition::import($input, $errors);
				} catch(InputException $e){
					$e->field = $property;
					$errors->push($e);
				}

				continue;
			}

			if($mode == 'dataobject'){
				if(!empty($input['id']) || !empty($data[$property.'_id'])){
					$input = $input['id'] ?? $data[$property.'_id'];
					$object = new $definition();

					try {
						$object->pull($input);
						$this->$property = $object;
					} catch(EmptyResultException $e){
						$errors->push(new RelationNonexistentException($property, $input, $definition));
					}

					continue;
				} else if(is_array($input)){
					$object = new $definition();

					try {
						$object->generate();
						$object->import($input);
						$this->$property = $object;
					} catch(InputFailedException $e){
						$errors->merge($e, $property);
					}

					continue;
				} else {
					try {
						$this->$property = null;
					} catch(TypeError $e){
						$errors->push(new MissingValueException($property, $definition));
					}

					continue;
				}
			}

			if($mode == 'relationlist') {
				if(empty($input)){
					continue;
				}

				if(empty($this->$property)){
					$this->$property = new $definition();
				}

				try {
					$this->$property->import($input, $this);
				} catch(InputFailedException $e){
					$errors->merge($e, $property);
				}

				continue;
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->set_empty(false);
	}


	public function export() : ?DataObject {
		if($this->is_empty()){
			return null;
		}

		$this->disabled = true;

		foreach($this as $property => $value){
			if($value instanceof DataObject){
				$value->export();
			} else if($value instanceof DataObjectList){
				$this->$property = $value->export();
			} else if($value instanceof DataObjectRelationList){
				$this->$property = $value->export($this::class);
			} else if(!empty($value) && is_array($value) && $value[0] instanceof DataObject){
				foreach($value as $obj){
					$obj->export();
				}
			}
		}

		return $this;
	}
}
?>
