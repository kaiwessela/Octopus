<?php
class Image {
	public $id;				# String(8)[base16]
	public $longid;			# String(1-128)[a-z0-9-]
	public $extension;		# String(1-4)[filename extension]
	public $description;	# String(1-256)

	private $imagefiles;

	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';


	public static function new() {
		$obj = new self();
		$obj->id = generate_id();
		return $obj;
	}

	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM images WHERE image_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
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

	public static function pull_all() {
		global $pdo;

		$query = 'SELECT * FROM images';

		$s = $pdo->prepare($query);
		$s->execute(array());

		if($s->rowCount() > 0){
			$res = [];
			while($r = $s->fetch()){
				$res[] = self::load($r);
			}
			return $res;
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
		return $obj;
	}

	public function insert($data) {
		global $pdo;

		if(isset($data['longid'])){
			if(preg_match('/^[a-z0-9-]{1,128}$/', $data['longid'])){
				$found = true;
				try {
					$test_image = self::pull_by_longid($data['longid']);
				} catch(ObjectNotFoundException $e){
					$found = false;
				}

				if($found){
					throw new ObjectInsertException('longid already exists');
				} else {
					$this->longid = $data['longid'];
				}
			} else {
				throw new ObjectInsertException('invalid longid');
			}
		} else {
			throw new ObjectInsertException('no longid provided');
		}

		$orig = Imagefile::new($this->id);

		if(isset($data['imagefile'])){
			$import = $data['imagefile'];
		} else if(isset($_FILES['imagefile'])){
			$import = $_FILES['imagefile']['tmp_name'];
		}

		try {
			$orig->import_image($import);
		} catch(Exception $e){
			throw new ObjectInsertException('imagefile error: ' . $e->getMessage());
		}

		$this->extension = $orig->detect_extension();

		$this->imagefiles[Imagefile::SIZE_SMALL] = $orig->resize(Imagefile::SIZE_SMALL);
		$this->imagefiles[Imagefile::SIZE_MIDDLE] = $orig->resize(Imagefile::SIZE_MIDDLE);
		$this->imagefiles[Imagefile::SIZE_LARGE] = $orig->resize(Imagefile::SIZE_LARGE);
		$this->imagefiles[Imagefile::SIZE_ORIGINAL] = $orig;

		if(isset($data['description'])){
			$this->description = $data['description'];
		}

		$query = 'INSERT INTO images (image_id, image_longid, image_extension, image_description)
			VALUES (:id, :longid, :extension, :description)';

		$values = [
			'id' => $this->id,
			'longid' => $this->longid,
			'extension' => $this->extension,
			'description' => $this->description ?? ''
		];

		$s = $pdo->prepare($query);
		$s->execute($values);

		foreach($this->imagefiles as $file){
			if($file != false){
				$file->insert();
			}
		}
	}

	public function update($data) {
		if($data['id'] !== $this->id || $data['longid'] !== $this->longid){
			throw new ObjectUpdateException('id or longid wrong');
		}

		if($data['description'] == $this->description){
			return;
		}

		$this->description = $data['description'] ?? '';

		$query = 'UPDATE images SET image_description = :description WHERE image_id = :id';
		$values = ['description' => $this->description, 'id' => $this->id];

		$s = $pdo->prepare($query);
		$s->execute($values);
	}

	public function delete() {
		global $pdo;

		$query = 'DELETE FROM images WHERE image_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		return $s->execute($values);
	}
}
?>
