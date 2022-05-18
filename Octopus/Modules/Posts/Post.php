<?php
namespace Octopus\Modules\Posts;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\Collection;
use \Octopus\Modules\Posts\PostList;
use \Octopus\Modules\Images\Image;
use \Octopus\Modules\Posts\PostColumnRelationshipList;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\StaticObjects\Timestamp;

class Post extends Entity {

	const DB_TABLE = 'posts';
	const DB_PREFIX = 'post';

	const LIST_CLASS = PostList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'overline' => '.{0,50}',
		'headline' => '.{1,100}',
		'subline' => '.{0,100}',
		'teaser' => 'string',
		'author' => '.{1,100}',
		'timestamp' => Timestamp::class,
		'image' => Image::class,
		'content' => [
			'class' => MarkdownText::class,
			'allow_html' => true,
			'collection' => 'collection'
		],
		'columns' => PostColumnRelationshipList::class,
		// 'collection' => Collection::class
	];


	public static function define_attributes() : array {
		return [
			'id' 		=> IDAttribute::define(),
			'longid' 	=> IdentifierAttribute::define(required:true, editable:false),
			'overline' 	=> StringAttribute::define(min:0, max:50),
			'headline' 	=> StringAttribute::define(min:1, max:100),
			'subline' 	=> StringAttribute::define(min:1, max:100),
			'teaser' 	=> StringAttribute::define(),
			'author' 	=> StringAttribute::define(min:1, max:100),
			'timestamp' => Timestamp::define(),
			'image' 	=> EntityAttribute::define(class:Image::class),
			'content' 	=> MarkdownText::define(allow_html:true, collection:'collection'),
			'columns' 	=> RelationshipAttribute::define(class:PostColumnRelationshipList::class),
			// 'collection' => Collection::define()
		];
	}


	public static function define_child_entities() : array {
		return [
			'image' => Image::define()
		];
	}
}
?>
