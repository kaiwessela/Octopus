<?php
class Image extends ContentObject {
	public $extension;		# String(1-4)[filename extension]
	public $description;	# String(1-256)
	public $sizes;

	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';


	public static function new() {
		$obj = new self();
		$obj->id = generate_id();
		return $obj;
	}

	public static function pull($id_or_longid) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_id = :id OR image_longid = :id';

		$values = ['id' => $id_or_longid];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			return self::load($s->fetch());
		}
	}

	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_id = :id';

		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			return self::load($s->fetch());
		}
	}

	public static function pull_by_longid($longid) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_longid = :longid';
		$values = ['longid' => $longid];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			return self::load($s->fetch());
		}
	}

	public static function pull_all($limit = null, $offset = null) {
		global $pdo;

		$query = 'SELECT * FROM images';
		$values = [];

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
				$res[] = self::load($r);
			}
			return $res;
		}
	}

	public static function load($data) {
		$obj = new self();

		$obj->id = $data['image_id'];
		$obj->longid = $data['image_longid'];
		$obj->extension = $data['image_extension'];
		$obj->description = $data['image_description'];
		$obj->sizes = explode(' ', $data['image_sizes']);

		return $obj;
	}

	public static function count() {
		global $pdo;

		$query = 'SELECT COUNT(*) FROM images';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	public function insert($data) {
		global $pdo;
		global $imagemanager;

		$this->import_longid($data['longid']);
		$this->import_description($data['description']);

		$imagemanager->receive_upload($this);

		$query = <<<SQL
INSERT INTO images (
 image_id,
 image_longid,
 image_extension,
 image_description,
 image_sizes
) VALUES (
 :id,
 :longid,
 :extension,
 :description,
 :sizes
)
SQL;

		$values = [
			'id' => $this->id,
			'longid' => $this->longid,
			'extension' => $this->extension,
			'description' => $this->description ?? '',
			'sizes' => implode(' ', $this->sizes)
		];

		$s = $pdo->prepare($query);
		$s->execute($values);
	}

	public function update($data) {
		global $pdo;

		$this->import_check_id_and_longid($data['id'], $data['longid']);
		$this->import_description($data['description']);

		$query = 'UPDATE images SET image_description = :description WHERE image_id = :id';
		$values = ['description' => $this->description, 'id' => $this->id];

		$s = $pdo->prepare($query);
		$s->execute($values);
	}

	public function delete() {
		global $pdo;
		global $imagemanager;

		$imagemanager->delete_images($this);

		$query = 'DELETE FROM images WHERE image_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		return $s->execute($values);
	}

	public function has_size($size) {
		return in_array($size, $this->sizes);
	}

	private function import_description($description) {
		$this->description = $description;
	}
}
?>
