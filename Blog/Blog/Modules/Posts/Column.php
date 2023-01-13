<?php
namespace Blog\Modules\Posts;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Modules\Standard\Model\Attributes\Strng;
use \Blog\Modules\Posts\ColumnList;
use \Blog\Modules\Posts\PostColumnRelationshipList;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Identifiers\ID;
use \Octopus\Modules\Identifiers\StringIdentifier;
use \Octopus\Modules\StaticObjects\MarkdownText;

class Column extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Strng $name;
	protected StaticObjectAttribute $description;
	protected RelationshipsReference $posts;

	const DB_TABLE = 'columns';
	const LIST_CLASS = ColumnList::class;


	protected static function define_attributes() : array {
		return [
			'id' 			=> ID::define(),
			'longid' 		=> StringIdentifier::define(is_editable:false),
			'name' 			=> Strng::define(min:1, max:60),
			// 'description' 	=> StaticObjectAttribute::define(class:MarkdownText::class),
			'posts' 		=> RelationshipsReference::define(PostColumnRelationshipList::class)
		];
	}


	const PRIMARY_IDENTIFIER = 'id';


	const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'longid' => true,
		'name' => true,
		'description' => true,
		'posts' => []
	];
}
?>
