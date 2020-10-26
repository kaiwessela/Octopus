<?php
namespace Blog\Model\DatabaseObjects;
use \Blog\Model\DatabaseObject;
use \Blog\Model\DatabaseObjects\Image;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\InputException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\RelationNonexistentException;
use InvalidArgumentException;

class Post extends DatabaseObject {
	public $overline;	# String(0-25)
	public $headline; 	# String(1-60)
	public $subline;	# String(0-40)
	public $teaser;		# String					// IDEA limit length
	public $author;		# String(1-50)				// IDEA use profile id
	public $timestamp;	# Integer[unix timestamp]	// IDEA publishdate, last edited, ... TODO 64bit
	public $image;		# Image
	public $content;	# String

	/* @inherited
	public $id;
	public $longid;

	private $new;
	private $empty;
	*/


	function __construct() {
		parent::__construct();
		$this->image = new Image();
	}

	public function pull($identifier) {
		$pdo = self::open_pdo();

		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM posts LEFT JOIN images ON image_id = post_image_id WHERE post_id = :id OR post_longid = :id';
		$values = ['id' => $identifier];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			$this->load($s->fetch());
		}
	}

	public static function pull_all($limit = null, $offset = null) {
		$pdo = self::open_pdo();

		$query = 'SELECT * FROM posts LEFT JOIN images ON image_id = post_image_id ORDER BY post_timestamp DESC';

		if($limit != null){
			if(!is_int($limit)){
				throw new InvalidArgumentException('Invalid argument: limit must be an integer.');
			}

			if($offset != null){
				if(!is_int($offset)){
					throw new InvalidArgumentException('Invalid argument: offset must be an integer.');
				}

				$query .= " LIMIT $offset, $limit";
			} else {
				$query .= " LIMIT $limit";
			}
		}

		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$res = [];
			while($r = $s->fetch()){
				$obj = new Post();
				$obj->load($r);
				$res[] = $obj;
			}
			return $res;
		}
	}

	public function load($data) {
		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$this->id = $data['post_id'];
		$this->longid = $data['post_longid'];
		$this->overline = $data['post_overline'];
		$this->headline = $data['post_headline'];
		$this->subline = $data['post_subline'];
		$this->teaser = $data['post_teaser'];
		$this->author = $data['post_author'];
		$this->timestamp = (int) $data['post_timestamp'];

		if(isset($data['image_id'])){
			$this->image->load($data);
		}

		$this->content = $data['post_content'];

		$this->empty = false;
		$this->new = false;
	}

	public static function count() {
		$pdo = self::open_pdo();

		$query = 'SELECT COUNT(*) FROM posts';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	public function push() {
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

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
			$query = 'INSERT INTO posts (post_id, post_longid, post_overline, post_headline,
				post_subline, post_teaser, post_author, post_timestamp, post_image_id,
				post_content) VALUES (:id, :longid, :overline, :headline, :subline, :teaser,
				:author, :timestamp, :image_id, :content)';

			$values['longid'] = $this->longid;
		} else {
			$query = 'UPDATE posts SET post_overline = :overline, post_headline = :headline,
				post_subline = :subline, post_teaser = :teaser, post_author = :author,
				post_timestamp = :timestamp, post_image_id = :image_id, post_content = :content
				WHERE post_id = :id';
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = false;
		}
	}

	public function import($data) {
		$errorlist = new InputFailedException();

		if($this->is_new()){
			try {
				$this->import_longid($data['longid']);
			} catch(InputException $e){
				$errorlist->push($e);
			}
		} else {
			try {
				$this->import_check_id_and_longid($data['id'], $data['longid']);
			} catch(InputException $e){
				$errorlist->push($e);
			}
		}

		$importconfig = [
			'overline' => [
				'required' => false,
				'pattern' => '^.{0,25}$'
			],
			'headline' => [
				'required' => true,
				'pattern' => '^.{1,60}$'
			],
			'subline' => [
				'required' => false,
				'pattern' => '^.{0,40}$'
			],
			'author' => [
				'required' => true,
				'pattern' => '^.{1,50}$'
			]
		];

		$this->import_standardized($data, $importconfig, $errorlist);

		$this->teaser = $data['teaser'];
		$this->content = $data['content'];

		try {
			$this->import_timestamp($data['timestamp']);
		} catch(InputException $e){
			$errorlist->push($e);
		}

		try {
			$this->import_image($data);
		} catch(InputFailedException $e){
			$errorlist->merge($e, 'image');
		} catch(InputException $e){
			$errorlist->push($e);
		}

		if(!$errorlist->is_empty()){
			throw $errorlist;
		}

		$this->empty = false;
	}

	public function delete() {
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		if($this->is_new()){
			throw new WrongObjectStateException('not new');
		}

		$query = 'DELETE FROM posts WHERE post_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = true;
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

	private function import_timestamp($timestamp) {
		if(empty($timestamp)){
			throw new MissingValueException('timestamp', 'unix timestamp');
		} else if(!is_numeric($timestamp)){
			throw new IllegalValueException('timestamp', $timestamp, 'unix timestamp');
		} else {
			$this->timestamp = (int) $timestamp;
		}
	}

	private function import_image($data) {
		$image = new Image();

		if(isset($data['image_id'])){
			try {
				$image->pull($data['image_id']);
			} catch(EmptyResultException $e){
				throw new RelationNonexistentException('image_id', $data['image_id'], 'Image');
			}

			$this->image = $image;
		} else if(isset($data['image'])){
			$image->generate();
			$image->import($data['image']);
			$image->push();
			$this->image = $image;
			// TODO improve this
		}
	}
}
?>
