<?php
namespace Blog\Modules\Posts;
use Octopus\Core\Model\Attributes\EntityReference;
use \Blog\Modules\Posts\Column;
use \Blog\Modules\Posts\Post;
use \Octopus\Core\Model\Relationship;
use \Octopus\Modules\Identifiers\ID;

class PostColumnRelationship extends Relationship {
	protected ID $id;
	protected EntityReference $post;
	protected EntityReference $column;

	const UNIQUE = true;
	const DB_TABLE = 'postcolumnrelations';


	protected static function define_attributes() : array {
		return [
			'id' 		=> ID::define(),
			'post' 		=> EntityReference::define(class:Post::class, identify_by:'id'),
			'column' 	=> EntityReference::define(class:Column::class, identify_by:'id'),
		];
	}


	const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'post' => [],
		'column' => []
	];
}
?>
