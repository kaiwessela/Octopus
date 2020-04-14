<?php
class Image {
	public $id;				# String(8)[base16]
	public $longid;			# String(1-128)[a-z0-9-]
	public $extension;		# String(1-4)[filename extension]
	public $description;	# String(1-256)

	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';

	const SIZE_ORIGINAL = 0;
	const SIZE_SMALL = 1;
	const SIZE_MIDDLE = 2;
	const SIZE_LARGE = 3;


	public static function new() {
		$obj = new self();
		return $obj;
	}

	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		$s->execute($values);
			return self::load($s->fetch());
		if($s->rowCount() == 1){

		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function pull_by_longid($longid) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_longid = :longid';
		$values = ['longid' => $longid];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function load($data) {
		$obj = new self();
		$obj->id = $data['image_id'];
		$obj->longid = $data['image_longid'];
		$obj->extension = $data['image_extension'];
		$obj->description = $data['image_description'];
	}

	public function get_available_sizes() {
		global $pdo;

		$query = 'SELECT imagefile_id, imagefile_size FROM imagefiles WHERE imagefile_image_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		$filelist = [];
		while($r = $s->fetch()){
			$filelist[$r['imagefile_size']] = $r['imagefile_id'];
		}

		return $filelist;
	}

	public function get_imagefile($id) {
		global $pdo;

		$query = 'SELECT * FROM imagefiles WHERE imagefile_id = :id && imagefile_image_id = :image';
		$values = ['id' => $id, 'image' => $this->id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return $s->fetch()['imagefile_data'];
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public function get_original_file() {
		global $pdo;

		$query = 'SELECT * FROM imagefiles WHERE imagefile_id = :id && imagefile_size = :size';
		$values = ['id' => $id, 'size' => self::SIZE_ORIGINAL];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return $s->fetch()['imagefile_data'];
		} else {
			throw new ObjectNotFoundException();
		}
	}

	private function upload_image($data) {
		if(!isset($imagedata) || imagecreatefromstring($imagedata) == false){
			throw new InvalidArgumentException();
			return false;
		}


	}

	private static function resize_image($imagedata, $size) {
		$dimensions = [
			1 => ['x' => 300, 'y' => 200],
			2 => ['x' => 600, 'y' => 400],
			3 => ['x' => 900, 'y' => 600]
		];

		if(!isset($dimensions[$size])){
			throw new InvalidArgumentException();
			return;
		}

		$image_original = imagecreatefromstring($imagedata);

		if($image_original == false){
			throw new InvalidArgumentException();
			return;
		}

		$orig_sizes = getimagesizefromstring($imagedata);
		$orig_ratio = $orig_sizes[0] / $orig_sizes[1];
		$orig_x = $orig_sizes[0];

		if($orig_ratio <= 1.5){
			$new_x = $dimensions[$size]['x'];
		} else {
			$new_x = round($dimensions[$size]['y'] * $orig_ratio);
		}

		if($orig_x < $dimensions[$size]['x'] + 20 || $orig_y < $dimensions[$size]['y'] + 20){
			return false;
		}

		$image_new = imagescale($image_original, $new_x);

		ob_start();

		if($this->extension == self::EXTENSION_PNG){
			imagepng($image_new);
		} else if($this->extension == self::EXTENSION_JPG){
			imagejpeg($image_new);
		} else if($this->extension == self::EXTENSION_GIF){
			imagegif($image_new);
		}

		$imagedata = ob_get_contents();
		ob_end_clean();

		imagedestroy($image_new);
		imagedestroy($image_original);

		return $imagedata;
	}
}
?>
