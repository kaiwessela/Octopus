<?php
namespace Blog\Model\DatabaseObjects;
use \Blog\Model\DataObject;
use \Blog\Model\DatabaseObjects\Image;

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

#	private $new;
#	private $empty;

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
			'type' => new Image(),
			'required' => false
		],
		'content' => [
			'type' => 'string'
		]
	];


	function __construct() {
		parent::__construct();
		$this->image = new Image();
	}

	public function load($data) {
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

		$this->set_new(false);
		$this->set_empty(false);
	}

	private function push_children() {
		if($this->image->is_new()){
			$this->image->push();
		}
	}

	private function db_export() {
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
	}

	public function export() {
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

		return $obj;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM post
LEFT JOIN images ON image_id = post_image_id
WHERE post_id = :id OR post_longid = :id;
SQL; #---|

	const DELETE_QUERY = <<<SQL
DELETE FROM posts
WHERE post_id = :id
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

}
?>
