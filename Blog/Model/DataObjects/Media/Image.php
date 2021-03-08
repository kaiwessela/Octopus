<?php
namespace Blog\Model\DataObjects\Media;
use \Blog\Model\DataObjects\Medium;
use \Blog\Model\FileManager;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Config\MediaConfig;

class Image extends Medium {
	public static string $class = 'image';


	protected function import_custom(string $property, array $data) : void {
		if($property != 'file' || !$this->is_new()){
			return;
		}

		$file = FileManager::receive('file');
		$variants = [];

		if(!in_array($file->type, MediaConfig::IMAGE_TYPES)){
			throw new IllegalValueException('file', '', 'image');
		}

		$this->type = $file->type;
		$this->extension = $file->extension;
		$this->files['original'] = $file;

		if(in_array($file->type, MediaConfig::RESIZABLE_IMAGE_TYPES)){
			foreach(array_keys(MediaConfig::IMAGE_RESIZE_WIDTHS) as $width){
				$this->files[$width] = $file->resize($width, upscaling:false);
				$this->versions[] = $width;
			}
		}
	}


	protected function write_file() : void {
		if($this->is_new()){
			foreach($this->files as $variant => $file){
				if($variant == 'original'){
					$variant = null;
				}

				FileManager::write($file, $this, $variant);
			}
		}
	}


	protected function push_file() : void {
		if($this->is_new()){
			FileManager::push($this->files['original'], $this);
		}
	}


	protected function erase_file() : void {
		FileManager::erase($this, true);
	}


	public function srcset() : ?string {
		$sources = [];

		foreach($this->variants as $variant){
			$sources[] = $this->src($variant).' '.MediaConfig::IMAGE_RESIZE_WIDTHS[$variant][0].'w';
		}

		return implode(', ', $sources);
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM media WHERE media_class = 'image' AND
(media_id = :id OR media_longid = :id)
SQL; #---|

}
?>
