<?php
namespace Blog\Model\DatabaseObjects;
use \Blog\Model\DatabaseObject;
use \Blog\Model\DatabaseObjects\Image;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\RelationNonexistentException;
use InvalidArgumentException;

class Person extends DatabaseObject {
	public $name;
	public $image;

	/* @inherited
	public $id;
	public $longid;

	private $new;
	private $empty;
	*/


	function __construct() {
		parent::__construct();
		$this->image = new Image();
	}

	public function pull($identifier) {
		$pdo = self::open_pdo();

		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM persons LEFT JOIN images ON image_id = person_image_id WHERE person_id = :id OR person_longid = :id';
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

		$query = 'SELECT * FROM persons LEFT JOIN images ON image_id = person_image_id ORDER BY person_name';

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
				$obj = new Person();
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

		$this->id = $data['person_id'];
		$this->longid = $data['person_longid'];
		$this->name = $data['person_name'];

		if(isset($data['image_id'])){
			$this->image->load($data);
		}

		$this->empty = false;
		$this->new = false;
	}

	public static function count() {
		$pdo = self::open_pdo();

		$query = 'SELECT COUNT(*) FROM persons';

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
			'name' => $this->name
		];

		if(!$this->image->is_empty()){
			$values['image_id'] = $this->image->id;
		} else {
			$values['image_id'] = '';
		}

		if($this->is_new()){
			$query = 'INSERT INTO persons (person_id, person_longid, person_name, person_image_id)
				VALUES (:id, :longid, :name, :image_id)';

			$values['longid'] = $this->longid;
		} else {
			$query = 'UPDATE persons SET person_name = :name, person_image_id = :image_id WHERE person_id = :id';
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
			'name' => [
				'required' => true,
				'pattern' => '^.{1,50}$'
			]
		];

		$this->import_standardized($data, $importconfig, $errorlist);

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
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		if($this->is_new()){
			throw new WrongObjectStateException('not new');
		}

		$query = 'DELETE FROM persons WHERE person_id = :id';
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
		$obj->name = $this->name;
		$obj->image = $this->image->export();

		return $obj;
	}

	private function import_image($data) {
		$image = new Image();

		if(!empty($data['image_id'])){
			try {
				$image->pull($data['image_id']);
			} catch(EmptyResultException $e){
				throw new RelationNonexistentException('image_id', $data['image_id'], 'Image');
			}

			$this->image = $image;
		} else if(!empty($data['image'])){
			$image->generate();
			$image->import($data['image']);
			$image->push();
			$this->image = $image;
			// TODO improve this
		}
	}
}
?>
