<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;

class Person extends DataObject {

#			NAME		TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public $name;	#	str			*			.{1,50}		=			=
	public $image;	#	Image								image_id	Image->id
	public $groups;	#	arr[Group]

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
		'name' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,50}'
		],
		'image' => [
			'type' => 'Image',
			'required' => false
		],
		'groups' => [
			'type' => 'relationlist'
		]
	];


	function __construct() {
		parent::__construct();
		$this->image = new Image();
		$this->groups = [];
		$this->relationlist = new PersonGroupRelationList();
	}


	public function load($data) {
		$this->req('empty');

		$this->load_single($data[0]);

		$relations = [];
		foreach($data as $groupdata){
			if(empty($groupdata['persongrouprelation_id'])){
				continue;
			}

			$group = new Group();
			$group->load_single($groupdata, true);
			$this->groups[] = $group;

			$relation = new PersonGroupRelation();
			$relation->load($group, $this, $groupdata);
			$relations[$relation->id] = $relation;
		}

		$this->relationlist->load($relations);
	}


	public function load_single($data) {
		$this->req('empty');

		$this->id = $data['person_id'];
		$this->longid = $data['person_longid'];
		$this->name = $data['person_name'];

		if(!empty($data['image_id'])){
			$this->image->load_single($data);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->name = $this->name;
		
		if(!$this->image->is_empty()){
			$obj->image = $this->image->export();
		} else {
			$obj->image = null;
		}

		if(!$block_recursion){
			$obj->groups = [];
			foreach($this->groups as $group){
				$obj->groups[] = $group->export(true);
			}
		}

		$obj->relations = $this->relationlist->export();

		return $obj;
	}


	protected function db_export() {
		$values = [
			'id' => $this->id,
			'name' => $this->name
		];

		if(!$this->image->is_empty()){
			$values['image_id'] = $this->image->id;
		} else {
			$values['image_id'] = null;
		}

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	protected function push_children() {
		if($this->image->is_new()){
			$this->image->push();
		}
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM persons
LEFT JOIN images ON image_id = person_image_id
LEFT JOIN persongrouprelations ON persongrouprelation_person_id = person_id
LEFT JOIN groups ON group_id = persongrouprelation_group_id
WHERE person_id = :id OR person_longid = :id
SQL; #---|


	const COUNT_QUERY = null;


	const INSERT_QUERY = <<<SQL
INSERT INTO persons (person_id, person_longid, person_name, person_image_id)
VALUES (:id, :longid, :name, :image_id)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE persons SET
	person_name = :name,
	person_image_id = :image_id
WHERE person_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM persons
WHERE person_id = :id
SQL; #---|

}
?>
