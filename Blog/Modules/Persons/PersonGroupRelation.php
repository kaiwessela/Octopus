<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Group;
use TypeError;

class PersonGroupRelation extends DataObjectRelation {
	public ?Person 	$person;
	public ?Group 	$group;
	public ?int 	$number;
	public ?string 	$role;

#	@inherited
#	public string $id;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;

	const UNIQUE = false;

	const OBJECTS = [
		'person' => Person::class,
		'group' => Group::class
	];

	const PROPERTIES = [
		'number' => null,
		'role' => '.{0,40}'
	];


	public function generate(/*Person|Group*/ $object) : void {
		parent::generate($object);

		if($object instanceof Person){
			$this->person = &$object;
			$this->group = null;
		} else if($object instanceof Group){
			$this->group = &$object;
			$this->person = null;
		} else {
			throw new TypeError('Invalid type of $object.');
		}
	}


	public function load(array $data, /*Person|Group*/ $object) : void {
		$this->require_empty();

		if($object instanceof Person){
			$this->person = &$object;
			$this->group = new Group();
			$this->group->load($data, true);
		} else if($object instanceof Group){
			$this->group = &$object;
			$this->person = new Person();
			$this->person->load($data, true);
		} else {
			throw new TypeError('Invalid type of $object.');
		}

		$this->id = $data['persongrouprelation_id'];
		$this->number = (int) $data['persongrouprelation_number'];
		$this->role = $data['persongrouprelation_role'];

		$this->set_not_empty();
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'number' => $this->number,
			'role' => $this->role
		];

		if($this->is_new()){
			$values['person_id'] = $this->person->id;
			$values['group_id'] = $this->group->id;
		}

		return $values;
	}


	const INSERT_QUERY = <<<SQL
INSERT INTO persongrouprelations (
	persongrouprelation_id,
	persongrouprelation_person_id,
	persongrouprelation_group_id,
	persongrouprelation_number,
	persongrouprelation_role
) VALUES (
	:id,
	:person_id,
	:group_id,
	:number,
	:role
)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE persongrouprelations
SET persongrouprelation_number = :number, persongrouprelation_role = :role
WHERE persongrouprelation_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM persongrouprelations
WHERE persongrouprelation_id = :id
SQL; #---|

}
?>
