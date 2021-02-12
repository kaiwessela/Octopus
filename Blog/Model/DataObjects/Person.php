<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Lists\GroupList;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;

class Person extends DataObject {
	public string 								$name;
	public ?Image 								$image;
	public GroupList|array|null 				$groups;
	public PersonGroupRelationList|array|null 	$grouprelations;

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

	const PROPERTIES = [
		'name' => '.{1,50}',
		'image' => Image::class,
		'groups' => GroupList::class,
		'grouprelations' => PersonGroupRelationList::class
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->req('empty');

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['person_id'];
		$this->longid = $row['person_longid'];
		$this->name = $row['person_name'];

		$this->image = empty($row['image_id']) ? null : new Image();
		$this->image?->load($row);

		if(!$norecursion){
			$this->grouprelations = empty($row['persongrouprelation_id']) ? null : new PersonGroupRelationList();
			$this->grouprelations?->load($data, $this);
			$this->groups = empty($this->grouprelations?->relations) ? null : new GroupList();
			$this->groups?->load_from_relationlist($this->grouprelations);
		}

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
