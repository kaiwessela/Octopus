<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\AttributeDefinition;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\CountRequest;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\FlowControl\Flow;
use PDOException;
use Exception;

// TODO explaination

abstract class RelationshipList {
	protected array $relationships;

	protected array $deletions;

	protected Entity $context;
	protected int $total_count;

	const RELATION_CLASS = ''; # the fully qualified name of the Relationship class whose instances this list contains

	protected Flow $flow; # this class uses the Flow class to control the order of its method calls. see there for more.


	### CONSTRUCTION METHODS

	function __construct(Entity &$context) {
		$this->context = &$context;

		$this->relationships = [];
		$this->deletions = [];

		$this->flow = new Flow([
			['root', 'constructed'],
			['constructed', 'loaded'],
			['loaded', 'counted'],
			['loaded', 'edited'],
			['loaded', 'storing'],
			['loaded', 'freezing'],
			['counted', 'edited'],
			['counted', 'storing'],
			['counted', 'freezing'],
			['edited', 'edited'],
			['edited', 'storing'],
			['edited', 'freezing'],
			['storing', 'stored'],
			['storing', 'freezing'],
			['stored', 'storing'],
			['stored', 'freezing'],
			['freezing', 'frozen']
		]);

		$this->flow->start();
	}


	### INITIALIZATION AND LOADING METHODS

	# Load data of multiple relationships from the database into Relationship objects and load them into this list.
	# @param $data: rows of relationship data from the database request's response
	final public function load(array $data) : void {
		$this->flow->check_step('loaded');

		foreach($data as $row){
			$cls = static::RELATION_CLASS;
			$relationship = new $cls($this->context); # initialize a new instance of this relationship class
			$relationship->load($row); # load the relationship
			$this->relationships[$relationship->id] = $relationship;
		}

		$this->flow->step('loaded');
	}


	# Return the total amount of entities that are listed in the database and match the constraints given on pull().
	# @param $force_request: whether reloading the count from the database is enforced. normally, a database request is
	# only performed on the first call. after that, a cached value is returned. this param overrides this
	final public function count_total(bool $force_request = false) : int {
		if(!isset($this->total_count) || $force_request){
			$this->flow->check_step('counted');

			$db = new DatabaseAccess();

			# use the context’s pull request to create a new CountRequest from
			$request = new CountRequest($this->context->pull_request);

			try {
				$s = $db->prepare($request->get_query());
				$s->execute($request->get_values());
			} catch(PDOException $e){
				throw new DatabaseException($e, $s);
			}

			$db->disable();

			$this->total_count = (int) $s->fetch()['total'];
			$this->flow->step('counted');
		}

		return $this->total_count;
	}


	# Return a JoinRequest for this relationship class that can be used by an entity's pull() method to include these
	# relationships in the entity
	# @param $on: The attribute on the calling relationship that identifies these relationships
	# paraphrased: LEFT JOIN [these relationships’ table] ON [these reelationships’ prefix].id = [on]
	public static function join(AttributeDefinition $on) : JoinRequest {
		// TODO explaination
		$identifier;
		$join;
		$columns = [];
		foreach(static::RELATION_CLASS::get_attribute_definitions() as $name => $definition){
			if($definition->supclass_is(Entity::class)){
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


	### EDITING METHODS

	# Edit multiple relationships of this list at once, for example to process POST data from an html form
	# works quite differently than that in Entity. in order for a relationship in this list to be altered, a string
	# value 'action' has to be included in the input, which can have the following contents:
	# ignore (not altering anything) | new (adding new relationship) | edit | delete
	# @param $data: an array of all relationships that should be altered:
	#	[
	# 		[attribute_name => new_attribute_value, ...]
	#	], …
	# @throws: AttributeValueExceptionList
	final public function receive_input(array $data) : void {
		$this->flow->check_step('edited');

		# create a new container exception that buffers and stores all AttributeValueExceptions
		# that occur during the editing of the relationships (i.e. invalid or missing inputs)
		$errors = new AttributeValueExceptionList();

		foreach($data as $index => $field){
			$action = $field['action'];
			$input = $field['data'];

			if($action === 'new'){ # create and add a new relationship
				$cls = static::RELATION_CLASS;
				$relation = new $cls($this->context);

				$relation->create(); # create a new relationship and let it handle the input

				try {
					$relation->receive_input($input);
				} catch(AttributeValueExceptionList $e){
					$errors->merge($e, "relationships.{$index}"); // TODO
				}

				$this->relationships[$relation->id] = $relation;

			} else if($action === 'edit' || $action === 'delete'){ # edit or delete an existing relationship
				$relation = &$this->relationships[$input['id']];

				if(empty($relation)){
					continue;
				}

				if($action === 'edit'){
					try {
						$relation->receive_input($input); # let the relationship handle the input data
					} catch(AttributeValueExceptionList $e){
						$errors->merge($e, "relationships.{$index}"); // TODO
					}

				} else if($action === 'delete'){
					$this->remove($relation->id); # remove the relationship
				}
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		$this->flow->step('edited');
	}


	# Add a new relationship by providing the joined entity.
	final public function add(Entity &$entity) : void {
		$this->flow->check_step('edited');

		$cls = static::RELATION_CLASS;
		$relation = new $cls($this->context);
		$relation->create();

		$relation->set_joined_entity($entity);

		$this->relationships[$relation->id] = $relation;

		$this->flow->step('edited');
	}


	# Remove a relationship from this list
	final public function remove(int|string $index_or_id) : bool {
		$this->flow->check_step('edited');

		if(!isset($this->relationships[$index_or_id])){
			return false; # if the relationship is not found, return false
		}

		$id = $this->relationships[$index_or_id]->id;
		$this->deletions[$id] = $this->relationships[$id];
		unset($this->relationships[$id]);

		$this->flow->step('edited');

		return true;
	}


	### STORING AND DELETING METHODS

	# Push (Insert/Update) the edited relationships, Delete the relationships that have been removed from the database
	# this method simply calls the Relationship::push() method on every relationship in this list
	# @return: whether a database request was performed (= whether the list or a relationship of it changed)
	final public function push() : bool {
		# if this relationship is currently in the storing process, do nothing. this prevents endless loops
		if($this->flow->is_at('storing')){
			return false;
		}

		$this->flow->step('storing');

		$request_performed = false;

		foreach($this->relationships as $id => $_){ # push all relationships in this list
			$request_performed |= $this->relationships[$id]->push();
		}

		foreach($this->deletions as $id => $_){ # delete the relationships that have been removed
			$this->deletions[$id]->delete();
			unset($this->deletions[$id]);
			$request_performed = true;
		}

		$this->flow->step('stored');

		return $request_performed;
	}


	### OUTPUT METHODS

	# Disable the database access for this list and all relationships it contanins.
	final public function freeze() : void {
		# if this relationship list is currently in the freezing process, do nothing. this prevents endless loops.
		if($this->flow->is_at('freezing')){
			return;
		}

		$this->flow->step('freezing');

		$this->db->disable();

		foreach($this->relationships as $index => $_){
			$this->relationships[$index]->freeze();
		}

		$this->flow->step('frozen');
	}


	# Transform this list into an array, containing its arrayified (transformed) relationships. (see --> Relationship)
	final public function arrayify() : array|null {
		# if this relationship list is already in the freezing process, return null. prevents endless loops
		# this also makes sure that this list only occurs once in the final array, preventing redundancies
		if($this->flow->is_at('freezing')){
			return null;
		}

		$this->flow->step('freezing');

		$this->db->disable();

		$result = [];
		foreach($this->relationships as $relation){
			$result[$relation->id] = $relation->arrayify();
		}

		$this->flow->step('frozen');

		return $result;
	}


	# Return the joined entity of a relationship in this list
	# @param $index_or_id: list index or id of the relationship (not of the entity!)
	final public function &get(int|string $index_or_id) : ?Entity {
		return $this->relationships[$index_or_id]?->get_joined_entity();
	}


	final public function each(callable $callback) { # function($value){}
		if(empty($this->relationships)){
			return;
		}

		foreach($this->relationships as $index => $_){
			$callback($this->relationships[$index]->get_joined_entity());
		}
	}


	final public function foreach(callable $callback) { # function($key, $value){}
		if(empty($this->relationships)){
			return;
		}

		foreach($this->relationships as $index => $_){
			$callback($index, $this->relationships[$index]->get_joined_object());
		}
	}


	final public function length() : int {
		return count($this->relationships);
	}


	final public function is_empty() : bool {
		return ($this->length() === 0);
	}
}
?>
