<?php
namespace Octopus\Modules\Persons\Groups;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Persons\Groups\GroupList;
use \Octopus\Modules\Persons\PersonGroupRelationshipList;
use \Octopus\Modules\StaticObjects\MarkdownContent;

class Group extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 					$name;
	protected ?MarkdownContent 			$description;
	protected ?PersonGroupRelationList 	$persons;

	protected static array $attributes;

	const DB_TABLE = 'groups';
	const DB_PREFIX = 'group';

	const LIST_CLASS = GroupList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'test' => '.{1,2}',
		'description' => MarkdownContent::class,
		'persons' => PersonGroupRelationshipList::class,
		'role' => 'contextual'
	];
}
?>
