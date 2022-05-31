<?php
namespace Octopus\Core\Model;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Core\Model\Attributes\PropertyAttribute;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Database\DatabaseAccess;
use \Octopus\Core\Model\Database\Exceptions\DatabaseException;
use \Octopus\Core\Model\Database\Exceptions\EmptyResultException;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\CountRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\InList;
use \Octopus\Core\Model\Exceptions\CallOutOfOrderException;
use PDOException;
use Exception;

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
	protected array $entities; # an array of the Entities this list contains [entity_id => entity, ...]

	protected readonly ?SelectRequest $pull_request;
	protected int $total_count;

	protected ?Relationship $shared_relatum;

	const ENTITY_CLASS = ''; # the fully qualified name of the concrete Entity class whose instances this list contains

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.


	### CONSTRUCTION METHODS

	final function __construct(DatabaseAccess $db) {
		if(!is_subclass_of(static::ENTITY_CLASS, Entity::class)){
			throw new Exception('Invalid EntityList class: constant ENTITY_CLASS must describe a subclass of Entity.');
		}

		$this->entities = [];

		$this->db = &$db;
	}


	### INITIALIZATION AND LOADING METHODS

	# Download data of multiple entities from the database and load these entities into this list.
	# @param $limit: how many entities to pull (SQL LIMIT)
	# @param $offset: the number of entities to be skipped (SQL OFFSET)
	# @param $options: additional, custom pull options
	final public function pull(?int $limit = null, ?int $offset = null, array $options = []) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$request = new SelectRequest(static::ENTITY_CLASS::DB_TABLE);

		foreach(static::ENTITY_CLASS::get_attribute_definitions() as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # recursively join Entity attribute
			} else if($attribute instanceof PropertyAttribute){
				$request->add_attribute($attribute);
			}
		}

		static::shape_select_request($request, $options);

		$request->set_limit($limit);
		$request->set_offset($offset);

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		$this->pull_request = $request; # the pull request might be needed later for count requests
		$this->load($s->fetchAll()); // see next FIXME: this used to be below the RowCount

		if($s->rowCount() === 0){
			// FIXME this is a hotfix. maybe not even throw an exception
			throw new EmptyResultException($s);
		}

	}


	protected static function shape_select_request(SelectRequest &$request, array $options) : void {}


	# Download data of multiple entities from the database, based on a list of ids, and load them into this list.
	# in contrast to pull(), there is no exception if an id is not found.
	# @param $idlist: a list of ids of all entities that should be pulled
	final public function pull_by_ids(array $idlist) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		if(empty($idlist)){
			return;
		}

		$request = new SelectRequest(static::ENTITY_CLASS::DB_TABLE);

		foreach(static::ENTITY_CLASS::get_attribute_definitions() as $name => $attribute){
			if($attribute instanceof EntityAttribute){
				$request->add_join($attribute->get_class()::join(on:$attribute)); # recursively join Entity attribute
			} else if($attribute instanceof PropertyAttribute){
				$request->add_attribute($attribute);
			}
		}

		static::shape_select_request($request, []);

		$request->set_condition(new InList(static::ENTITY_CLASS::get_property_definitions()['id'], $idlist));

		try {
			$s = $this->db->prepare($request->get_query());
			$s->execute($request->get_values());
		} catch(PDOException $e){
			throw new DatabaseException($e, $s);
		}

		if($s->rowCount() === 0){
			return;
		}

		$this->pull_request = $request; # the pull request might be needed later for count requests
		$this->load($s->fetchAll());
	}


	# Load data of multiple entities from the database into Entity objects and load them into this list.
	# @param $data: rows of entity data from a database request's response
	final public function load(array $data) : void {
		if($this->is_loaded()){
			throw new CallOutOfOrderException();
		}

		$shared_relatum = null; // IDEA
		foreach($data as $row){
			$cls = $this::ENTITY_CLASS;
			$entity = new $cls($this, $this->db); # initialize a new instance of this entity class
			$entity->load($row, $shared_relatum); # load the entity
			$this->entities[$entity->id] = $entity;
		}
	}


	# Return the total amount of entities that are listed in the database and match the constraints given on pull().
	# @param $force_request: whether reloading the count from the database is enforced. normally, a database request is
	# only performed on the first call. after that, a cached value is returned. this param overrides this
	final public function count_total(bool $force_request = false) : int {
		if(!isset($this->total_count) || $force_request){
			if(!isset($this->pull_request)){
				throw new CallOutOfOrderException();
			}

			$request = new CountRequest($this->pull_request); # create a CountRequest from the former PullRequest

			try {
				$s = $this->db->prepare($request->get_query());
				$s->execute($request->get_values());
			} catch(PDOException $e){
				throw new DatabaseException($e, $s);
			}

			$this->total_count = $s->fetch()['total'];
		}

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
		return isset($this->request); // TEMP
	}
}
?>
