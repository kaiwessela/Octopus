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
		$this->role = (int) $data['persongrouprelation_role'];
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

}
?>
