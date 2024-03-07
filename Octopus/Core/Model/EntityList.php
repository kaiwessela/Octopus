<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Core\Model\Database\DatabaseAccess;
use Octopus\Core\Model\Database\Exceptions\DatabaseException;
use Octopus\Core\Model\Database\Requests\SelectRequest;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use PDOException;



class EntityList {
	protected ?Entity $context_entity;
	protected null|EntityReference|RelationshipsReference $context_attribute;

	protected Entity $prototype;
	protected array $entities; # an array of the Entities this list contains [entity_id => entity, ...]

	protected int $total_count;

	protected ?DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(Entity $prototype, ?DatabaseAccess $db = null) {
		$this->prototype = $prototype;

		$this->prototype->contextualize(list:$this);

		$this->db = &$db;

		$this->context_entity = null;
		$this->context_attribute = null;
	}


	final public function contextualize(?Entity $entity = null, null|EntityReference|RelationshipsReference $attribute = null) : void {
		// TODO check

		$this->context_entity = &$entity;
		$this->context_attribute = &$attribute;

		$this->prototype->contextualize(entity:$entity, list:$this, attribute:$attribute);
	}



	// final function old__construct(DatabaseAccess $db, string $entity_class) {
	// 	if(!is_subclass_of($entity_class, Entity::class)){
	// 		throw new Exception('Invalid EntityList class: constant ENTITY_CLASS must describe a subclass of Entity.');
	// 	}

	// 	$this->db = &$db;
	// 	$this->prototype = new $entity_class($this);
	// }


	# Returns the DatabaseAccess of this entity or its context, if it is dependent.
	final public function &get_db() : DatabaseAccess {
		$db = $this->db ?? $this->context_entity?->get_db();

		if(is_null($db)){
			throw new Exception('no db'); // TODO 
		}

		return $db;
	}


	### INITIALIZATION AND LOADING METHODS

	# Download data of multiple entities from the database and load these entities into this list.
	# @param $limit: how many entities to pull (SQL LIMIT)
	# @param $offset: the number of entities to be skipped (SQL OFFSET)
	# @param $options: additional, custom pull options
	final public function pull(?int $limit = null, ?int $offset = null, array $include_attributes = [], array $conditions = [], array $order_by = []) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$request = new SelectRequest($this->prototype);
		$this->prototype->resolve_pull_attributes($request, $include_attributes);
		$this->prototype->resolve_pull_order($request, $order_by);
		$request->where($this->prototype->resolve_pull_conditions($conditions));
		$request->limit($limit, $offset);

		try {
			$s = $this->get_db()->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		try {
			$c = $this->get_db()->prepare($request->get_count_query());
			$c->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $c);
		}

		$this->total_count = (int) $c->fetch()['total'];
		$this->load($s->fetchAll());
	}


	# Load data of multiple entities from the database into Entity objects and load them into this list.
	# @param $data: rows of entity data from a database request's response
	public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$this->entities = [];

		$datasets = [];
		$last_id = null;
		$i = -1;
		foreach($data as $row){
			if($last_id !== $row[$this->prototype->get_primary_identifier()->get_result_column()]){
				$i++;
				$datasets[$i] = [];
			}

			$datasets[$i][] = $row;
			$last_id = $row[$this->prototype->get_primary_identifier()->get_result_column()];
		}

		foreach($datasets as $dataset){
			$entity = clone $this->prototype;
			$entity->load($dataset);
			$this->entities[$entity->get_primary_identifier()->get_value()] = $entity;
		}
	}


	# Return the total amount of entities, matching the constraints given on pull(), that are listed in the database.
	final public function count_total() : int {
		return $this->total_count;
	}


	### OUTPUT METHODS

	# Transform this list into an array, containing its arrayified (transformed) entities. (compare --> Entity)
	public function arrayify() : array|null {
		if(!$this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$result = [];
		foreach($this->entities as $entity){
			$result[] = $entity->arrayify();
		}

		return $result;
	}


	final public function get_all() : array {
		return $this->entities;
	}


	# Return an entity in this list
	# @param $index_or_id: list index or id of the entity
	final public function &get(string|int $id) : ?Entity {
		if(is_int($id)){
			return array_values($this->entities)[$id] ?? null;
		} else {
			return $this->entities[$id] ?? null;
		}
	}


	final public function each(callable $callback) { # function($value){}
		if(empty($this->entities)){
			return;
		}

		foreach($this->entities as $entity){
			$callback($entity);
		}
	}


	final public function foreach(callable $callback) { # function($key, $value){}
		if(empty($this->entities)){
			return;
		}

		foreach($this->entities as $i => $entity){
			$callback($i, $entity);
		}
	}


	final public function length() : int {
		return count($this->entities);
	}


	final public function is_empty() : bool {
		return ($this->length() === 0);
	}


	final public function is_loaded() : bool {
		return isset($this->entities);
	}


	final public function is_independent() : bool {
		return isset($this->db);
	}
}
?>
