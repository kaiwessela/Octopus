<?php
// BUG: permissions of the uploaded files
namespace Blog\Backend;
use \Blog\Backend\Models\Image;
use \Blog\Backend\Exceptions\ImageManagerException;
use InvalidArgumentException;

class ImageManager {
	private $dir;

	const DIMENSIONS = [
		'original' 		=> null,
		'extrasmall' 	=> [	200, 	300, 	400		],
		'small' 		=> [	400, 	600, 	800		],
		'middle' 		=> [	800, 	1200, 	1600	],
		'large' 		=> [	1600, 	2400, 	3200	],
		'extralarge' 	=> [	3200, 	4800, 	6400	]
	];

	const JPG_QUALITY = [
		'original' 		=> 100,
		'extrasmall'	=> 80,
		'small' 		=> 85,
		'middle'		=> 90,
		'large' 		=> 95,
		'extralarge' 	=> 100
	];


	function __construct($dir) {
		$dir = __DIR__ . '/..' . $dir;
		if(!is_dir($dir)){
			throw new InvalidArgumentException('Directory is not a valid directory: ' . $dir);
		} else if(!is_writable($dir)){
			throw new InvalidArgumentException('Directory is not writable: ' . $dir);
		} else {
			$this->dir = $dir;
		}
	}

	# TODO better exception
	# @param: &$image = Image object; Database representation of the image to be uploaded
	public function receive_upload(&$image) {
		# check if $image is a valid Image object
		if(!isset($image) || !$image instanceof Image){
			throw new InvalidArgumentException('Valid Image required in ImageManager::receive_upload().');
		}

		# check different sources; form post -> form-data, ajax -> json
		# then receive the sent data
		if($_SERVER['CONTENT_TYPE'] == 'application/json'){
			$data = $this->read_data_from_json();
		} else if(preg_match('/^multipart\/form-data;.+$/', $_SERVER['CONTENT_TYPE'])){
			$data = $this->read_data_from_formdata();
		} else {
			throw new ImageManagerException('Invalid Content-Type; application/json or multipart/form-data allowed.');
		}

		# determine imagetype of the image
		$type = getimagesizefromstring($data)[2];

		# try to create an image from data
		$orig_image = imagecreatefromstring($data);

		# check if the image is valid -> data is a valid image
		if(!$orig_image){
			throw new ImageManagerException('Received Invalid or No image data from POST input.');
		}

		# check if image type is valid and write it into the Image object (as extension)
		$this->check_and_set_type($image, $type);

		# make a directory for the image files
		mkdir($this->dir . '/' . $image->longid);

		# list of all available sizes
		$sizes = ['original'];

		foreach(self::DIMENSIONS as $target_size => $value){
			if($target_size == 'original'){
				$this->write_image($image, $data, 'original');
				continue;
			}

			# resize image
			$resized_data = $this->resize_image($image, $orig_image, $target_size);

			if($resized_data == false){
				# resized image would be bigger than the original one so do not use it
				continue;
			}

			# add size to the list and write image file
			$sizes[] = $target_size;
			$this->write_image($image, $resized_data, $target_size);
		}

		$image->sizes = $sizes;

		imagedestroy($orig_image);
	}

	private function check_and_set_type(&$image, $type) {
		if($type == IMAGETYPE_PNG){
			$image->extension = Image::EXTENSION_PNG;
		} else if($type == IMAGETYPE_JPEG){
			$image->extension = Image::EXTENSION_JPG;
		} else if($type == IMAGETYPE_GIF){
			$image->extension = Image::EXTENSION_GIF;
		} else {
			throw new ImageManagerException('Invalid image type. PNG, JPG and GIF allowed.');
		}
	}


	private function resize_image($image, &$orig_image, $size) {
		$width = imagesx($orig_image);
		$height = imagesy($orig_image);
		$ratio = $width / $height;

		if($ratio < 0.5){
			$new_width = self::DIMENSIONS[$size][0];
		} else if($ratio > 2){
			$new_width = self::DIMENSIONS[$size][2];
		} else {
			$new_width = self::DIMENSIONS[$size][1];
		}

		if($new_width > $width){
			return null;
		}

		$new_image = imagescale($orig_image, $new_width);

		ob_start();
		if($image->extension == Image::EXTENSION_PNG){
			imagepng($new_image, null, 9);
		} else if($image->extension == Image::EXTENSION_JPG){
			imagejpeg($new_image, null, self::JPG_QUALITY[$size]);
		} else if($image->extension == Image::EXTENSION_GIF){
			imagegif($new_image);
		}

		$data = ob_get_contents();
		ob_end_clean();

		imagedestroy($new_image);

		return $data;
	}

	private function write_image(&$image, $data, $size) {
		$path = "$this->dir/$image->longid/$size.$image->extension";

		if(!file_put_contents($path, $data)){
			throw new ImageManagerException('Unable to write file. Check your server configuration and permissions.');
		}
	}

	private function read_data_from_json() {
		$input = file_get_contents('php://input');
		$raw_base64 = json_decode($input, true)['imagedata'];
		$clean_base64 = preg_replace('/data:.+;base64/', '', $raw_base64);
		return base64_decode($clean_base64);
	}

	private function read_data_from_formdata() {
		return file_get_contents($_FILES['imagedata']['tmp_name']);
	}

	private function is_valid_image($data) {
		$image = imagecreatefromstring($data);
		$valid = ($image != false);
		imagedestroy($image);
		return $valid;
	}

	public function delete_images($image) {
		foreach($image->sizes as $size){
			$file = "$this->dir/$image->longid/$size.$image->extension";
			if(file_exists($file)){
				unlink($file);
			}
		}

		$dir = "$this->dir/$image->longid";
		if(is_dir($dir))
		rmdir($dir);
	}
}
?>
