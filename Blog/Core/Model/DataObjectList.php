<?php
namespace Blog\Core\Model;

# What is a DataObjectList?
# A DataObjectList is pretty much what the name suggests: A list of DataObjects of the same class.
#


abstract class DataObjectList {
	protected array $objects; # an array of the dataobjects this list contains [object_id => object, ...]

	protected ?SelectRequest $pull_request;
	protected int $total_count;

	const OBJECT_CLASS; # the fully qualified name of the concrete DataObject class whose instances this list contains

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	### CONSTRUCTION METHODS

	final function __construct() {
		// TODO check OBJECT_CLASS

		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'constructed'],
			['constructed', 'loaded'],
			['loaded', 'counted'], // TEMP
			['counted', 'frozen'], // TEMP
			['loaded', 'frozen']
		]);

		$this->cycle->start();

		$this->objects = [];
	}


	# ==== INITIALIZATION AND LOADING METHODS ==== #

	# this function downloads object data from the database and fills this list with these dataobjects
	# @param $limit: how many objects to pull (sql LIMIT)
	# @param $offset: the number of objects that are skipped (sql OFFSET)
	# @param $options: TODO
	final public function pull(?int $limit = null, ?int $offset = null, ?array $options = null) : void {
		$this->cycle->check_step('loaded');

		$request = new SelectRequest();

		foreach(static::OBJECT_CLASS::get_property_definitions() as $name => $definition){
			if($definition->supclass_is(DataObject::class)){
				$request->add_join({$definition->get_class()}::join());
			} else if(!$definition->supclass_is(DataObjectRelationList::class)){
				$request->add_property($definition);
			}
		}

		static::shape_select_request(&$request, $options);

		$request->set_limit($limit);
		$request->set_offset($offset);

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			# the database request has failed
			throw new DatabaseException($s);
		} else if($s->rowCount() === 0){
			# the response is empty, meaning that no objects were found
			throw new EmptyResultException($query);
		} else {
			# create objects using the load function
			$this->load($s->fetchAll());
			$this->pull_request = $request;
		}
	}


	protected static function shape_select_request(SelectRequest &$request, ?array $options) : void {}


	# this function downloads object data by a list of object ids (similar to the pull function).
	# in contrast to pull(), there is no exception if an id is not found.
	# @param $idlist: a list of ids of all objects that should be pulled
	final public function pull_by_ids(array $idlist) : void {
		$this->cycle->check_step('loaded');

		if(empty($idlist)){
			return;
		}

		$request = new SelectRequest($this::class);

		self::shape_select_request(&$request, []);

		$request->set_condition(new InCondition(static::OBJECT_CLASS::get_property_definitions()['id'], $idlist));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			throw new DatabaseException($s);
		} else if($s->rowCount() !== 0){
			$this->load($s->fetchAll());
			$this->pull_request = $request;
		}
	}


	# this function loads rows of object data from the database into objects and puts these objects into this list
	# @param $data: rows of dataobjects from a database request's response
	final public function load(array $data) : void {
		$this->cycle->check_step('loaded');

		foreach($data as $row){
			$object = new {$this::OBJECT_CLASS}(&$this); # initialize a new DataObject of this single-object class
			$object->load($row); # load the object, do not load relations
			$this->objects[$object->id] = $object;
		}

		$this->cycle->step('loaded');
	}


	final public function count_total(bool $force_request = false) : int { // TEMP/TEST
		if(!isset($this->total_count) || $force_request){
			$this->cycle->check_step('counted');

			$request = new CountRequest($this->pull_request);

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


	# ==== STADIUM 3 METHODS (output) ==== #

	# this function disables the database access for this object and all of the dataobjects it contanins.
	# see DataObject.php freeze function for more
	final public function freeze() : void {
		$this->cycle->step('frozen');
		$this->db->disable();

		foreach($this->objects as &$object){
			$object->freeze();
		}
	}


	# this function creates an array of the arrayified dataobjects it contains.
	# see DataObject.php arrayify function for more
	public function arrayify() : array {
		$this->cycle->step('frozen');
		$this->db->disable();

		$result = [];

		foreach($this->objects as $object){
			$result[] = $object->arrayify();
		}

		return $result;
	}


	# ==== STATIC METHODS ==== #

	public static function count() : int { // TODO
		$db = new DatabaseAccess();
		$db->enable();

		$s = $db->prepare($this::COUNT_QUERY);
		if(!$s->execute()){
			throw new DatabaseException($s);
		}

		$db->disable();
		unset($db);
		return (int) $s->fetch()[0];
	}


	# ==== GENERAL METHODS ==== #

	public function get(string $id) : ?DataObject {
		return &$this->objects[$id] ?? null;
	}


	public function each(callable $callback) { # function($value){}
		if(empty($this->objects)){
			return;
		}

		foreach($this->objects as $object){
			$callback($object);
		}
	}


	// FIXME this returns an int if callback is an each-type callback
	public function foreach(callable $callback) { # function($key, $value){}
		if(empty($this->objects)){
			return;
		}

		foreach($this->objects as $i => $object){
			$callback($i, $object);
		}
	}

	public function length() : int {
		return count($this->objects);
	}

	public function is_empty() : bool {
		return ($this->length() == 0);
	}
}
?>
