<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\Traits\DBTrait;
use \Blog\Model\Abstracts\Traits\StateTrait;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use InvalidArgumentException;

abstract class DataObjectList {
	public array $objects;
	public int $count;

	const OBJECT_CLASS = null;

	use DBTrait;
	use StateTrait;

	const PAGINATABLE = true;

	private bool $new;
	private bool $empty;
	private bool $disabled;


	function __construct() {
		$this->new = false;
		$this->empty = true;
		$this->disabled = false;

		$this->objects = [];
	}


	public function pull_by_ids(array $idlist) : void {
		$this->require_empty();
		$pdo = $this->open_pdo();

		if(empty($idlist)){
			return;
		}

		$query = $this::SELECT_IDS_QUERY . ' (' . implode(', ', array_fill(0, count($idlist), '?')) . ')';
		$s = $pdo->prepare($query);

		if(!$s->execute(array_values($idlist))){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 0){
			$this->load($s->fetchAll());
		}
	}


	public function pull(?int $limit = null, ?int $offset = null, ?array $options = null) : void {
#	@action:
#	  - select multiple objects from the database
#	  - call this->load to assign the received data to this->objects
#	@params:
#	  - $limit: the amount of objects to be selected
#	  - $offset: the amount of objects to be skipped at the beginning; ignored if $limit == null

		$this->require_empty();
		$pdo = $this->open_pdo();

		$query = $this->pull_query($limit, $offset, $options);
		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$this->load($s->fetchAll());
		}
	}


	protected function pull_query(?int $limit = null, ?int $offset = null, ?array $options = null) : string {
		$query = $this::SELECT_QUERY;
		$query .= ($limit) ? (($offset) ? " LIMIT $offset, $limit" : " LIMIT $limit") : null;
		return $query;
	}


	public function load(array $data) : void {
#	@action:
#	  - call special functions to create DataObjects from $data
#	  - assign the list of created DataObjects to this->objects
#	  - set this->new and this->empty to false
#	@params:
#	  - $data: an array of arrays which contain the values for one DataObject

		$this->require_empty();

		$class = $this::OBJECT_CLASS;
		foreach($data as $row){
			$obj = new $class();
			$obj->load($row);
			$this->objects[$obj->id] = $obj;
		}

		$this->set_not_new();
		$this->set_not_empty();
	}


	public function load_from_relationlist(DataObjectRelationList $relationlist) : void {
		$this->require_empty();

		foreach($relationlist->relations as $relation){
			$this->objects[] = $relation->get_object($this::OBJECT_CLASS);
		}

		$this->set_not_empty();
	}


	public function get(string $id) : ?DataObject {
		return $this->objects[$id] ?? null;
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


	public function export() : void {
#	@action:
#	  - return an array of the results of the export function of each object
#	@return:
#		array of arrays which contain the exported data of one DataObject

		$this->disabled = true;

		if(empty($this->objects)){
			return;
		}

		foreach($this->objects as &$obj){
			$obj?->export();
		}
	}


	public function staticize() : ?array {
		$result = [];

		foreach($this->objects as $object){
			$result[] = $object->staticize(norelations:true);
		}

		return $result;
	}


	public function count() : int {
		if(empty($this->count)){
			$pdo = $this->open_pdo();

			$s = $pdo->prepare($this::COUNT_QUERY);
			if(!$s->execute()){
				throw new DatabaseException($s);
			} else {
				$this->count = (int) $s->fetch()[0];
			}
		}

		return $this->count;
	}


	public function amount() : int {
		return count($this->objects);
	}


	public function empty() : bool {
		return ($this->amount() == 0);
	}
}
?>
