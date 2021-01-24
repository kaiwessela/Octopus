<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Group;

class PersonGroupRelation extends DataObjectRelation {
	public ?Person $person;
	public ?Group $group;
	public ?int $number;
	public ?string $role;

#	@inherited
#	public $id;
#
#	private $new;
#	private $empty;

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
		} else if($object instanceof Group){
			$this->group = &$group;
		}
	}


	public function load(array $data, /*Person|Group*/ $object) : void {
		$this->req('empty');

		$this->id = $data['persongrouprelation_id'];
		$this->number = (int) $data['persongrouprelation_number'];
		$this->role = $data['persongrouprelation_role'];

		if($object instanceof Person){
			$this->person = &$object;
			$this->group = new Group();
			$this->group->load_single($data);
		} else if($object instanceof Group){
			$this->group = &$object;
			$this->person = new Person();
			$this->person->load_single($data);
		}

		$this->set_empty(false);
	}


	public function export(?string $perspective = null) : ?PersonGroupRelation {
		if($this->is_empty()){
			return null;
		}

		$this->disabled = true;

		if($perspective == Person::class){
			$this->person = null;
		}

		if($perspective == Group::class){
			$this->group = null;
		}

		return $this;
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
