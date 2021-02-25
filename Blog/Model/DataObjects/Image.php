<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\ImageManager;
use \Blog\Model\ImageManager\Exceptions\ImageManagerException;
use \Blog\Config\ImageManager as ImageManagerConfig;
use \Blog\Config\Config;

class Image extends DataObject {
	public string 	$extension;
	public ?string 	$description;
	public ?string 	$copyright;
	public array 	$sizes;

#	@inherited
#	public string $id;
#	public string $longid;
#
#	public ?int $count;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;

	const IGNORE_PULL_LIMIT = true;

	private ?ImageManager $imagemanager;

	const PROPERTIES = [
		'description' => '.{0,100}',
		'copyright' => '.{0,100}',
		'data' => 'custom'
	];


	protected function import_custom(string $property, array $data) : void {
		if($property != 'data' || !$this->is_new()){
			return;
		}

		$this->imagemanager = new ImageManager();

		try {
			$this->imagemanager->upload($this, 'imagedata');
			$this->imagemanager->scale();
		} catch(ImageManagerException $e){
			throw new InputException($property, $e->getMessage());
		}

		$this->extension = $this->imagemanager->get_extension();
		$this->sizes = $this->imagemanager->get_sizes();
	}


	public function load(array $data, bool $norecursion = false) : void {
		$this->require_empty();

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['image_id'];
		$this->longid = $row['image_longid'];
		$this->extension = $row['image_extension'];
		$this->description = $row['image_description'];
		$this->copyright = $row['image_copyright'];
		$this->sizes = explode(' ', $row['image_sizes']);

		$this->set_not_new();
		$this->set_not_empty();
	}


	public function push() : void {
		$impush = $this->is_new();

		parent::push();

		if($impush){
			$this->imagemanager->write($this->longid);
			$this->imagemanager->push();
		}
	}


	public function delete() : void {
		parent::delete();

		$this->imagemanager = new ImageManager();
		$this->imagemanager->erase($this->longid);
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'description' => $this->description,
			'copyright' => $this->copyright
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
			$values['extension'] = $this->extension;
			$values['sizes'] = implode(' ', $this->sizes);
		}

		return $values;
	}


	public function src(string $size = 'original') : ?string {
		if(!in_array($size, $this->sizes)){
			return null;
		}

		return Config::SERVER_URL . Config::DYNAMIC_IMAGE_PATH
			. $this->longid . '/' . $size . '.' . $this->extension;
	}


	public function srcset() : ?string {
		$sources = [];

		foreach($this->sizes as $size){
			if($size == 'original'){
				continue;
			}

			$sources[] = $this->src($size) . ' ' . ImageManagerConfig::SCALINGS[$size][1] . 'w';
		}

		return implode(', ', $sources);
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM images
WHERE image_id = :id OR image_longid = :id
SQL; #---|

	const COUNT_QUERY = null;

	const INSERT_QUERY = <<<SQL
INSERT INTO images
(image_id, image_longid, image_extension, image_description, image_copyright, image_sizes)
VALUES (:id, :longid, :extension, :description, :copyright, :sizes)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE images SET
	image_description = :description,
	image_copyright = :copyright
WHERE image_id = :id
SQL; #---|

	const DELETE_QUERY = <<<SQL
DELETE FROM images
WHERE image_id = :id
SQL; #---|

}
?>
