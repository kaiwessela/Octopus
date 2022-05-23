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
	protected StringAttribute $overline;
	protected StringAttribute $headline;
	protected StringAttribute $subline;
	protected StringAttribute $teaser;
	protected StringAttribute $author;
	protected Timestamp $created_at;
	protected MarkdownText $content;

	protected ?Image $image;
	protected ?PostColumnRelationshipList $columns;
	// protected ?Collection $collection;


	protected static array $attributes;


	const DB_TABLE = 'posts';
	const DB_PREFIX = 'post';

	const LIST_CLASS = PostList::class;

	protected static function define_attributes() : array {
		return [
			'id' 			=> IDAttribute::define(),
			'longid' 		=> IdentifierAttribute::define(required:true, editable:false),
			'overline' 		=> StringAttribute::define(min:0, max:50),
			'headline' 		=> StringAttribute::define(min:1, max:100),
			'subline' 		=> StringAttribute::define(min:1, max:100),
			'teaser' 		=> StringAttribute::define(),
			'author' 		=> StringAttribute::define(min:1, max:100),
			'created_at' 	=> Timestamp::define(),
			'content' 		=> MarkdownText::define(allow_html:true, collection:'collection'),
			'image' 		=> EntityAttribute::define(Image::class);
			'columns' 		=> RelationshipAttribute::define(PostColumnRelationshipList::class);
		];
	}







	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 						$overline;
	protected ?string 						$headline;
	protected ?string 						$subline;
	protected ?string 						$teaser;
	protected ?string 						$author;
	protected ?Timestamp 					$timestamp;
	protected ?Image 						$image;
	protected ?MarkdownText 				$content;
	protected ?PostColumnRelationshipList 	$columns;
	// protected ?Collection					$collection;

	protected static array $attributes;

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
}
?>
