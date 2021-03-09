<?php
namespace Blog\Model\DataObjects\Media;
use \Blog\Model\DataObjects\Medium;
use \Blog\Model\FileManager;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Config\MediaConfig;

class Application extends Medium {
	public static string $class = 'application';


	protected function import_custom(string $property, array $data) : void {
		if($property != 'file' || !$this->is_new()){
			return;
		}

		$file = FileManager::receive('file');
		$variants = [];

		if(!in_array($file->type, MediaConfig::APPLICATION_TYPES)){
			throw new IllegalValueException('file', '', 'application');
		}

		$this->type = $file->type;
		$this->extension = $file->extension;
		$this->files['original'] => $file;
		$this->variants = null;
	}


	protected function write_file() : void {
		if($this->is_new()){
			FileManager::write($this->files['original'], $this);
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


	const PULL_QUERY = <<<SQL
SELECT * FROM media WHERE medium_class = 'application' AND
(medium_id = :id OR medium_longid = :id)
SQL; #---|

}
?>
