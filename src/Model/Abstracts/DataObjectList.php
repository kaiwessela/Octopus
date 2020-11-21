<?php
namespace Blog\Model\Abstracts;
use Blog\Model\DataObjectTrait;
use Blog\Model\Exceptions\DatabaseException;
use Blog\Model\Exceptions\EmptyResultException;
use InvalidArgumentException;

abstract class DataObjectList {
	public $objects;
	public $count;

	private $new;
	private $empty;

	use DataObjectTrait;

	abstract protected static function load_each($data);


	public function pull(/*int*/$limit = null, /*int*/$offset = null) {
#	@action:
#	  - select multiple objects from the database
#	  - call this->load to assign the received data to this->objects
#	@params:
#	  - $limit: the amount of objects to be selected
#	  - $offset: the amount of objects to be skipped at the beginning; ignored if $limit == null

		$pdo = self::open_pdo();
		$this->req('empty');

		$query = $this::SELECT_QUERY;

		if($limit != null){
			if(!is_int($limit)){
				throw new InvalidArgumentException('Invalid argument: limit must be an integer.');
			}

			if($offset != null){
				if(!is_int($offset)){
					throw new InvalidArgumentException('Invalid argument: offset must be an integer.');
				}

				$query .= " LIMIT $offset, $limit";
			} else {
				$query .= " LIMIT $limit";
			}
		}

		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$this->load($s->fetchAll());
		}
	}


	public function load(array $data) {
#	@action:
#	  - call special functions to create DataObjects from $data
#	  - assign the list of created DataObjects to this->objects
#	  - set this->new and this->empty to false
#	@params:
#	  - $data: an array of arrays which contain the values for one DataObject

		$this->req('empty');

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

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export() {
#	@action:
#	  - return an array of the results of the export function of each object
#	@return:
#		array of arrays which contain the exported data of one DataObject

		$result = [];
		foreach($this->objects as $object){
			$result[] = $object->export();
		}
		return $result;
	}


	public function count() {
#	@action:
#	  - return the number of objects of this type stored in the database
#	  - store this number in this->count
#	@return:
#		integer

		$pdo = self::open_pdo();

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
