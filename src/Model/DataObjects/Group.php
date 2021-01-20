<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;
use \Blog\Model\DataObjects\Relations\Lists\PersonGroupRelationList;

class Group extends DataObject {

#					NAME				TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 	$name;			#	str			*			.{1,30}		=			=
	public ?string 	$description;	#	str						.*			=			=
	public ?array 	$persons;		#	arr[Person]
	//TODO ^^^^^ use PersonList

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


	function __construct() {
		parent::__construct();
		$this->relationlist = new PersonGroupRelationList();
	}


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);

		$relations = [];
		foreach($data as $persondata){
			if(empty($persondata['persongrouprelation_id'])){
				continue;
			}

			$person = new Person();
			$person->load_single($persondata, true);
			$this->persons[] = $person;

			$relation = new PersonGroupRelation();
			$relation->load($this, $person, $persondata);
			$relations[$relation->id] = $relation;
		}

		$this->relationlist->load($relations);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['group_id'];
		$this->longid = $data['group_longid'];
		$this->name = $data['group_name'];
		$this->description = $data['group_description'];

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
