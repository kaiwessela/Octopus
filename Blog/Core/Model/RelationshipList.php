<?php // CODE ??, COMMENTS --, IMPORTS --
namespace Octopus\Core\Model;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Cycle\Cycle;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use Exception;

abstract class DataObjectRelationList {
	protected DataObject $context;
	protected int $total_count;

	protected array $relations;

	protected array $deletions;

	const RELATION_CLASS = null; # the fully qualified name of the concrete DORelation class whose instances this list contains

	protected DatabaseAccess $db; // TEMP
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	# ==== CONSTRUCTION METHODS ==== #

	function __construct(DataObject &$context) {
		$this->context = &$context;

		$this->db = new DatabaseAccess();

		/* CYCLE:
		root
		constructed
		created | loaded
		*/

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
	final public function load(array $data, ?SelectRequest $pull_request) : void {
		$this->cycle->check_step('init/load');

		$this->pull_request = $pull_request;

		foreach($data as $row){
			$cls = static::RELATION_CLASS;
			$relation = new $cls($this->context); # initialize a new relation
			$relation->load($row); # load the relation with the row data
			$this->relations[$relation->id] = $relation; # write the relation into this list
		}

		$this->cycle->step('init/load');
	}


	final public function count_total(bool $force_request = false) : int { // TEMP/TEST
		if(!isset($this->total_count) || $force_request){
			$this->cycle->check_step('counted');

			$request = new CountRequest($this->context->pull_request);

			$s = $this->db->prepare($request->get_query());
			if(!$s->execute($request->get_values())){
				throw new DatabaseException($s);
			} else {
				$this->total_count = $s->fetch()['total'];
				$this->cycle->step('counted');
			}
		}

		return $this->total_count;
	}


	final public function count_total() : int { // TEMP
		$this->cycle->check_step('counted');

		$s = $this->db->prepare($this->pull_request->get_total_count_query());
		if(!$s->execute($request->get_values())){
			throw new DatabaseException($s);
		} else {
			$r = $s->fetch();
			$this->cycle->step('counted');
			return $r['total'];
		}
	}


	public static function join(PropertyDefinition $on) : JoinRequest {
		$identifier;
		$join;
		$columns = [];
		foreach(static::RELATION_CLASS::get_property_definitions() as $name => $definition){
			if($definition->supclass_is(DataObject::class)){
				if($definition->get_class()::DB_TABLE === $on->get_db_table()){
					$identifier = $definition;
				} else {
					$join = $definition;
				}
			} else {
				$columns[] = $definition;
			}
		}

		$request = new JoinRequest(static::RELATION_CLASS::DB_TABLE, $identifier, $on);

		foreach($columns as $column){
			$request->add_property($column);
		}

		$request->add_join($join->get_class()::join(on:$join));

		return $request;
	}


	# ==== EDITING METHODS ==== #

	# the receive_input function of a RelationList works quite differently than that of a DataObject. in order for a
	# relation in this list to be altered, a string value 'action' has to be included in the input, which can have the
	# following contents: ignore (not altering anything) | new (adding new relation) | edit | delete
	# @param $input: [['action' => action string, 'data' => relation input data array], ...]
	final public function receive_input(array $input) : void {
		$this->cycle->check_step('edit');

		# create a new container exception that buffers and stores all PropertyValueExceptions
		# that occur during the editing of the properties (i.e. invalid or missing inputs)
		$errors = new InputFailedException(); // TODO update

		# loop through the input array
		foreach($input as $index => $field){
			$action = $field['action'];
			$data = $field['data'];

			if($action === 'new'){ # a new relation should be created and added
				$cls = static::RELATION_CLASS;
				$relation = new $cls($this->context);

				$relation->create();

				try {
					$relation->receive_input($data);
				} catch(InputFailedException $e){
					$errors->merge($e, "relation{$index}");
				}

				$this->relations[$relation->id] = $relation;

				// TODO check unique

				// XXX DEPRECATED ---------------------------
				try {
					$this->add($relation);
				} catch(RelationCollisionException $e){
					$errors->push($e, "relation{$index}"); // prefix for push() deprecated
				}
				// xxx end ----------------------------------

			} else if($action === 'edit' || $action === 'delete'){
				if(empty($relation = &$this->relations[$data['id']])){
					$errors->push(new RelationObjectNotFoundException()); // TODO
				}

				if($action === 'edit'){
					try {
						$relation->receive_input($data);
					} catch(InputFailedException $e){
						$errors->merge($e, 'relation_'.$index);
					}

				} else if($action === 'delete'){
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


	final public function add(DataObject &$object) : void {
		$this->cycle->check_step('edit');

		$cls = static::RELATION_CLASS;
		$relation = new $cls($this->context);
		$relation->create();

		try {
			$relation->set_joined_object($object);
		} catch(Exception $e){
			throw $e; // TODO
		}

		// TODO check unique

		$this->relations[$relation->id] = $relation;
		$this->cycle->step('edit');
	}


	final public function remove(int|string $index_or_id, bool $quiet = true) : void {
		$this->cycle->check_step('edit');

		if(!isset($this->relations[$index_or_id])){
			if($quiet){
				return;
			}

			throw new Exception('not found'); // TODO
		}

		$id = $this->relations[$index_or_id]->id;
		$this->deletions[$id] = $this->relations[$id];
		unset($this->relations[$id]);
		$this->cycle->step('edit');
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

		foreach($this->relations as $index => $_){
			$this->relations[$index]->freeze();
		}
	}


	final public function arrayify() : array {
		$this->cycle->step('output');
		$this->db->disable();

		$result = [];

		foreach($this->relations as $relation){
			$result[$relation->id] = $relation->arrayify();
		}

		return $result;
	}


	# ==== GENERAL METHODS ==== #


	final public function &get(int|string $index_or_id) : ?DataObject { // id means the relation id, not the object id
		return $this->relations[$index_or_id]?->get_joined_object();
	}


	public function each(callable $callback) { # function($value){}
		if(empty($this->relations)){
			return;
		}

		foreach($this->relations as $index => $_){
			$callback($this->relations[$index]->get_joined_object());
		}
	}


	public function foreach(callable $callback) { # function($key, $value){}
		if(empty($this->relations)){
			return;
		}

		foreach($this->relations as $index => $_){
			$callback($index, $this->relations[$index]->get_joined_object());
		}
	}


	public function length() : int {
		return count($this->relations);
	}


	public function is_empty() : bool {
		return ($this->length() === 0);
	}

}
?>
