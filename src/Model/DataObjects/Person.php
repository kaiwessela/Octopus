<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;

class Person extends DataObject {

#					NAME			TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 	$name;		#	str			*			.{1,50}		=			=
	public ?Image 	$image;		#	Image								image_id	Image->id
	public ?array 	$groups;	#	arr[Group]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'name' => '.{1,50}',
		'image' => Image::class,
		'groups' => PersonGroupRelationList::class
	];


	function __construct() {
		parent::__construct();
		$this->relationlist = new PersonGroupRelationList();
	}


	public function load(array $data) : void {
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


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['person_id'];
		$this->longid = $data['person_longid'];
		$this->name = $data['person_name'];

		$this->image = empty($data['image_id']) ? null : new Image();
		$this->image?->load_single($data);

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'name' => $this->name,
			'image_id' => $this->image?->id
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	protected function push_children() : void {
		if($this->image?->is_new()){
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
