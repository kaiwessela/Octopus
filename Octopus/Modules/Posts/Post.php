<?php # Post.php 2021-10-04 beta
namespace Blog\Modules\Posts;
use \Blog\Core\Model\DataObject;
use \Blog\Core\Model\DataObjectCollection;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\DataTypes\Timestamp;
use \Blog\Modules\Media\Image;
use \Blog\Modules\Posts\PostColumnRelationList;
use \Blog\Modules\Posts\Columns\ColumnList;

class Post extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected ?string 					$overline;
	protected string 					$headline;
	protected ?string 					$subline;
	protected ?string 					$teaser;
	protected string 					$author;
	protected Timestamp 				$timestamp;
	protected ?Image 					$image;
	protected ?MarkdownContent 			$content;
	protected ?PostColumnRelationList 	$columnrelations;
	protected ?DataObjectCollection		$collection;


	const PROPERTIES = [
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
			'class' => MarkdownContent::class,
			'allow_html' => true,
			'collection' => 'collection'
		],
		'columnrelations' => PostColumnRelationList::class,
		'collection' => DataObjectCollection::class
	];

	const RELATIONLIST_EXTRACTS = [ // TODO
		'columns' => [ColumnList::class, 'columnrelations']
	];


	const DB_PREFIX = 'post';


	const QUERY_PULL_COLUMNS = 'posts.*';

	const QUERY_PULL = 'SELECT '.self::QUERY_PULL_COLUMNS.', '.Medium::QUERY_PULL_COLUMNS.<<<SQL
	FROM posts
LEFT JOIN media ON

SELECT  FROM posts
LEFT JOIN
SQL;


	const PULL_QUERY = <<<SQL
SELECT * FROM posts
LEFT JOIN media ON medium_id = post_image_id
LEFT JOIN postcolumnrelations ON postcolumnrelation_post_id = post_id
LEFT JOIN columns ON column_id = postcolumnrelation_column_id
WHERE post_id = :id OR post_longid = :id
SQL; #---|

	const INSERT_QUERY = <<<SQL
INSERT INTO posts (
	post_id,
	post_longid,
	post_overline,
	post_headline,
	post_subline,
	post_teaser,
	post_author,
	post_timestamp,
	post_image_id,
	post_content
) VALUES (
	:id,
	:longid,
	:overline,
	:headline,
	:subline,
	:teaser,
	:author,
	:timestamp,
	:image_id,
	:content
)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE posts SET
	post_overline = :overline,
	post_headline = :headline,
	post_subline = :subline,
	post_teaser = :teaser,
	post_author = :author,
	post_timestamp = :timestamp,
	post_image_id = :image_id,
	post_content = :content
WHERE post_id = :id
SQL; #---|

	const DELETE_QUERY = 'DELETE FROM posts WHERE post_id = :id';

}
?>
