<?php
class Imagefile {
	public $id;
	public $image_id;
	public $size;
	public $data;

	const SIZE_ORIGINAL = 0;
	const SIZE_SMALL = 1;
	const SIZE_MIDDLE = 2;
	const SIZE_LARGE = 3;

	const DIMENSIONS = [
		1 => ['x' => 300, 'y' => 200],
		2 => ['x' => 600, 'y' => 400],
		3 => ['x' => 900, 'y' => 600]
	];


	public static function new($image_id) {
		$obj = new Imagefile();
		$obj->id = generate_id();
		$obj->image_id = $image_id;
		$obj->size = self::SIZE_ORIGINAL;
		return $obj;
	}

	public static function pull_by_id($id) {
		$query = 'SELECT * FROM images LEFT JOIN imagefiles ON image_id = imagefile_image_id WHERE imagefile_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function pull_original_size($image_id) {
		$query = 'SELECT * FROM images LEFT JOIN imagefiles ON image_id = imagefile_image_id
			WHERE image_id = :id AND imagefile_size = 0';

		$values = ['id' => $image_id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function pull_all_sizes($image_id, $no_data = false) {
		global $pdo;

		if($no_data == false){
			$query = 'SELECT * FROM imagefiles WHERE imagefile_image_id = :image_id';
		} else {
			$query = 'SELECT imagefile_id, imagefile_size, imagefile_image_id WHERE imagefile_image_id = :image_id';
		}

		$values = ['image_id' => $image_id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		$res = [];
		while($r = $s->fetch()){
			$obj = self::load($r);
			$res[$obj->size] = $obj;
		}

		return $res;
	}

	public static function load($data, $image = null) {
		$obj = new self();
		$obj->id = $data['imagefile_id'];
		$obj->image_id = $data['imagefile_image_id'];
		$obj->size = $data['imagefile_size'];
		$obj->data = $data['imagefile_data'];
		return $obj;
	}

	public function insert() {
		global $pdo;

		$query = 'INSERT INTO imagefiles (imagefile_id, imagefile_image_id, imagefile_size, imagefile_data)
			VALUES (:id, :image_id, :size, :data)';
		$values = [
			'id' => $this->id,
			'image_id' => $this->image_id,
			'size' => $this->size,
			'data' => $this->export()
		];

		$s = $pdo->prepare($query);

		if($s->execute($values) == false){
			throw new DatabaseException();
			return;
		} else {
			return true;
		}
	}

	public function delete() {
		global $pdo;

		$query = 'DELETE FROM imagefiles WHERE imagefile_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		return $s->execute($values);
	}

	public function resize($size) {
		if(!is_int($size) || !isset(self::DIMENSIONS[$size])){
			throw new InvalidArgumentException();
			return;
		}

		$image = imagecreatefromstring($this->data);

		$width = imagesx($image);
		$height = imagesy($image);
		$ratio = $width / $height;

		if($width < self::DIMENSIONS[$size]['x'] + 20 || $height < self::DIMENSIONS[$size]['y'] + 20){
			return false;
		}

		if($ratio <= 1.5){
			$new_width = $dimensions[$size]['x'];
		} else {
			$new_width = round($dimensions[$size]['y'] * $ratio);
		}

		$extension = $this->detect_extension();

		ob_start();

		if($extension == Image::EXTENSION_PNG){
			imagepng($image);
		} else if($extension == Image::EXTENSION_JPG){
			imagejpeg($this->image);
		} else if($extension == Image::EXTENSION_GIF){
			imagegif($this->image);
		}

		$data = ob_get_contents();
		ob_end_clean();

		$obj = Imagefile::new($this->image_id);
		$obj->size = $size;
		$obj->data = $data;

		return $obj;
	}

	public function import_image($data) { // base64 string or filename ($_FILES['']['tmp_name'])
		if($this->size !== self::SIZE_ORIGINAL){
			return false;
		}

		if(!isset($data) || !is_string($data)){
			return false;
		}

		if(preg_match('/data:.+;base64,/', $data)){
			$this->data = base64_decode(preg_replace('/data:.+;base64,/', '', $data));
		} else if(is_uploaded_file($data)){
			$this->data = file_get_contents($data);
		}

		if($this->data == false){
			return false;
		} else {
			return true;
		}
	}

	public function detect_extension() {
		$type = getimagesizefromstring($this->data)[2];

		if($type == IMAGETYPE_PNG){
			return Image::EXTENSION_PNG;
		} else if($type == IMAGETYPE_JPEG){
			return Image::EXTENSION_JPG;
		} else if($type == IMAGETYPE_GIF){
			return Image::EXTENSION_GIF;
		}
	}
}
?>
