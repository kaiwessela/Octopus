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
}
?>
