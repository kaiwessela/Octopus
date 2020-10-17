<?php
namespace Blog\Backend\Models;
use \Blog\Config\Config;
use \Blog\Backend\Model;
use \Blog\Backend\ModelTrait;
use \Blog\Backend\ImageManager;
use \Blog\Backend\Exceptions\WrongObjectStateException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Backend\Exceptions\InvalidInputException;
use InvalidArgumentException;

class Image extends Model {
	public $extension;		# String(1-4)[filename extension]
	public $description;	# String(1-256)
	public $copyright;
	public $sizes;

	/* @inherited
	public $id;
	public $longid;

	private $new;
	private $empty;
	*/

	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';


	public function pull($identifier) {
		$pdo = self::open_pdo();

		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM images WHERE image_id = :id OR image_longid = :id';
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

		$this->id = $data['image_id'];
		$this->longid = $data['image_longid'];
		$this->extension = $data['image_extension'];
		$this->description = $data['image_description'];
		$this->copyright = $data['image_copyright'];
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
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		$values = [
			'id' => $this->id,
			'description' => $this->description,
			'copyright' => $this->copyright
		];

		if($this->is_new()){
			$query = 'INSERT INTO images (image_id, image_longid, image_extension,
				image_description, image_copyright, image_sizes) VALUES (:id, :longid, :extension,
				:description, :copyright, :sizes)';

			$values['longid'] = $this->longid;
			$values['extension'] = $this->extension;
			$values['sizes'] = implode(' ', $this->sizes);
		} else {
			$query = 'UPDATE images SET image_description = :description, image_copyright =
			 	:copyright WHERE image_id = :id';
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = false;
		}
	}

	public function import($data) {
		$imagemanager = new ImageManager(Config::DYNAMIC_IMAGE_PATH);

		if($this->is_new()){
			$this->import_longid($data['longid']);
			$imagemanager->receive_upload($this);
		} else {
			$this->import_check_id_and_longid($data['id'], $data['longid']);
		}

		$this->import_description($data['description']);
		$this->import_copyright($data['copyright']);

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
		$imagemanager = new ImageManager(Config::DYNAMIC_IMAGE_PATH);

		$imagemanager->delete_images($this);

		$query = 'DELETE FROM images WHERE image_id = :id';
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
		$obj->extension = $this->extension;
		$obj->description = $this->description;
		$obj->copyright = $this->copyright;
		$obj->sizes = $this->sizes;

		return $obj;
	}

	public function has_size($size) {
		return in_array($size, $this->sizes);
	}

	private function import_description($description) {
		if(!isset($description)){
			$this->description = null;
		} else if(!preg_match('/^.{0,100}$/', $description)){
			throw new InvalidInputException('description', '.{0,100}', $description);
		} else {
			$this->description = $description;
		}
	}

	private function import_copyright($copyright) {
		if(!isset($copyright)){
			$this->copyright = null;
		} else if(!preg_match('/^.{0,100}$/', $copyright)){
			throw new InvalidInputException('copyright', '.{0,100}', $copyright);
		} else {
			$this->copyright = $copyright;
		}
	}
}
?>
