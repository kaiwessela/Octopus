<?php
namespace Octopus\Modules\Persons\Groups;
use \Octopus\Core\Model\DataObject;
use \Octopus\Modules\DataTypes\MarkdownContent;
use \Octopus\Modules\Persons\PersonGroupRelationList;
use \Octopus\Modules\Persons\PersonList;

class Group extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 					$name;
	protected ?MarkdownContent 			$description;
	protected ?PersonGroupRelationList 	$persons;

	protected static array $properties;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'test' => '.{1,2}',
		#'description' => MarkdownContent::class,
		'persons' => PersonGroupRelationList::class,
		#'role' => 'contextual'
	];

	const DB_TABLE = 'groups';
	const DB_PREFIX = 'group';
}
?>
