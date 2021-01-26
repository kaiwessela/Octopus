<?php
namespace Blog\Model\Abstracts;
use Blog\Model\DataObjectTrait;
use Blog\Model\Abstracts\DataObject;
use Blog\Model\Exceptions\DatabaseException;
use Blog\Model\Exceptions\EmptyResultException;
use InvalidArgumentException;

abstract class DataObjectList {
	public $objects;
	public int $count;

	private bool $new;
	private bool $empty;
	private bool $disabled;

	const OBJECT_CLASS = null;


	use DataObjectTrait;


	function __construct() {
		$this->objects = [];

		$this->set_new(false);
		$this->set_empty();
		$this->disabled = false;
	}


	public function pull(?int $limit = null, ?int $offset = null, ?array $options = null) : void {
#	@action:
#	  - select multiple objects from the database
#	  - call this->load to assign the received data to this->objects
#	@params:
#	  - $limit: the amount of objects to be selected
#	  - $offset: the amount of objects to be skipped at the beginning; ignored if $limit == null

		$pdo = $this->open_pdo();
		$this->req('empty');

		$query = $this->pull_query($limit, $offset, $options);

		// IDEA use subqueries to pull relations as well (maybe optionally)

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

		$this->req('empty');

		/* row buffer to use on sql statements with relation joins. currently not necessary.
		$i = 0;
		$last_id = null;
		$row_buffer = [];

		foreach($data as $row){ // IDEA sql 'group by'
			if($row[0] == $last_id){
				$row_buffer[$i][] = $row;
			} else {
				$i++;
				$row_buffer[$i] = [];
				$row_buffer[$i][] = $row;
				$last_id = $row[0];
			}
		}

		foreach($row_buffer as $data){
			$this->objects[] = $this::load_each($data);
		}
		*/

		$class = $this::OBJECT_CLASS;
		foreach($data as $row){
			$obj = new $class();
			$obj->load($row);
			$this->objects[] = $obj;
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export() : array {
#	@action:
#	  - return an array of the results of the export function of each object
#	@return:
#		array of arrays which contain the exported data of one DataObject

		if($this->is_empty()){
			return null;
		}

		$this->disabled = true;

		foreach($this->objects as $obj){
			$obj->export();
		}

		return $this->objects;
	}


	public function count() : int {
#	@action:
#	  - return the number of objects of this type stored in the database
#	  - store this number in this->count
#	@return:
#		integer

		$pdo = $this->open_pdo();

		$s = $pdo->prepare($this::COUNT_QUERY);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			$this->count = (int) $s->fetch()[0];
			return $this->count;
		}
	}


	function __get($name) {
#	@action:
#	  - create custom alias for $objects

		if($name == $this::OBJECTS_ALIAS){
			return $this->objects;
		}
	}


	function __set($name, $value) {
#	@action:
#	  - create custom alias for $objects

		if($name == $this::OBJECTS_ALIAS){
			$this->objects = $value;
		}
	}

}
?>
