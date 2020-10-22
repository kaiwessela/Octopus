<?php
namespace Blog\Model\DatabaseObjects;
use \Blog\Config\Config;
use \Blog\Model\DatabaseObject;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InvalidInputException;
use InvalidArgumentException;

class Event extends DatabaseObject {
	public $title;
	public $organisation;
	public $timestamp;
	public $location;
	public $description;
	public $cancelled;

	/* @inherited
	public $id;
	public $longid;

	private $new;
	private $empty;
	*/


	public function pull($identifier) {
		$pdo = self::open_pdo();

		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM events WHERE event_id = :id OR event_longid = :id';
		$values = ['id' => $identifier];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			$this->load($s->fetch());
		}
	}

	public static function pull_all($limit = null, $offset = null) {
		$pdo = self::open_pdo();

		$query = 'SELECT * FROM events ORDER BY event_timestamp DESC';

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
			$res = [];
			while($r = $s->fetch()){
				$obj = new Event();
				$obj->load($r);
				$res[] = $obj;
			}
			return $res;
		}
	}

	public static function pull_future() {
		$pdo = self::open_pdo();

		$query = 'SELECT * FROM events WHERE event_timestamp >= UNIX_TIMESTAMP(CURDATE()) ORDER BY event_timestamp DESC';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$res = [];
			while($r = $s->fetch()){
				$obj = new Event();
				$obj->load($r);
				$res[] = $obj;
			}
			return $res;
		}
	}

	public function load($data) {
		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$this->id = $data['event_id'];
		$this->longid = $data['event_longid'];
		$this->title = $data['event_title'];
		$this->organisation = $data['event_organisation'];
		$this->timestamp = (int) $data['event_timestamp'];
		$this->location = $data['event_location'];
		$this->description = $data['event_description'];
		$this->cancelled = (boolean) $data['event_cancelled'];

		$this->empty = false;
		$this->new = false;
	}

	public static function count() {
		$pdo = self::open_pdo();

		$query = 'SELECT COUNT(*) FROM events';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	public function push() {
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'organisation' => $this->organisation,
			'timestamp' => $this->timestamp,
			'location' => $this->location,
			'description' => $this->description,
			'cancelled' => (int) $this->cancelled
		];

		if($this->is_new()){
			$query = 'INSERT INTO events (event_id, event_longid, event_title, event_organisation,
				event_timestamp, event_location, event_description, event_cancelled) VALUES (:id,
				:longid, :title, :organisation, :timestamp, :location, :description, :cancelled)';

			$values['longid'] = $this->longid;
		} else {
			$query = 'UPDATE events SET event_title = :title, event_organisation = :organisation,
				event_timestamp = :timestamp, event_location = :location, event_description =
				:description, event_cancelled = :cancelled WHERE event_id = :id';
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = false;
		}
	}

	public function import($data) {
		if($this->is_new()){
			$this->import_longid($data['longid']);
		} else {
			$this->import_check_id_and_longid($data['id'], $data['longid']);
		}

		$this->import_title($data['title']);
		$this->import_organisation($data['organisation']);
		$this->import_timestamp($data['timestamp']);
		$this->import_location($data['location']);
		$this->import_description($data['description']);
		$this->import_cancelled($data['cancelled'] ?? false);

		$this->empty = false;
	}

	public function delete() {
		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		if($this->is_new()){
			throw new WrongObjectStateException('not new');
		}

		$pdo = self::open_pdo();

		$query = 'DELETE FROM events WHERE event_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = true;
		}
	}

	public function export() {
		if($this->is_empty()){
			return null;
		}

		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->title = $this->title;
		$obj->organisation = $this->organisation;
		$obj->timestamp = $this->timestamp;
		$obj->location = $this->location;
		$obj->description = $this->description;
		$obj->cancelled = $this->cancelled;

		return $obj;
	}

	public function import_title($title) {
		if(!isset($title)){
			throw new InvalidInputException('title', '.{1,50}');
		} else if(!preg_match('/^.{1,50}$/', $title)){
			throw new InvalidInputException('title', '.{1,50}', $title);
		} else {
			$this->title = $title;
		}
	}

	public function import_organisation($organisation) {
		if(!isset($organisation)){
			throw new InvalidInputException('organisation', '.{1,40}');
		} else if(!preg_match('/^.{1,40}$/', $organisation)){
			throw new InvalidInputException('organisation', '.{1,40}', $organisation);
		} else {
			$this->organisation = $organisation;
		}
	}

	public function import_timestamp($timestamp) {
		if(!isset($timestamp)){
			throw new InvalidInputException('timestamp', '[unix timestamp]');
		} else if(!is_numeric($timestamp)){
			throw new InvalidInputException('timestmap', '[unix timestamp]', $timestamp);
		} else {
			$this->timestamp = (int) $timestamp;
		}
	}

	public function import_location($location) {
		if(!isset($location)){
			$this->location = null;
		} else if(!preg_match('/^.{0,60}$/', $location)){
			throw new InvalidInputException('location', '.{0,60}', $location);
		} else {
			$this->location = $location;
		}
	}

	public function import_description($description) {
		$this->description = $description;
	}

	public function import_cancelled($cancelled = false) { // TEMP TEMP TEMP
		if(isset($cancelled)){
			if($cancelled == true){
				$this->cancelled = true;
			} else {
				$this->cancelled = false;
			}
		} else {
			$this->cancelled = false;
		}
	}
}
?>
