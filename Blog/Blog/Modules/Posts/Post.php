<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\PostList;
use \Blog\Modules\Posts\PostColumnRelationshipList;
use \Blog\Modules\Images\Image;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Identifiers\ID;
use \Octopus\Modules\Identifiers\StringIdentifier;
use \Octopus\Modules\Primitives\Stringy;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\Timestamp\Timestamp;
use \Octopus\Core\Model\Attributes\EntityAttribute;
use \Octopus\Core\Model\Attributes\RelationshipAttribute;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;

class Post extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Stringy $overline;
	protected Stringy $headline;
	protected Stringy $subline;
	protected Stringy $teaser;
	protected Stringy $author;
	protected StaticObjectAttribute $timestamp;
	protected StaticObjectAttribute $content;
	protected EntityAttribute $image;
	protected RelationshipAttribute $columns;

	protected const DB_TABLE = 'posts';
	protected const LIST_CLASS = PostList::class;


	protected static function define_attributes() : array {
		return [
			'id' 			=> ID::define(),
			'longid' 		=> StringIdentifier::define(is_editable:false),
			'overline' 		=> Stringy::define(min:0, max:50),
			'headline' 		=> Stringy::define(min:1, max:100),
			'subline' 		=> Stringy::define(min:1, max:100),
			'teaser' 		=> Stringy::define(),
			'author' 		=> Stringy::define(min:1, max:100),
			'timestamp' 	=> StaticObjectAttribute::define(class:Timestamp::class),
			'content' 		=> StaticObjectAttribute::define(class:MarkdownText::class),
			'image' 		=> EntityAttribute::define(class:Image::class, identify_by:'id'),
			'columns' 		=> RelationshipAttribute::define(PostColumnRelationshipList::class),
		];
	}


	const DEFAULT_PULL_ATTRIBUTES = [
		'id' 			=> true,
		'longid' 		=> true,
		'overline' 		=> true,
		'headline' 		=> true,
		'subline' 		=> true,
		'teaser' 		=> true,
		'author' 		=> true,
		'timestamp' 	=> true,
		'content' 		=> true,
		'image' 		=> [],
		'columns' 		=> []
	];

}
?>
