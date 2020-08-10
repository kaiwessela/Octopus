<?php
namespace Blog\Frontend\Web\Modules;
use Blog\Backend\Models\Image;
use Blog\Backend\ImageManager;
use Blog\Config\Config;

class Picture {
	public $image;
	public $original_src;
	public $show_source;
	public $srcset;
	public $alt;
	//public $width; // TODO maybe add later if necessary
	//public $height;


	function __construct(Image $image) {
		$this->image = $image;
		$path_base = Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $image->longid . '/';

		$this->original_src = $path_base . 'original.' . $image->extension;
		$this->alt = $image->description;

		foreach($this->image->sizes as $size){
			if($size == 'original'){
				continue;
			}

			$this->srcset[] = $path_base . $size . '.' . $this->image->extension . ' '
				. ImageManager::DIMENSIONS[$size][1] . 'w';
		}

		if(count($this->srcset) == 0){
			$this->show_source = false;
		} else {
			$this->show_source = true;
			$this->srcset = implode(', ', $this->srcset);
		}
	}
}
?>
