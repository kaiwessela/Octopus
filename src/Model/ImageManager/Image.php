<?php
namespace Blog\Model\ImageManager;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Config\ImageManager as IMConfig;
use \Blog\Model\ImageManager\Exceptions\ImageManagerException;

class Image {
	public $image_id;
	public $data;
	private $resource;


	function __construct($image_id) {
		$this->image_id = $image_id;
	}

	public function receive($data) {
		if(empty($data)){
			throw new ImageManagerException('received data is empty.');
		}

		if(!$this->resource = imagecreatefromstring($data)){
			throw new ImageManagerException('received data is not an image.');
		}

		$this->data = $data;

		if(!in_array($this->type(), [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF])){
			throw new ImageManagerException('received image is not of a valid type.');
		}
	}

	public static function pull($image_id) {
		$pdo = DataObject::open_pdo();

		$query = 'SELECT * FROM imagefiles WHERE imagefile_id = :id';
		$values = ['id' => $image_id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			$this->image_id = $s->fetch()['imagefile_image_id'];
			$this->data = $s->fetch()['imagefile_data'];
			$this->resource = imagecreatefromstring($this->data);
		}
	}

	public function push() {
		$pdo = DataObject::open_pdo();

		$query = 'INSERT INTO imagefiles (imagefile_id, imagefile_data) VALUES (:id, :data)';
		$values = ['id' => $this->image_id, 'data' => $this->data];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		}
	}

	public function type() {
		return getimagesizefromstring($this->data)[2];
	}

	public function extension() {
		return ltrim(image_type_to_extension($this->type()), '.');
	}

	public function mime() {
		return image_type_to_mime_type($this->type());
	}

	public function width() {
		return getimagesizefromstring($this->data)[0];
	}

	public function height() {
		return getimagesizefromstring($this->data)[1];
	}

	public function ratio() {
		return $this->width() / $this->height();
	}

	public function scale() {
		if(empty($this->resource)){

		}

		$images = [];

		foreach(IMConfig::SCALINGS as $size => $widths){
			if($this->ratio() <= 0.5){
				$width = IMConfig::SCALINGS[$size][0];
			} else if($this->ratio() >= 2){
				$width = IMConfig::SCALINGS[$size][2];
			} else {
				$width = IMConfig::SCALINGS[$size][1];
			}

			if($width >= $this->width()){
				continue;
			}

			$new = imagescale($this->resource, $width);

			ob_start();

			switch($this->type()){
				case IMAGETYPE_PNG:
					imagepng($new, null, 9);
					break;

				case IMAGETYPE_JPEG:
					imageinterlace($new, true);
					imagejpeg($new, null, IMConfig::SCALINGS[$size][3] ?? 100);
					break;

				case IMAGETYPE_GIF:
					imagegif($new, null);
					break;
			}

			$data = ob_get_contents();
			ob_end_clean();

			$img = new self($this->image_id);
			$img->data = $data;
			$images[$size] = $img;

			imagedestroy($new);
		}

		return $images;
	}
}
?>
