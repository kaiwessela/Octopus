<?php
class ImageManager {
	private $dir;

	// ratio = width/height – <0,5; 0,5-2; >2
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
		'extrasmall'	=> 50,
		'small' 		=> 65,
		'middle'		=> 75,
		'large' 		=> 80,
		'extralarge' 	=> 90
	];


	function __construct($dir) {
		$this->dir = $dir; // TODO validation
	}

	# TODO better exception
	# @param: &$image = Image object; Database representation of the image to be uploaded
	public function receive_upload(&$image) {
		# check if $image is a valid Image object
		if(!isset($image) || !$image instanceof Image){
			throw new InvalidArgumentException();
		}

		# check different sources; form post -> form-data, ajax -> json
		# then receive the sent data
		if($_SERVER['CONTENT_TYPE'] == 'application/json'){
			$data = $this->read_data_from_json();
		} else if(preg_match('/^multipart\/form-data;.+$/', $_SERVER['CONTENT_TYPE'])){
			$data = $this->read_data_from_formdata();
		} else {
			throw new Exception('invalid content type: ' . $_SERVER['CONTENT_TYPE']);
		}

		$type = getimagesizefromstring($data)[2];
		$orig_image = imagecreatefromstring($data);

		if(!$orig_image){
			throw new Exception('invalid image data');
		}

		$this->check_and_set_type($image, $type);

		mkdir($this->dir . '/' . $image->longid);

		$sizes['original'] = $data;
		foreach(self::DIMENSIONS as $target_size => $value){
			if($target_size == 'original'){
				$this->write_image($image, $data, 'original');
				continue;
			}

			$resized_data = $this->resize_image($image, $orig_image, $target_size);

			if($resized_data == false){
				continue;
			}

			$this->write_image($image, $resized_data, $target_size);
		}

		$image->sizes = array_keys($sizes);

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
			throw new Exception('invalid image type');
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

	private function write_image(&$image, $data, $size) { // TODO validation
		$path = $this->dir . '/' . $image->longid . '/' . $size . '.' . $image->extension;
		file_put_contents($path, $data);
		chmod($path, 0777); // FIXME probably wrong
	}

	public function delete_image($image, $size = null) {

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
}
?>
