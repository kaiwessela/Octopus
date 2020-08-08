<?php
namespace Blog\Backend\Models;
use \Blog\Backend\Model;
use \Blog\Backend\ImageManager;
use \Blog\Backend\Exceptions\WrongObjectStateException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Backend\Exceptions\InvalidInputException;
use InvalidArgumentException;

class Image implements Model {
	public $id;
	public $longid;
	public $extension;		# String(1-4)[filename extension]
	public $description;	# String(1-256)
	public $sizes;

	private $pdo;
	private $new;
	private $empty;

	private $imagemanager;

	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';

	use Model;


	function __construct() {
		$this->pdo = self::open_pdo();
		$this->imagemanager = new ImageManager();
		$this->empty = true;
	}

	public function generate() {
		if(!$this->empty){
			throw new WrongObjectStateException('empty');
		}

		$this->generate_id();

		$this->new = true;
		$this->empty = false;
	}

	public static function pull($identifier) {
		if(!$this->empty){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM images WHERE image_id = :id OR image_longid = :id';
		$values = ['id' => $identifier];

		$s = $this->pdo->prepare($query);
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

		$query = 'SELECT * FROM images';

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
				$obj = new Image();
				$obj->load($r)
				$res[] = &$obj;
			}
			return $res;
		}
	}

	public function load($data) {
		if(!$this->empty){
			throw new WrongObjectStateException('empty');
		}

		$this->id = $data['image_id'];
		$this->longid = $data['image_longid'];
		$this->extension = $data['image_extension'];
		$this->description = $data['image_description'];
		$this->sizes = explode(' ', $data['image_sizes']);

		$this->empty = false;
		$this->new = false;
	}

	public static function count() {
		$pdo = self::open_pdo();

		$query = 'SELECT COUNT(*) FROM images';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	public function push() {
		if($this->empty){
			throw new WrongObjectStateException('not empty');
		}

		$values = [
			'id' => $this->id,
			'description' => $this->description
		];

		if($this->new){
			$query = 'INSERT INTO images (image_id, image_longid, image_extension,
				image_description, image_sizes) VALUES (:id, :longid, :extension, :description,
				:sizes)';

			$values['longid'] = $this->longid;
			$values['extension'] = $this->extension;
			$values['sizes'] = implode(' ', $this->sizes);
		} else {
			$query = 'UPDATE images SET image_description = :description WHERE image_id = :id';
		}

		$s = $this->pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = false;
		}
	}

	public function import($data) {
		if($this->new){
			$this->import_longid($data['longid']);
			$this->imagemanager->receive_upload($this);
		} else {
			$this->import_check_id_and_longid($data['id'], $data['longid']);
		}

		$this->import_description($data['description']);

		$this->empty = false;
	}

	public function delete() {
		$this->imagemanager->delete_images($this);

		$query = 'DELETE FROM images WHERE image_id = :id';
		$values = ['id' => $this->id];

		$s = $this->pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = true;
		}
	}

	public function has_size($size) {
		return in_array($size, $this->sizes);
	}

	private function import_description($description) {
		$this->description = $description;
	}
}
?>
