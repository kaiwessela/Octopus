<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\DataObjectRelation;
use \Octopus\Core\Model\DataObject;
use \Octopus\Modules\Persons\Person;
use \Octopus\Modules\Persons\Groups\Group;
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

protected static array $properties;

	const UNIQUE = false;

	const DB_TABLE = 'persongrouprelations';
	const DB_PREFIX = 'persongrouprelation';


	const PROPERTIES = [
		'id' => 'id',
		'group' => Group::class,
		'person' => Person::class,
		'number' => 'int',
		'role' => '.{0,40}'
	];
}
?>
