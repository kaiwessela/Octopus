<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\ImageManager; // TEMP

class Image extends DataObject {

#			NAME				TYPE	REQUIRED	PATTERN		DB NAME		DB VALUE
	public $extension;		#	str		*			custom		=			=
	public $description;	#	str					.{0,100}	=			=
	public $copyright;		#	str					.{0,100}	=			=
	public $sizes;			#	array	*			custom		=			= (imploded)

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;

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


	// TEMP from here; ImageManager
	const EXTENSION_PNG = 'png';
	const EXTENSION_JPG = 'jpg';
	const EXTENSION_GIF = 'gif';

	protected function import_custom($fieldname, $data, $errors) {
		if($fieldname != 'data' || !$this->is_new()){
			return;
		}

		$imagemanager = new ImageManager('/../resources/images/dynamic');
		$imagemanager->receive_upload($this);
	}
	// END TEMP
	// TODO delete procedure


	public function load($data) {
		$this->req('empty');

		$this->load_single($data[0]);
	}

	public function load_single($data) {
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

	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->extension = $this->extension;
		$obj->description = $this->description;
		$obj->copyright = $this->copyright;
		$obj->sizes = $this->sizes;

		return $obj;
	}


	protected function db_export() {
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
