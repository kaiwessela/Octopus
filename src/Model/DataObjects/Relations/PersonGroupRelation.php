<?php
namespace Blog\Model\DataObjects\Relations;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Group;

class PersonGroupRelation extends DataObjectRelation {

	public $number;
	public $role;

#	@inherited
#	public $id;
#	public $primary_object;
#	public $secondary_object;
#
#	private $new;
#	private $empty;

	const UNIQUE = false;

	const PRIMARY_ALIAS = 'person';
	const SECONDARY_ALIAS = 'group';

	const FIELDS = [
		'number' => [
			'type' => 'integer',
			'required' => false
		],
		'role' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,40}'
		]
	];


	public function load(DataObject $object1, DataObject $object2, $data = []) {
		parent::load($object1, $object2, $data);

		$this->id = $data['persongrouprelation_id'];
		$this->number = (int) $data['persongrouprelation_number'];
		$this->role = $data['persongrouprelation_role'];
	}

	protected function set_object(DataObject $object) {
		if($object instanceof Person){
			$this->primary_object = $object;
			return;
		}

		if($object instanceof Group){
			$this->secondary_object = $object;
			return;
		}
	}

	protected function get_primary_prototype() {
		return new Person();
	}

	protected function get_secondary_prototype() {
		return new Group();
	}

	public function export() {
		if($this->is_empty()){
			return null;
		}

		$export = [
			'id' => $this->id,
			'primary_id' => $this->primary_object->id,
			'secondary_id' => $this->secondary_object->id,
			'number' => $this->number,
			'role' => $this->role
		];

		$export[$this::PRIMARY_ALIAS . '_id'] = $this->primary_object->id;
		$export[$this::SECONDARY_ALIAS . '_id'] = $this->secondary_object->id;

		return $export;
	}

	protected function db_export() {
		$values = [
			'id' => $this->id,
			'number' => $this->number,
			'role' => $this->role
		];

		if($this->is_new()){
			$values['person_id'] = $this->primary_object->id;
			$values['group_id'] = $this->secondary_object->id;
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
