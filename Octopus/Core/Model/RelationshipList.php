<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attributes\Exceptions\AttributeValueExceptionList;
use Octopus\Core\Model\Database\Requests\Join;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\EntityList;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use Octopus\Core\Model\Relationship;

class RelationshipList extends EntityList {
	// protected Relationship $prototype;
	// protected array $relationships;

	protected array $deletions;

	// protected Entity $context;

	// protected const RELATION_CLASS = ''; # the fully qualified name of the Relationship class whose instances this list contains

	protected bool $is_complete;


	### CONSTRUCTION METHODS

	// function __construct(Entity $context, Relationship $prototype) {
	// 	$this->context = &$context;

	// 	$this->prototype = $prototype; // TODO improve

	// 	$this->relationships = [];
	// 	$this->deletions = [];
	// }


	// final public function join(array $include_attributes) : Join {
	// 	$request = new Join($this->prototype, $this->prototype->get_context_attribute(), $this->context->get_primary_identifier());
	// 	$this->prototype->resolve_pull_attributes($request, $include_attributes);
	// 	return $request;
	// }


	### INITIALIZATION AND LOADING METHODS

	# Load data of multiple relationships from the database into Relationship objects and load them into this list.
	# @param $data: rows of relationship data from the database request's response
	final public function load(array $data, bool $is_complete = false) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		foreach($data as $row){
			$relationship = clone $this->prototype;
			$relationship->load($row); # load the relationship

			if(isset($this->entities[$relationship->get_primary_identifier()->get_value()])){
				break;
			}

			$this->entities[$relationship->get_primary_identifier()->get_value()] = $relationship;
		}

		$this->is_complete = $is_complete;
	}


	### EDITING METHODS

	final public function receive_input(mixed $data) : void { // TODO
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		throw new Exception('not ready yet');

		$errors = new AttributeValueExceptionList();

		if(!$this->is_complete()){
			// error
		}

		if(!$this->is_distinct()){
			// error
		}

		if(is_null($data)){
			return;
		} else if(!is_array($data)){
			// error
		}

		foreach($data as $value){
			if(is_string($value)){
				$relatum_id = $value;
			} else if(is_array($value)){
				if(!isset($value['relatum'])){
					// error
				}

				$relatum_id = $value['relatum'];
			} else {
				// invalid
			}

			$relationship = $this->find_by_relatum($relatum_id);

			if(is_null($relationship)){
				$class = static::RELATION_CLASS;
				$relationship = new $class($this->context);
			}

			$relationship->edit($value);

			// find the relationship with this foreign entity
			// if not found, create a new one

			// edit the relationship
			// push it

			// delete the relationships that are not in $data
		}

		/*
		columns => [
			column1.id,
			column2.id,
			column3.id

			OR

			[
				column: id,
				other attributes
			],
			...
		]
		*/



		// $this->flow->check_step('edited');


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

				$this->entities[$relation->id] = $relation;

			} else if($action === 'edit' || $action === 'delete'){ # edit or delete an existing relationship
				$relation = &$this->entities[$input['id']];

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

		// $this->flow->step('edited');
	}


	final public function add(array $input) : string {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$relationship = clone $this->prototype;
		$relationship->create();
		$relationship->receive_input($input);

		$this->entities[$relationship->id] = $relationship;

		return $relationship->id;
	}


	# Remove a relationship from this list
	final public function remove(string $id) : bool {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		if(!isset($this->entities[$id])){
			return false; # if the relationship is not found, return false // TODO maybe exception
		}

		$this->deletions[$id] = $this->entities[$id];
		unset($this->entities[$id]);

		return true;
	}


	### STORING AND DELETING METHODS

	# Push (Insert/Update) the edited relationships, Delete the relationships that have been removed from the database
	# this method simply calls the Relationship::push() method on every relationship in this list
	# @return: whether a database request was performed (= whether the list or a relationship of it changed)
	final public function push() : bool {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$request_performed = false;

		foreach($this->entities as $id => $_){ # push all relationships in this list
			$request_performed |= $this->entities[$id]->push();
		}

		foreach($this->deletions as $id => $_){ # delete the relationships that have been removed
			$this->deletions[$id]->delete();
			unset($this->deletions[$id]);
			$request_performed = true;
		}

		return $request_performed;
	}


	### OUTPUT METHODS


	# Transform this list into an array, containing its arrayified (transformed) relationships. (see --> Relationship)
	final public function arrayify() : array|null {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$result = [];
		foreach($this->entities as $id => $relation){
			$result[$id] = $relation->arrayify();
		}

		return $result;
	}


	final public function is_complete() : bool {
		return $this->is_complete;
	}
}
?>
