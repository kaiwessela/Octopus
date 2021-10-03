<?php
namespace Blog\Model\FileManager;
use \Blog\Config\MediaConfig;
use Exception;

class File {
	public string $type;
	public string $extension;
	public string $data;

	private ?array $imagesize;

	private static array $image_mime_types;


	function __construct(string $type, string $extension, string $data) {
		$this->type = $type;
		$this->extension = $extension;
		$this->data = $data;
		$this->imagesize = null;
	}

	public function resize(string $size_name, bool $upscaling = true) : ?File {
		if(!$this->is_resizable()){
			throw new Exception('File | Resize » file is not resizable.');
		}

		if(!isset(MediaConfig::IMAGE_RESIZE_WIDTHS[$size_name])){
			throw new Exception('File | Resize » requested size name not found.');
		}

		$size = MediaConfig::IMAGE_RESIZE_WIDTHS[$size_name];
		if(!is_array($size) || !is_int($size[0]) || !is_int($size[1])){
			throw new Exception("File | Config » illegal value: IMAGE_RESIZE_WIDTHS[$size_name].");
		}

		$width = $size[0];
		$quality = $size[1];

		if(!$upscaling && $width >= $this->width){
			return null;
		}

		if(!$resource = imagecreatefromstring($this->data)){
			throw new Exception('File | GD » error creating image resource from data.');
		}

		if(!$scaled_resource = imagescale($resource, $width)){
			throw new Exception('File | GD » error creating scaled image resource.');
		}

		ob_start();
		switch($this->imagetype){
			case IMAGETYPE_BMP:
				imagebmp($scaled_resource, null, true);
				break;

			case IMAGETYPE_GIF:
				imagegif($scaled_resource);
				break;

			case IMAGETYPE_JPEG:
				imageinterlace($scaled_resource, 1);
				imagejpeg($scaled_resource, null, $quality);
				break;

			case IMAGETYPE_PNG:
				imagepng($scaled_resource, null, 9, PNG_ALL_FILTERS);
				break;

			case IMAGETYPE_WBMP:
				imagewbmp($scaled_resource);
				break;

			case IMAGETYPE_WEBP:
				imagewebp($scaled_resource, null, $quality);
				break;

			case IMAGETYPE_XBM:
				imagexbm($scaled_resource);
				break;
		}
		$new_data = ob_get_contents();
		ob_end_clean();
		imagedestroy($resource);
		imagedestroy($scaled_resource);

		return new File($this->type, $this->extension, $new_data);
	}

	public function is_resizable() : bool {
		return in_array($this->imagetype, self::IMAGETYPES_RESIZABLE);
	}

	private static function load_image_mime_types() : void {
		foreach(self::IMAGETYPES as $imtp){
			self::$image_mime_types[] = image_type_to_mime_type($imtp);
		}
	}

	function __get($prop) {
		if($prop === 'width' || $prop === 'height' || $prop === 'imagetype'){
			if(empty($this->imagesize)){
				if(!isset(self::$image_mime_types)){
					self::load_image_mime_types();
				}

				if(!in_array($this->type, self::$image_mime_types)){
					return null;
				}

				$imsz = getimagesizefromstring($this->data);
				if($imsz === false){
					// TODO Exception
					return null;
				} else {
					$this->imagesize = $imsz;
				}
			}

			return match($prop){
				'width' => $this->imagesize[0],
				'height' => $this->imagesize[1],
				'imagetype' => $this->imagesize[2]
			};
		}
	}


	const IMAGETYPES = [
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_SWF,
		IMAGETYPE_PSD,
		IMAGETYPE_BMP,
		IMAGETYPE_TIFF_II,
		IMAGETYPE_TIFF_MM,
		IMAGETYPE_JPC,
		IMAGETYPE_JP2,
		IMAGETYPE_JPX,
		IMAGETYPE_JB2,
		IMAGETYPE_SWC,
		IMAGETYPE_IFF,
		IMAGETYPE_WBMP,
		IMAGETYPE_XBM,
		IMAGETYPE_ICO,
		IMAGETYPE_WEBP
	];

	const IMAGETYPES_RESIZABLE = [
		IMAGETYPE_BMP,
		IMAGETYPE_GIF,
		IMAGETYPE_JPEG,
		IMAGETYPE_PNG,
		IMAGETYPE_WBMP,
		IMAGETYPE_WEBP,
		IMAGETYPE_XBM
	];
}
?>
