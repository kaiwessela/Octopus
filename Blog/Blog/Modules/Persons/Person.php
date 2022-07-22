<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Persons\PersonList;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\Media\Image;
use \Octopus\Modules\Persons\PersonGroupRelationshipList;
use \Octopus\Modules\Persons\Groups\GroupList;

class Person extends Entity {
	# inherited from Entity:
	# protected string $id;
	# protected string $longid;

	protected ?string 						$name;
	protected ?MarkdownText 				$profile;
	protected ?Image 						$image;
	protected ?PersonGroupRelationshipList 	$groups;

	protected static array $attributes;

	const DB_TABLE = 'persons';
	const DB_PREFIX = 'person';

	const LIST_CLASS = PersonList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'profile' => MarkdownText::class,
		'image' => Image::class,
		'groups' => PersonGroupRelationshipList::class,
		'number' => 'contextual',
		'group' => 'contextual'
	];
}
?>
