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

#					NAME				TYPE	REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 	$extension;		#	str		*			custom		=			=
	public ?string 	$description;	#	str					.{0,100}	=			=
	public ?string 	$copyright;		#	str					.{0,100}	=			=
	public array 	$sizes;			#	array	*			custom		=			= (imploded)

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	private ?ImageManager $imagemanager;

	const IGNORE_PULL_LIMIT = true;

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


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['image_id'];
		$this->longid = $data['image_longid'];
		$this->extension = $data['image_extension'];
		$this->description = $data['image_description'];
		$this->copyright = $data['image_copyright'];
		$this->sizes = explode(' ', $data['image_sizes']);

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function push_children() : void {
		if($this->is_new()){
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
