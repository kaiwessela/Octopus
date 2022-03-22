<?php
namespace Octopus\Modules\Posts\Columns;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Posts\Columns\ColumnList;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\Posts\PostColumnRelationshipList;

class Column extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 						$name;
	protected ?MarkdownText					$description;
	protected ?PostColumnRelationshipList 	$postrelations;

	protected static array $attributes;

	const DB_TABLE = 'columns';
	const DB_PREFIX = 'column';

	const LIST_CLASS = ColumnList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'name' => '.{1,60}',
		'description' => MarkdownText::class,
		'postrelations' => PostColumnRelationshipList::class
	];
}
?>
