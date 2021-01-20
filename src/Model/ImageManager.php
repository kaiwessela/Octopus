<?php
namespace Blog\Model;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\ImageManager\Image as IMImage;
use \Blog\Model\ImageManager\Exceptions\ImageManagerException;
use \Blog\Model\ImageManager\Exceptions\FileException;
use \Blog\Config\ImageManager as IMConfig;

class ImageManager {
	private $basedir;
	public $versions; # = [size(str) => IMImage, …]
	private $pdo;


	function __construct() {
		$this->versions = [];
		$this->basedir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . IMConfig::BASEDIR;

		if(!file_exists($this->basedir)){
			throw new FileException('basedir not found.');
		}
	}


	public function upload(Image $image, $fieldname) {
#	@action:
#	  - receive image data uploaded to the server (as json base64 or via $_FILES)
#	  - create an ImageManagerImage from that data
#	@params:
#	  - $image: Image (DataObject), the IMImage gets the same id
#	  - $fieldname: Name of the input field ($_FILES[XXX][tmp_name] / jsoninput[XXX])

		if($_SERVER['CONTENT_TYPE'] == 'application/json'){
			$jsoninput = json_decode(file_get_contents('php://input'), true);
			$data_base64 = preg_replace('/data:.+;base64/', '', $jsoninput[$fieldname]);
			$data = base64_decode($data_base64) ?? null;
		} else if(preg_match('/^multipart\/form-data;.+$/', $_SERVER['CONTENT_TYPE'])){
			$data = file_get_contents($_FILES[$fieldname]['tmp_name']) ?? null;
		} else {
			throw new ImageManagerException('invalid content-type.');
		}

		$original = new IMImage($image->id);
		$original->receive($data);

		$this->versions['original'] = $original;
	}


	public function pull($image) {
#	@action:
#	  - select an IMImage from the database
#	  - set it as the original image in versions
#	@params:
#	  - $image: Image->id string or Image object

		if(is_string($image)){
			$this->versions['original'] = IMImage::pull($image);
		} else if($image instanceof Image){
			$this->versions['original'] = IMImage::pull($image->id);
		} else {
			throw new ImageManagerException('image must be an Image or an image id.');
		}
	}


	public function push() {
#	@action:
#	  - insert the original IMImage into the database

		if(empty($this->versions['original'])){
			throw new ImageManagerException('no image found.');
		}

		$this->versions['original']->push();
	}


	public function scale() {
#	@action:
#	  - scale the uploaded / original image to different sizes

		if(empty($this->versions)){
			throw new ImageManagerException('no image found.');
		}

		$images = $this->versions['original']->scale();

		if(!empty($images)){
			$this->versions = array_merge($this->versions, $images);
		}
	}


	public function write($longid, $only_original = false) {
		# filename = /[basedir]/[longid]/[size].[extension]

		if(!is_string($longid) || empty($longid)){
			throw new InvalidArgumentException('longid must be a non-empty string.');
		}

		$directory = $this->basedir . DIRECTORY_SEPARATOR . $longid;
		if(is_dir($directory)){
			$this::rm($directory);
		}


		if(!mkdir($directory)){
			throw new FileException('directory creation (mkdir) failed: ' . $directory);
		}

		if(!is_writable($directory)){
			throw new FileException('directory not writable: ' . $directory);
		}

		foreach($this->versions as $size => $image){
			if($only_original && $size != 'original'){
				continue;
			}

			$filename = $directory . DIRECTORY_SEPARATOR . $size . '.' . $image->extension();
			if(file_put_contents($filename, $image->data) === false){
				throw new FileException('file_put_contents failed: ' . $filename);
			}
		}
	}


	public function erase($longid) {
		if(!is_string($longid) || empty($longid)){
			throw new InvalidArgumentException('longid must be a non-empty string.');
		}

		$this::rm($this->basedir . DIRECTORY_SEPARATOR . $longid);
	}


	public function get_extension() {
		if(empty($this->versions['original'])){
			throw new ImageManagerException('no image found.');
		}

		return $this->versions['original']->extension();
	}


	public function get_sizes() {
		if(empty($this->versions)){
			throw new ImageManagerException('no image found.');
		}

		return array_keys($this->versions);
	}


	private static function rm($path) {
		if(!file_exists($path)){
			throw new FileException('file or directory does not exist: ' . $path);
		}

		if(!is_writable($path)){
			throw new FileException('file or directory is not writable: ' . $path);
		}

		if(is_dir($path)){
			$children = scandir($path);

			if(!empty($children)){
				foreach($children as $child){
					if($child == '.' || $child == '..'){
						continue;
					}

					self::rm($path . DIRECTORY_SEPARATOR . $child);
				}
			}

			if(!rmdir($path)){
				throw new FileException('directory is not removable: ' . $path);
			}

			return;

		} else {
			if(!unlink($path)){
				throw new FileException('file is not deletable: ' . $path);
			}

			return;
		}
	}


}
?>
