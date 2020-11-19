<?php
namespace Blog\Model\DataObjects\Relations;

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

	const PRIMARY_PROTOTYPE = new Person();
	const SECONDARY_PROTOTYPE = new Group();

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


	private function set_object(DataObject $object) {
		if($object instanceof Person){
			$this->primary_object = $object;
			return;
		}

		if($object instanceof Group){
			$this->secondary_object = $object;
			return;
		}
	}

}
?>
