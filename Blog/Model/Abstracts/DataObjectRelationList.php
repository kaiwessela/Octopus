<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\Traits\DBTrait;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\RelationCollisionException;

abstract class DataObjectRelationList {
	protected DataObject $base_object;
	protected array $relations;

	protected array $deletions; // DEPRECATED
	protected array $updates; // DEPRECATED

	const RELATION_CLASS; # the fully qualified name of the concrete DORelation class whose instances this list contains

	# this class does not use the local/altered/synced states of DatabaseAccess!
	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	# ==== CONSTRUCTION METHODS ==== #

	function __construct(DataObject &$base_object) { // TODO
		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'construct'],
			// TODO
		]);

		$this->cycle->start();

		$this->relations = [];
	}


	# ==== INITIALIZATION AND LOADING METHODS ==== #
	# similar to a relation, a relationlist is always loaded using a base DataObject which is forwarded to each relation
	# to be set as the first object. the second object is then loaded using the relation's own load functions, usually
	# using database rows.
	# that means that a relationlist always has a 'perspective'. the first object of the relations it contains is always
	# the same one, and to this object, the relationlist provides a list of objects linked to it.

	# this function receives rows of multiple relation and object data from the database, initializes and loads
	# individual relations with it and appends them to this list. the base object $object is simply passed on to
	# the individual relation's load() function. see there for further documentation
	# the database request itself (the pull function) is always performed by the base object (using joins)
	# @param $data: array of rows from the database request's response
	# @param $object: the base object of the relations
	final public function load(array $data, DataObject &$object) : void {
		$this->cycle->check_step('init/load');

		$class = $this::RELATION_CLASS;

		foreach($data as $row){
			$relation = new $class(); # initialize a new relation
			$relation->load($row, $object); # load the relation with the row data
			$this->relations[$relation->id] = $relation; # write the relation into this list
		}

		$this->cycle->step('init/load');
	}


	# ==== EDITING METHODS ==== #

	# the receive_input function of a RelationList works quite differently than that of a DataObject. in order for a
	# relation in this list to be altered, a string value 'action' has to be included in the input, which can have the
	# following contents: ignore (not altering anything) | new (adding new relation) | edit | delete
	# @param $input: [['action' => action string, 'input' => relation input data array], ...]
	final public function receive_input(array $input, DataObject &$object) : void {
		$this->cycle->check_step('edit');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing inputs)
		$errors = new InputFailedException();

		foreach($input as $index => $field){
			$action = $field['action'];
			$data = $field['input'];
			$class = $this::RELATION_CLASS;

			if($action == 'new'){
				$relation = new $class();

				try {
					$relation->create($object);
					$relation->receive_input($data);
				} catch(InputFailedException $e){
					$errors->merge($e, 'relation_'.$index);
				}

				try {
					$this->add($relation);
				} catch(RelationCollisionException $e){
					$errors->push($e, 'relation_'.$index);
				}

			} else if($action == 'edit' || $action == 'delete'){
				$relation = $this->get($data['id']); # returns reference

				if(is_null($relation)){
					$errors->push(new RelationObjectNotFoundException(new PropertyDefinition('(unnamed)', $class), $data['id']), 'relation_'.$index);
				}

				if($action == 'edit'){
					try {
						$relation->receive_input($data);
					} catch(InputFailedException $e){
						$errors->merge($e, 'relation_'.$index);
					}

				} else { # action=delete
					$this->remove($relation);
				}
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->cycle->step('edit');
	}


	final public function add(DataObjectRelation $relation) : void {
		$this->cycle->check_step('edit');

		if($relation::class !== $this::RELATION_CLASS){
			// exception wrong class (maybe TypeError)
		}

		if($relation->get_object($this->base_object::class)?->id !== $this->base_object->id){
			// exception different base object
		}

		// TODO check unique (general, also for DataObjectRelation.php)

		$this->relations[$relation->id] = $relation; // TODO reference?
	}


	final public function remove(DataObjectRelation|string $relation, bool $error = false) : null|void {
		/*
		$this->deletions[] = $relation->id;
		unset($this->relations[$relation->id]);
		*/
	}


	public function import(array $data, DataObject $object) : void { // DEPRECATED
		$errors = new InputFailedException();

		foreach($data as $index => $relationdata){
			$action = $relationdata['action'];
			$class = $this::RELATION_CLASS;

			if($action == 'new'){
				$relation = new $class();

				try {
					$relation->generate($object);
					$relation->import($relationdata);
				} catch(InputFailedException $e){
					$errors->merge($e, $index);
					continue;
				}

				if($class::UNIQUE){
					$propname;
					foreach($class::OBJECTS as $nm => $cls){
						if($cls != $object::class){
							$propname = $nm;
						}
					}

					foreach($this->relations as $existing){
						if($existing->$propname->id == $relation->$propname->id){
							throw new RelationCollisionException($propname, '', $existing->id);
						}
					}
				}

				$this->relations[$relation->id] = $relation;
				$this->updates[] = $relation->id;

			} else if($action == 'edit') || $action == 'delete'){
				$relation = $this->relations[$relationdata['id']];

				if(!$relation instanceof DataObjectRelation){
					// TODO maybe exception
					continue;
				}

				if($action == 'edit'){
					try {
						$relation->import($relationdata);
					} catch(InputFailedException $e){
						$errors->merge($e, $index);
					}

					$this->updates[] = $relation->id;
					$this->relations[$relation->id] = $relation;

				} else if($action == 'delete'){
					$this->deletions[] = $relation->id;
					unset($this->relations[$relation->id]);
				}

			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}

	# ==== STORING AND DELETING METHODS ==== #

	final public function push() : null|void {
		$this->cycle->check_step('store/delete');

		$request_performed = false;

		foreach($this->relations as $id => $_){
			if($this->relations[$id]->push() !== null){
				$request_performed = true;
			}
		}

		foreach($this->deletions as $id => $_){
			$this->deletions[$id]->delete();
			unset($this->deletions[$id]);
			$request_performed = true;
		}

		if(!$request_performed){
			return null;
		}
	}

	public function push() : void { // DEPRECATED
		$pdo = $this->open_pdo();

		if(!empty($this->updates)){
			if(count($this->updates) == 1){
				$this->relations[$this->updates[0]]->push();
			} else {
				$valuestrings = [];
				$values = [];
				foreach($this->updates as $i => $id){
					$valuestrings[] = $this->db_valuestring($i);
					$values = array_merge($values, $this->db_values($i, $id));
				}

				$valuestring = implode(', ', $valuestrings);
				$query = str_replace('%VALUESTRING%', $valuestring, $this::PUSH_QUERY);

				$s = $pdo->prepare($query);
				if(!$s->execute($values)){
					throw new DatabaseException($s);
				} else {
					$this->updates = [];
				}
			}
		}

		if(!empty($this->deletions)){
			$idstrings = [];
			$values = [];
			foreach($this->deletions as $i => $id){
				$idstrings[] = $this->db_idstring($i);
				$values["id_$i"] = $id;
			}

			$idstring = implode(' OR ', $idstrings);
			$query = str_replace('%IDSTRING%', $idstring, $this::DELETE_QUERY);

			$s = $pdo->prepare($query);
			if(!$s->execute($values)){
				throw new DatabaseException($s);
			} else {
				$this->deletions = [];
			}
		}
	}


	# ==== OUTPUT METHODS ==== #

	final public function freeze() : void {
		$this->cycle->step('output');
		$this->db->disable();

		foreach($this->relations as &$relation){
			$relation->freeze();
		}
	}


	final public function arrayify() : ?array {
		$this->cycle->step('output');
		$this->db->disable();

		$result = [];

		foreach($this->relations as $relation){
			$result[] = $relation->arrayify();
		}

		return $result;
	}


	# ==== GENERAL METHODS ==== #


	final public function get(string $id) : ?DataObjectRelation {
		return &$this->relations[$id] ?? null;
	}


	public function each(callable $callback) { # function($value){}
		if(empty($this->relations)){
			return;
		}

		foreach($this->relations as &$relation){
			$callback($relation);
		}
	}


	public function foreach(callable $callback) { # function($key, $value){}
		if(empty($this->relations)){
			return;
		}

		for($i = 0; $i < count($this->relations); $i++){
			$callback($i, &$this->relations[$i])
		}
	}


	public function length() : int {
		return count($this->relations);
	}


	public function is_empty() : bool {
		return ($this->length() == 0);
	}

}
?>
