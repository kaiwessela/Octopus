<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Relations\PostColumnRelation;
use \Blog\Model\DataObjects\Relations\Lists\PostColumnRelationList;

class Post extends DataObject {

#			NAME			TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public $overline;	#	str						.{0,25}		=			=
	public $headline; 	#	str			*			.{1,60}		=			=
	public $subline;	#	str						.{0,40}		=			=
	public $teaser;		#	str						.*			=			=
	public $author;		#	str			*			.{1,50}		=			=
	public $timestamp;	#	int			*						=			=
	public $image;		#	Image								image_id	Image->id
	public $content;	#	str						.*			=			=
	public $columns;	#	arr[Column]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const FIELDS = [
		'overline' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,25}'
		],
		'headline' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,60}'
		],
		'subline' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,40}'
		],
		'teaser' => [
			'type' => 'string'
		],
		'author' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,50}'
		],
		'timestamp' => [
			'type' => 'integer',
			'required' => true
		],
		'image' => [
			'type' => 'Image',
			'required' => false
		],
		'content' => [
			'type' => 'string'
		],
		'columns' => [
			'type' => 'relationlist'
		]
	];


	function __construct() {
		parent::__construct();
		$this->image = new Image();
		$this->relationlist = new PostColumnRelationList();
	}


	public function load($data, $block_recursion = false) {
		$this->req('empty');

		$this->id = $data['post_id'];
		$this->longid = $data['post_longid'];
		$this->overline = $data['post_overline'];
		$this->headline = $data['post_headline'];
		$this->subline = $data['post_subline'];
		$this->teaser = $data['post_teaser'];
		$this->author = $data['post_author'];
		$this->timestamp = (int) $data['post_timestamp'];

		if(!empty($data['image_id'])){
			$this->image->load($data);
		}

		$this->content = $data['post_content'];

		if(!$block_recursion){
			$relations = [];
			foreach($data as $columndata){
				$column = new Column();
				$column->load($columndata, true);
				$this->columns[] = $column;

				$relation = new PostColumnRelation();
				$relation->load($column, $this, $columndata);
				$relations[$relation->id] = $relation;
			}

			$this->relationlist->load($relations);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		if($this->is_empty()){
			return null;
		}

		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->overline = $this->overline;
		$obj->headline = $this->headline;
		$obj->subline = $this->subline;
		$obj->teaser = $this->teaser;
		$obj->author = $this->author;
		$obj->timestamp = $this->timestamp;
		$obj->content = $this->content;
		$obj->image = $this->image->export();

		if(!$block_recursion){
			$obj->columns = [];
			foreach($this->columns as $column){
				$obj->columns[] = $column->export(true);
			}
		}

		$obj->relations = $this->relationlist->export();

		return $obj;
	}


	protected function db_export() {
		$values = [
			'id' => $this->id,
			'overline' => $this->overline,
			'headline' => $this->headline,
			'subline' => $this->subline,
			'teaser' => $this->teaser,
			'author' => $this->author,
			'timestamp' => $this->timestamp,
			'content' => $this->content
		];

		if(!$this->image->is_empty()){
			$values['image_id'] = $this->image->id;
		} else {
			$values['image_id'] = '';
		}

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	protected function push_children() {
		if($this->image->is_new()){
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
