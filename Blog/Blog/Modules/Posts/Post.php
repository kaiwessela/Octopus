<?php
namespace Blog\Modules\Posts;
use Blog\Modules\Images\Image;
use Blog\Modules\Posts\PostColumnRelationshipList;
use Blog\Modules\Posts\PostList;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Attributes\RelationshipsReference;
use Octopus\Core\Model\Attributes\StaticObjectAttribute;
use Octopus\Core\Model\Entity;
use Octopus\Modules\Identifiers\ID;
use Octopus\Modules\Identifiers\StringIdentifier;
use Octopus\Modules\Standard\Model\Attributes\Strng;
use Octopus\Modules\StaticObjects\MarkdownText;
use Octopus\Modules\Timestamp\Timestamp;
use Octopus\Modules\Timestamp\TimestampAttribute;

class Post extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Strng $overline;
	protected Strng $headline;
	protected Strng $subline;
	protected Strng $teaser;
	protected Strng $author;
	protected TimestampAttribute $timestamp;
	protected StaticObjectAttribute $content;
	protected EntityReference $image;
	protected RelationshipsReference $columns;

	protected const DB_TABLE = 'posts';
	protected const LIST_CLASS = PostList::class;


	protected static function define_attributes() : array {
		return [
			'id' 			=> ID::define(),
			'longid' 		=> StringIdentifier::define(is_editable:false),
			'overline' 		=> Strng::define(min:0, max:50),
			'headline' 		=> Strng::define(min:1, max:100),
			'subline' 		=> Strng::define(min:1, max:100),
			'teaser' 		=> Strng::define(),
			'author' 		=> Strng::define(min:1, max:100),
			'timestamp' 	=> TimestampAttribute::define(),
			// 'content' 		=> StaticObjectAttribute::define(class:MarkdownText::class),
			'image' 		=> EntityReference::define(class:Image::class, identify_by:'id'),
			'columns' 		=> RelationshipsReference::define(PostColumnRelationshipList::class),
		];
	}


	protected const PRIMARY_IDENTIFIER = 'id';


	protected const DEFAULT_PULL_ATTRIBUTES = [
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
