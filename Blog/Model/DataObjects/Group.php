<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Lists\PersonList;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;

class Group extends DataObject {
	public string 								$name;
	public ?string 								$description;
	public PersonList|array|null 				$persons;
	public PersonGroupRelationList|array|null 	$personrelations;

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;
#
#	const IGNORE_PULL_LIMIT = false;

	const PROPERTIES = [
		'name' => '.{1,30}',
		'description' => null,
		'persons' => PersonList::class,
		'personrelations' => PersonGroupRelationList::class
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->req('empty');

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['group_id'];
		$this->longid = $row['group_longid'];
		$this->name = $row['group_name'];
		$this->description = $row['group_description'];

		if(!$norecursion){
			$this->personrelations = empty($row['persongrouprelation_id']) ? null : new PersonGroupRelationList();
			$this->personrelations?->load($data, $this);
			$this->persons = empty($this->personrelations?->relations) ? null : new PersonList();
			$this->persons?->load_from_relationlist($this->personrelations);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$export = [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description
		];

		if($this->is_new()){
			$export['longid'] = $this->longid;
		}

		return $export;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM groups
LEFT JOIN persongrouprelations ON persongrouprelation_group_id = group_id
LEFT JOIN persons ON person_id = persongrouprelation_person_id
LEFT JOIN images ON image_id = person_image_id
WHERE group_id = :id OR group_longid = :id
ORDER BY persongrouprelation_number
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM persongrouprelations WHERE persongrouprelation_group_id = :id
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO groups (group_id, group_longid, group_name, group_description)
VALUES (:id, :longid, :name, :description)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE groups SET
	group_name = :name,
	group_description = :description
WHERE group_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM groups WHERE group_id = :id
SQL; #---|

}
?>
