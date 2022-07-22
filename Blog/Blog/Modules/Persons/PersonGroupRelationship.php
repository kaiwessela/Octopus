<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\Relationship;
use \Octopus\Modules\Persons\Person;
use \Octopus\Modules\Persons\Groups\Group;

class PersonGroupRelationship extends Relationship {
	# inherited from Relationship:
	# protected readonly string $id;

	protected ?Person 	$person;
	protected ?Group 	$group;
	protected ?int 		$number;
	protected ?string 	$role;

	protected static array $attributes;

	const UNIQUE = false;

	const DB_TABLE = 'persongrouprelations';
	const DB_PREFIX = 'persongrouprelation';

	const ATTRIBUTES = [
		'id' => 'id',
		'group' => Group::class,
		'person' => Person::class,
		'number' => 'int',
		'role' => '.{0,40}'
	];
}
?>
