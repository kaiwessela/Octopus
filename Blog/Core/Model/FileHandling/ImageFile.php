<?php // prototype
namespace Blog\Core\Model\File;

class ImageFile extends File {
	# inherited from File
	# public string $name;
	# public string $mime_type;
	# public string $extension;
	# public string $variant;
	# public string $data;


	public function is_resizable() : bool {

	}


	public function resize(int $width, int $quality, bool $upscaling = false) : ?ImageFile {
		if(!$this->is_resizable()){
			throw new Exception('ImageFile | resize » this type of image is not resizable.');
		}

		if(!$resource = imagecreatefromstring($this->data)){
			throw new Exception('ImageFile | resize » (GD) error creating image resource from data.');
		}

		$orig_width = imagesx($resource);

		if(!$upscaling && $width >= $orig_width){
			imagedestroy($resource);
			return null; // TODO maybe throw error
		}

		if(!$scaled = imagescale($resource, $width)){
			throw new Exception('ImageFile | resize » (GD) error creating scaled image resource.');
		}

		ob_start();
		switch($this->mime_type){
			case 'image/bmp':
				imagebmp($scaled, null, true);
				break;

			case 'image/gif':
				imagegif($scaled);
				break;

			case 'image/jpeg':
				imageinterlace($scaled, true);
				imagejpeg($scaled, null, $quality); // TODO validate quality
				break;

			case 'image/png':
				imagepng($scaled, null, 9, PNG_ALL_FILTERS);
				break;

			case 'image/vnd.wap.wbmp':
				imagewbmp($scaled);
				break;

			case 'image/webp':
				imagewebp($scaled, null, $quality);
				break;

			case 'image/x-xbitmap':
				imagexbm($scaled);
				break;
		}
		$scaled_data = ob_get_contents();
		ob_end_clean();

		imagedestroy($resource);
		imagedestroy($scaled);

		$file = new ImageFile();
		$file->create($scaled_data, $this->mime_type, $this->extension);
		return $file;
	}


	const RESIZABLE_MIME_TYPES = [
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/vnd.wap.wbmp',
		'image/webp',
		'image/x-xbitmap',
	];
}
?>
