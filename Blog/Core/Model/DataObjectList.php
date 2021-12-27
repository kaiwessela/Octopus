<?php
namespace Blog\Core\Model;

# What is a DataObjectList?
# A DataObjectList is pretty much what the name suggests: A list of DataObjects of the same class.
#


abstract class DataObjectList {
	protected array $objects; # an array of the dataobjects this list contains [object_id => object, ...]
	protected int $total_count;

	const OBJECT_CLASS; # the fully qualified name of the concrete DataObject class whose instances this list contains

	protected DatabaseAccess $db; # this class uses the DatabaseAccess class to access the database. see there for more.
	protected Cycle $cycle; # this class uses the Cycle class to control the order of its actions. see there for more.


	### CONSTRUCTION METHODS

	final function __construct() {
		$this->db = new DatabaseAccess(); # prepare a connection to the database

		$this->cycle = new Cycle([
			['root', 'constructed'],
			['constructed', 'loaded']








			['root', 'constructed'],
			['constructed', 'loaded'],
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

		$request = new SelectRequest($this::class, true); // TEMP (2nd arg)
		$request->set_condition($this->get_pull_condition($options));
		$request->set_limit($limit);
		$request->set_offset($offset);

		$order = $this->get_pull_order($options);
		$request->set_order($order['by'] ?? null, $order['desc'] ?? false);

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
		}
	}


	# this function downloads object data by a list of object ids (similar to the pull function).
	# in contrast to pull(), there is no exception if an id is not found.
	# @param $idlist: a list of ids of all objects that should be pulled
	final public function pull_by_ids(array $idlist) : void {
		$this->cycle->check_step('loaded');
		$this->db->enable();

		if(empty($idlist)){
			return;
		}

		$request = new SelectRequest($this::class);
		$request->set_condition(new InCondition(self::$properties['id'], $idlist));

		$s = $this->db->prepare($request->get_query());
		if(!$s->execute($request->get_values())){
			throw new DatabaseException($s);
		} else if($s->rowCount() !== 0){
			$this->load($s->fetchAll());
		}
	}


	protected function get_pull_condition(?array $options) : ?Condition {
		return null;
	}

	protected function get_pull_order(?array $options) : ?array {
		return null;
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
