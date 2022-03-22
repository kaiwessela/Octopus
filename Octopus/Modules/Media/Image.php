<?php
namespace Octopus\Modules\Media;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\FileHandling\ImageFile;
use \Octopus\Modules\Media\ImageList;
use \Octopus\Modules\Media\Medium;
use \Octopus\Modules\Media\ImageConfig;

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
	#
	# protected ?array $variant_files;
	#
	# final const ATTRIBUTES;

	// const DB_TABLE = 'images';
	// const DB_PREFIX = 'image';

	const LIST_CLASS = ImageList::class;

	const CONFIG = ImageConfig::class;
	const FILE_CLASS = ImageFile::class;
	const DB_CLASS_STRING = 'image';


	protected function autoversion() : void {
		$rules = ImageConfig::AUTOVERSION_RULES[$this->mime_type];

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

		$sources = [];

		foreach($this->variants as $variant){
			$sources[] = $this->src($variant).' '.ImageConfig::SIZES[$variant]['width'].'w';
		}

		return implode(', ', $sources);
	}
}
?>
