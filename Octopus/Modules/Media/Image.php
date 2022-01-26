<?php # Image.php 2021-10-04 beta
namespace Blog\Modules\Media;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\Media\Medium;

// not ideal from here
use \Blog\Model\FileManager;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Config\MediaConfig;

class Image extends Medium {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	# inherited from Medium:
	# protected ?string $name;
	# protected ?string $copyright;
	# protected ?string $type;
	# protected ?string $extension;
	# protected ?string $description;
	# protected ?string $alternative;
	# protected ?array 	$variants;

	# protected ?File $file;
	# protected ?array $variant_files;


	const DB_TABLE = 'images'
	const DB_PREFIX = 'image';
	

	const FILE_CLASS = ImageFile::class;
	const DB_CLASS_STRING = 'image';


	protected function autoversion() : void {
		$rules = Config::get('Modules.'.$this::class.'.autoversion_rules.'.$this->mime_type) ?? null;

		if(!is_array($rules)){
			return;
		}

		if($rules['resize'] === 'all' || is_array($rules['resize'])){
			// TODO resize
		}

		// TODO convert
	}


	public function srcset() : string {
		// TODO cycle check


	}


	public function srcset() : ?string {
		$sources = [];

		foreach($this->variants as $variant){
			if(empty(MediaConfig::IMAGE_RESIZE_WIDTHS[$variant])){
				continue;
			}

			$sources[] = $this->src($variant).' '.MediaConfig::IMAGE_RESIZE_WIDTHS[$variant][0].'w';
		}

		return implode(', ', $sources);
	}
}
?>
