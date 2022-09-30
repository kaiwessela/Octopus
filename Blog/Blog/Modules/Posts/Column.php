<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\ColumnList;
use \Blog\Modules\Posts\PostColumnRelationshipList;
use \Octopus\Modules\Identifiers\ID;
use \Octopus\Modules\Identifiers\StringIdentifier;
use \Octopus\Modules\Primitives\Stringy;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;

class Column extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Stringy $name;
	protected StaticObjectAttribute $description;
	protected RelationshipAttribute $posts;

	const DB_TABLE = 'columns';
	const LIST_CLASS = ColumnList::class;


	protected static function define_attributes() : array {
		return [
			'id' 			=> ID::define(),
			'longid' 		=> StringIdentifier::define(is_editable:false),
			'name' 			=> Stringy::define(min:1, max:60),
			'description' 	=> StaticObjectAttribute::define(class:MarkdownText::class),
			'posts' 		=> RelationshipAttribute::define(PostColumnRelationshipList::class)
		];
	}

	const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'longid' => true,
		'name' => true,
		'description' => true,
		'posts' => []
	];
}
?>
