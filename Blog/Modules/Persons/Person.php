<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\DataObject;
use \Octopus\Modules\DataTypes\MarkdownContent;
use \Octopus\Modules\Media\Image;
use \Octopus\Modules\Persons\PersonGroupRelationList;
use \Octopus\Modules\Persons\Groups\GroupList;

class Person extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected ?string 					$name;
	protected ?MarkdownContent 			$profile;
	protected ?Image 					$image;
	protected ?PersonGroupRelationList 	$groups;

	protected static array $properties;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		#'profile' => MarkdownContent::class,
		#'image' => Image::class,
		'groups' => PersonGroupRelationList::class,
		#'number' => 'contextual',
		#'group' => 'contextual'
	];

	const DB_TABLE = 'persons';
	const DB_PREFIX = 'person';
}
?>
