<?php
namespace Octopus\Core\Model\FileManager;
use \Octopus\Core\Model\FileManager\File;
use \Imagick;
use \GdImage;
use \Exception;

class ImageFile extends File {
	# inherited from File:
	# protected ?string $mime_type;
	# protected ?string $extension;
	protected mixed $data; # Imagick|GdImage|string|null
	#
	# protected Flow $flow;


	public function is_resizable() : bool {
		return in_array($this->mime_type, static::RESIZABLE_IMAGE_TYPES);
	}


	public function is_convertable_to(string $type) : bool {
		return isset(static::SUPPORTED_CONVERSIONS[$this->mime_type]) && in_array($type, static::SUPPORTED_CONVERSIONS[$this->mime_type]);
	}


	public function get_data() : ?string {
		if($this->data instanceof Imagick){
			return $this->data->getImageBlob();
		} else if($this->data instanceof GdImage){
			ob_start();

			if($this->mime_type === 'image/jpeg'){
				imageinterlace($this->data, true);
				imagejpeg($this->data, null, 100);
			} else if($this->mime_type === 'image/png'){
				imagepng($this->data, null, 9, PNG_ALL_FILTERS);
			} else if($this->mime_type === 'image/gif'){
				imagegif($this->data);
			} else if($this->mime_type === 'image/avif'){
				imageavif($this->data);
			}

			$data = ob_get_contents();
			ob_end_clean();

			return $data;
		} else {
			return $this->data;
		}
	}


	public function convert(string $to) : void {
		if(!$this->is_convertable_to($to)){
			throw new Exception('ImageFile » convert: this mime type is not convertable.');
		} else if(is_null($this->data)){
			throw new Exception('ImageFile » convert: cannot convert files with empty data.');
		}

		if(is_string($this->data)){
			$this->create_resource();
		}

		$this->mime_type = $to;
		$this->extension = static::get_extension_for_mime_type($to);
	}


	protected static function get_extension_for_mime_type(string $mime_type, ?string $proposed_extension = null) : string {
		if(!isset(static::EXTENSIONS[$mime_type])){
			throw new Exception("ImageFile » internal: no extensions defined for mime type «{$mime_type}».");
		}

		if(!is_null($proposed_extension) && in_array($proposed_extension, static::EXTENSIONS[$mime_type])){
			return $proposed_extension;
		} else {
			return static::EXTENSIONS[$mime_type][0];
		}
	}


	protected function create_resource() : void {
		if(is_null($this->data)){
			throw new Exception('ImageFile » edit: cannot edit files with empty data.');
		} else if(!is_string($data)){
			return; # resource has already been created
		} if(class_exists(Imagick::class)){
			$resource = new Imagick();

			if($resource->readImageBlob($this->data) === false){
				throw new Exception('ImageFile » (Imagick): error creating image resource from data');
			}
		} else if(class_exists(GdImage::class)){
			$resource = imagecreatefromstring($this->data);

			if($resource === false){
				throw new Exception('ImageFile » (GD): error creating image resource from data.');
			}

			$exif = exif_read_data($this->data);
			if(!empty($exif['Orientation'])){
				switch($exif['Orientation']){
					case 2:
						imageflip($resource, IMG_FLIP_HORIZONTAL);
						break;
					case 3:
						imageflip($resource, IMG_FLIP_BOTH);
						break;
					case 4:
						imageflip($resource, IMG_FLIP_VERTICAL);
						break;
					case 5:
						imageflip($resource, IMG_FLIP_HORIZONTAL);
						imagerotate($resource, 90, 0);
						break;
					case 6:
						imagerotate($resource, -90, 0);
						break;
					case 7:
						imageflip($resource, IMG_FLIP_VERTICAL);
						imagerotate($resource, 90, 0);
						break;
					case 8:
						imagerotate($resource, 90, 0);
						break;
				}
			}
		} else {
			throw new Exception('ImageFile » edit: no image editing extension found (Imagick or GD).');
		}

		$this->data = $resource;
	}


	public function resize(int $width, int $quality, bool $upscaling = false) : void {
		if(!$this->is_resizable()){
			throw new Exception('ImageFile » resize: this type of image is not resizable.');
		}

		if(is_string($this->data) || is_null($this->data)){
			$this->create_resource();
			$this->resize($width, $quality, $upscaling);
		} else if($this->data instanceof Imagick){
			$this->resize_imagick($width, $quality, $upscaling);
		} else if($this->data instanceof GdImage){
			$this->resize_gd($width, $quality, $upscaling);
		}
	}


	private function resize_imagick(int $width, int $quality, bool $upscaling) : void {
		$original_width = $this->data->getImageWidth();

		if($upscaling === false && $width >= $original_width){
			return;
		}

		if($this->data->resizeImage($width, 0, Imagick::FILTER_GAUSSIAN, 1.0, true) === false){
			throw new Exception('ImageFile » resize: (Imagick) error resizing the image.');
		}
	}


	private function resize_gd(int $width, int $quality, bool $upscaling) : void {
		$original_width = imagesx($this->data);

		if($upscaling === false && $width >= $original_width){
			return;
		}

		$scaled_resource = imagescale($this->data, $width);

		if($scaled_resource === false){
			throw new Exception('ImageFile » resize: (GD) error creating the scaled image resource.');
		} else {
			$this->data = $scaled_resource;
		}
	}


	# avif, gif, (ico), (jbig), jpeg, png, (svg), (tiff), (webp),
	const SUPPORTED_IMAGE_TYPES = [
		'image/jpeg',
		'image/png',
		'image/gif',
		'image/avif',
		'image/svg+xml'
	];

	const EXTENSIONS = [
		'image/jpeg' => ['jpg', 'jpeg'],
		'image/png' => ['png'],
		'image/gif' => ['gif'],
		'image/avif' => ['avif'],
		'image/svg+xml' => ['svg']
	];

	const RESIZABLE_IMAGE_TYPES = [
		'image/jpeg',
		'image/png',
		'image/avif'
	];

	const SUPPORTED_CONVERSIONS = [
		'image/jpeg' => ['image/png', 'image/avif'],
		'image/png' => ['image/jpeg', 'image/avif']
	];
}
?>
