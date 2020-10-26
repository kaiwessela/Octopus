<?php
namespace Blog\Model\DatabaseObjects;
use \Blog\Config\Config;
use \Blog\Model\DatabaseObject;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;
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
		$errorlist = new InputFailedException();

		if($this->is_new()){
			try {
				$this->import_longid($data['longid']);
			} catch(InputException $e){
				$errorlist->push($e);
			}
		} else {
			try {
				$this->import_check_id_and_longid($data['id'], $data['longid']);
			} catch(InputException $e){
				$errorlist->push($e);
			}
		}

		$importconfig = [
			'title' => [
				'required' => true,
				'pattern' => '^.{1,50}$'
			],
			'organisation' => [
				'required' => true,
				'pattern' => '^.{1,40}$'
			],
			'location' => [
				'required' => false,
				'pattern' => '^.{0,60}$'
			]
		];

		$this->import_standardized($data, $importconfig, $errorlist);

		$this->description = $data['description'];

		$this->import_timestamp($data['timestamp']);
		$this->import_cancelled($data['cancelled']);

		try {
			$this->import_image($data);
		} catch(InputFailedException $e){
			$errorlist->merge($e, 'image');
		} catch(InputException $e){
			$errorlist->push($e);
		}

		if(!$errorlist->is_empty()){
			throw $errorlist;
		}

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

	private function import_timestamp($timestamp) {
		if(empty($timestamp)){
			throw new MissingValueException('timestamp', 'unix timestamp');
		} else if(!is_numeric($timestamp)){
			throw new IllegalValueException('timestamp', $timestamp, 'unix timestamp');
		} else {
			$this->timestamp = (int) $timestamp;
		}
	}

	private function import_cancelled($cancelled = false) {
		if(!empty($cancelled)){
			$this->cancelled = (bool) $cancelled;
		} else {
			$this->cancelled = false;
		}
	}
}
?>
