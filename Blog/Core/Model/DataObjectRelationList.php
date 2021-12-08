<?php // CODE ??, COMMENTS --, IMPORTS --
namespace Blog\Core\Model;

abstract class DataObjectRelationList {
	protected ?DataObject $pivot;
	protected array $relations;

	protected array $deletions;

	const RELATION_CLASS; # the fully qualified name of the concrete DORelation class whose instances this list contains

	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	# ==== CONSTRUCTION METHODS ==== #

	function __construct() { // TODO
		$this->cycle = new Cycle([
			['root', 'construct'],
			// TODO
		]);

		$this->cycle->start();

		$this->relations = [];
		$this->deletions = [];
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
	final public function load(array $data, ?DataObject $pivot = null) : void {
		$this->cycle->check_step('init/load');

		$this->pivot = $pivot; // NOTE: there is no validation for this value

		foreach($data as $row){
			$relation = new {$this::RELATION_CLASS}(); # initialize a new relation
			$relation->load($row, $pivot); # load the relation with the row data
			$this->relations[$relation->id] = $relation; # write the relation into this list
		}

		$this->cycle->step('init/load');
	}


	# ==== EDITING METHODS ==== #

	# the receive_input function of a RelationList works quite differently than that of a DataObject. in order for a
	# relation in this list to be altered, a string value 'action' has to be included in the input, which can have the
	# following contents: ignore (not altering anything) | new (adding new relation) | edit | delete
	# @param $input: [['action' => action string, 'input' => relation input data array], ...]
	final public function receive_input(array $input) : void {
		$this->cycle->check_step('edit');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing inputs)
		$errors = new InputFailedException();

		# loop through the input array
		foreach($input as $index => $field){
			$action = $field['action'];
			$data = $field['input'];

			if($action === 'new'){ # a new relation should be created and added
				$relation = new {$this::RELATION_CLASS}();

				$relation->create();

				if(!is_null($this->pivot)){
					$relation->set_object($this->pivot);
				}

				try {
					$relation->receive_input($data);
				} catch(InputFailedException $e){
					$errors->merge($e, "relation{$index}");
				}

				try {
					$this->add($relation);
				} catch(RelationCollisionException $e){
					$errors->push($e, "relation{$index}");
				}

			} else if($action === 'edit' || $action === 'delete'){
				$relation = $this->get($data['id']); # returns reference

				if(is_null($relation)){ // FIXME
					$errors->push(new RelationObjectNotFoundException(new PropertyDefinition('(unnamed)', $class), $data['id']), 'relation_'.$index);
				}

				if($action === 'edit'){
					try {
						$relation->receive_input($data);
					} catch(InputFailedException $e){
						$errors->merge($e, 'relation_'.$index);
					}

				} else { # action===delete
					$this->remove($relation);
				}
			} else if($action !== 'ignore'){
				// TODO maybe throw an exception
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
			throw new Exception(); // TODO exception wrong class (maybe TypeError)
		}

		if(!is_null($this->pivot) && $relation->get_object($this->pivot::class)?->id !== $this->pivot->id){
			throw new Exception(); // exception different base object
		}

		// TODO check unique (general, also for DataObjectRelation.php)

		$this->relations[$relation->id] = $relation;
	}


	final public function remove(DataObjectRelation|string $relation, bool $quiet = true) : void {
		if(is_string($relation)){
			$id = $relation;
		} else {
			$id = $relation?->id;
		}

		if(!isset($this->relations[$id])){
			if($error_on_null){
				throw new Exception();
			} else {
				return;
			}
		}

		$this->deletions[$id] = $this->relations[$id];
		unset($this->relations[$id]);
	}


	# ==== STORING AND DELETING METHODS ==== #

	final public function push() : bool {
		$this->cycle->check_step('store/delete');

		$request_performed = false;

		foreach($this->relations as $id => $_){
			$request_performed |= $this->relations[$id]->push();
		}

		foreach($this->deletions as $id => $_){
			$this->deletions[$id]->delete();
			unset($this->deletions[$id]);
			$request_performed = true;
		}

		return $request_performed;
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
