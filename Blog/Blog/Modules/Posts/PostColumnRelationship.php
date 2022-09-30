<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\Post;
use \Blog\Modules\Posts\Column;
use \Octopus\Modules\Identifiers\ID;
use \Octopus\Core\Model\Relationship;
use \Octopus\Core\Model\Attributes\EntityAttribute;

class PostColumnRelationship extends Relationship {
	protected ID $id;
	protected EntityAttribute $post;
	protected EntityAttribute $column;

	const UNIQUE = true;
	const DB_TABLE = 'postcolumnrelations';


	protected static function define_attributes() : array {
		return [
			'id' 		=> ID::define(),
			'post' 		=> EntityAttribute::define(class:Post::class, identify_by:'id'),
			'column' 	=> EntityAttribute::define(class:Column::class, identify_by:'id'),
		];
	}


	const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'post' => [],
		'column' => []
	];
}
?>
