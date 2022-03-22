<?php
namespace Octopus\Modules\Posts;
use \Octopus\Core\Model\Relationship;
use \Octopus\Modules\Posts\Post;
use \Octopus\Modules\Posts\Columns\Column;

class PostColumnRelationship extends Relationship {
	# inherited from Relationship:
	# protected readonly string $id;

	protected ?Post 	$post;
	protected ?Column 	$column;

	protected static array $attributes;

	const UNIQUE = true;

	const DB_TABLE = 'postcolumnrelations';
	const DB_PREFIX = 'postcolumnrelation';

	const ATTRIBUTES = [
		'id' => 'id',
		'post' => Post::class,
		'column' => Column::class,
	];
}
?>
