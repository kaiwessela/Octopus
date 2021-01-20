<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Relations\PostColumnRelation;
use \Blog\Model\DataObjects\Relations\Lists\PostColumnRelationList;
use \Blog\Model\DataTypes\Timestamp;
use \Blog\Model\DataTypes\MarkdownContent;

class Post extends DataObject {

#							NAME			TYPE			REQUIRED	PATTERN		DB NAME		DB VALUE
	public ?string 			$overline;	#	str							.{0,25}		=			=
	public string 			$headline; 	#	str				*			.{1,60}		=			=
	public ?string 			$subline;	#	str							.{0,40}		=			=
	public ?string 			$teaser;	#	str							.*			=			=
	public string 			$author;	#	str				*			.{1,50}		=			=
	public Timestamp 		$timestamp;	#	str(timestamp)	*						=			=
	public ?Image 			$image;		#	Image									image_id	Image->id
	public ?MarkdownContent $content;	#	str							.*			=			=
	public ?array 			$columns;	#	arr[Column]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'overline' => '.{0,25}',
		'headline' => '.{1,60}',
		'subline' => '.{0,40}',
		'teaser' => null,
		'author' => '.{1,50}',
		'timestamp' => Timestamp::class,
		'image' => Image::class,
		'content' => MarkdownContent::class,
		'columns' => PostColumnRelationList::class
	];


	function __construct() {
		parent::__construct();
		$this->relationlist = new PostColumnRelationList();
	}


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);

		$relations = [];
		foreach($data as $columndata){
			if(empty($columndata['postcolumnrelation_id'])){
				continue;
			}

			$column = new Column();
			$column->load_single($columndata, true);
			$this->columns[] = $column;

			$relation = new PostColumnRelation();
			$relation->load($column, $this, $columndata);
			$relations[$relation->id] = $relation;
		}

		$this->relationlist->load($relations);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['post_id'];
		$this->longid = $data['post_longid'];
		$this->overline = $data['post_overline'];
		$this->headline = $data['post_headline'];
		$this->subline = $data['post_subline'];
		$this->teaser = $data['post_teaser'];
		$this->author = $data['post_author'];

		$this->timestamp = new Timestamp($data['post_timestamp']);

		$this->image = empty($data['image_id']) ? null : new Image();
		$this->image?->load_single($data);

		$this->content = empty($data['post_content'])
			? null : new MarkdownContent($data['post_content']);

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'overline' => $this->overline,
			'headline' => $this->headline,
			'subline' => $this->subline,
			'teaser' => $this->teaser,
			'author' => $this->author,
			'timestamp' => (string) $this->timestamp,
			'image_id' => $this->image?->id,
			'content' => (string) $this->content
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	protected function push_children() : void {
		if($this->image?->is_new()){
			$this->image->push();
		}
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM posts
LEFT JOIN images ON image_id = post_image_id
LEFT JOIN postcolumnrelations ON postcolumnrelation_post_id = post_id
LEFT JOIN columns ON column_id = postcolumnrelation_column_id
WHERE post_id = :id OR post_longid = :id
SQL; #---|

	const COUNT_QUERY = null;

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

	const DELETE_QUERY = <<<SQL
DELETE FROM posts
WHERE post_id = :id
SQL; #---|

}
?>
