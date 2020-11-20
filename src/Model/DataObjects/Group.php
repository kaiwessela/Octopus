<?php
namespace Blog\Model\DataObjects;

class Group extends DataObject {

#			NAME				TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public $name;			#	str			*			.{1,30}		=			=
	public $description;	#	str						.*			=			=
	public $persons;		#	arr[Person]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;
#
#	const IGNORE_PULL_LIMIT = false;


	const FIELDS = [
		'name' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,30}'
		],
		'description' => [
			'type' => 'string'
		],
		'persons' => [
			'type' => 'relationlist'
		]
	];


	public function load($data, $block_recursion = false) {
		$this->req('empty');

		$this->name = $data[0]['group_name'];
		$this->description = $data[0]['group_description'];

		if(!$block_recursion){
			$relations = [];
			foreach($data as $persondata){
				$person = new Person();
				$person->load($persondata, true);
				$this->persons[] = $person;

				$relation = new PersonGroupRelation();
				$relation->load($this, $person, $persondata);
				$relations[$relation->id] = $relation;
			}

			$this->relationlist->load($relations);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->name = $this->name;
		$obj->description = $this->description;

		if(!$block_recursion){
			$obj->persons = [];
			foreach($this->persons as $person){
				$obj->persons[] = $person->export(true);
			}
		}

		$obj->relations = $this->relationlist->export();

		return $obj;
	}


	private function db_export() {
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
SQL;


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM persongrouprelations WHERE persongrouprelation_group_id = :id
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO groups
	(group_id, group_longid, group_name, group_description)
VALUES
	(:id, :longid, :name, :description)
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
