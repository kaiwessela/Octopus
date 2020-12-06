<?php
namespace Blog\Controller\Processors;
use \Blog\Model\DataObjects\Image;
use \Blog\Config\ImageManager as ImageManagerConfig;
use \Blog\Config\Config;

class Picture {
	public $id;
	public $longid;
	public $extension;
	public $description;
	public $copyright;
	public $sizes;

	public $sources;
	public $source_original;


	function __construct($image) {
		if(!$image instanceof Image){
			$image = new Image();
		}

		$this->id = $image->id;
		$this->longid = $image->longid;
		$this->extension = $image->extension;
		$this->description = $image->description;
		$this->copyright = $image->copyright;
		$this->sizes = $image->sizes ?? [];

		$this->sources = [];
		$this->source_original = $this->url('original');

		foreach($this->sizes as $size){
			if($size == 'original'){
				continue;
			}

			$this->sources[] = $this->url($size) . ' ' . ImageManagerConfig::SCALINGS[$size][1] . 'w';
		}

		if(count($this->sources) == 0){
			$this->sources = $this->source_original;
		} else {
			$this->sources = implode(', ', $this->sources);
		}
	}

	private function url($size) {
		if(empty($this->longid)){
			return null;
		}

		return Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH . $this->longid . '/' . $size . '.' . $this->extension;
	}
}
