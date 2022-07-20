<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use \PDOException;
use \Exception;

/*
Idee:
EntityList pullen, aber bestimmtes relatum als WHERE
und die Relationship rechts/links ran joinen

SELECT * FROM posts
LEFT JOIN postcolumnrelations ON postcolumnrelations.post_id = posts.id
LEFT JOIN columns ON columns.id = postcolumnrelations.column_id
WHERE column.id = :id

sodass die contextuals erhalten bleiben

also eigentlich umgekehrt wie es jetzt läuft.
jetzt muss man eine column pullen, wenn man ihre posts will.
man soll aber die posts pullen und die column joinen, falls man die posts will.

FRAGE: Kann man dann den ganzen Unsinn mit LIMIT bei single Entity pull weglassen?


Dann müsste das RelationshipAttribute je nachdem eine RelationshipList oder nur eine Relationship enthalten

*/


abstract class EntityList {
	protected Entity $prototype;
	protected array $entities; # an array of the Entities this list contains [entity_id => entity, ...]

	protected int $total_count;

	const ENTITY_CLASS = ''; # the fully qualified name of the concrete Entity class whose instances this list contains

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(DatabaseAccess $db) {
		if(!is_subclass_of(static::ENTITY_CLASS, Entity::class)){
			throw new Exception('Invalid EntityList class: constant ENTITY_CLASS must describe a subclass of Entity.');
		}

		$this->db = &$db;

		$class = static::ENTITY_CLASS;
		$this->prototype = new $class($this);
	}


	public function &get_db() : DatabaseAccess {
		return $this->db;
	}


	### INITIALIZATION AND LOADING METHODS

	# Download data of multiple entities from the database and load these entities into this list.
	# @param $limit: how many entities to pull (SQL LIMIT)
	# @param $offset: the number of entities to be skipped (SQL OFFSET)
	# @param $options: additional, custom pull options
	final public function pull(?int $limit = null, ?int $offset = null, array $attributes = [], array $conditions = [], array $order = []) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$request = new SelectRequest($this->prototype);
		$this->prototype->build_pull_request($request, $attributes);

		$request->set_condition($this->prototype->resolve_pull_conditions($conditions));
		$request->set_order($this->prototype->resolve_pull_order($order));
		$request->set_limit($limit, $offset);

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		try {
			$c = $this->db->prepare($request->get_count_query());
			$c->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $c);
		}

		$this->total_count = (int) $c->fetch()['total'];
		$this->load($s->fetchAll());
	}


	# Load data of multiple entities from the database into Entity objects and load them into this list.
	# @param $data: rows of entity data from a database request's response
	final public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$this->entities = [];

		$datasets = [];
		$last_id = null;
		$i = -1;
		foreach($data as $row){
			if($last_id !== $row[$this->prototype->get_main_identifier_attribute()->get_result_column()]){
				$i++;
				$datasets[$i] = [];
			}

			$datasets[$i][] = $row;
			$last_id = $row[$this->prototype->get_main_identifier_attribute()->get_result_column()];
		}

		foreach($datasets as $dataset){
			$entity = clone $this->prototype;
			$entity->load($dataset);
			$this->entities[$entity->get_main_identifier_attribute()->get_value()] = $entity;
		}
	}


	# Return the total amount of entities, matching the constraints given on pull(), that are listed in the database.
	final public function count_total() : int {
		return $this->total_count;
	}


	### OUTPUT METHODS

	# Transform this list into an array, containing its arrayified (transformed) entities. (compare --> Entity)
	public function arrayify() : array|null {
		$result = [];
		foreach($this->entities as $entity){
			$result[] = $entity->arrayify();
		}

		return $result;
	}


	# Return an entity in this list
	# @param $index_or_id: list index or id of the entity
	final public function &get(string $id) : ?Entity {
		return $this->entities[$id] ?? null;
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
}
?>
