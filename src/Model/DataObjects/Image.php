<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\ImageManager;
use \Blog\Model\ImageManager\Exceptions\ImageManagerException;

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
#
#	private $relationlist;

	private ?ImageManager $imagemanager;

	const IGNORE_PULL_LIMIT = true;

	const FIELDS = [
		'description' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,100}'
		],
		'copyright' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,100}'
		],
		'data' => [
			'type' => 'custom',
			'required' => false
		]
	];


	protected function import_custom(string $fieldname, $data, InputFailedException $errors) : void {
		if($fieldname != 'data' || !$this->is_new()){
			return;
		}

		$this->imagemanager = new ImageManager();

		try {
			$this->imagemanager->upload($this, 'imagedata');
			$this->imagemanager->scale();
		} catch(ImageManagerException $e){
			$errors->push(new InputException($fieldname, $e->getMessage()));
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

	// public function export(bool $block_recursion = false) : object {
	// 	$obj = (object) [];
	//
	// 	$obj->id = $this->id;
	// 	$obj->longid = $this->longid;
	// 	$obj->extension = $this->extension;
	// 	$obj->description = $this->description;
	// 	$obj->copyright = $this->copyright;
	// 	$obj->sizes = $this->sizes;
	//
	// 	return $obj;
	// }


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
