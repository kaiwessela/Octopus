<?php
namespace Blog\Backend\Models;
use \Blog\Backend\Model;
use \Blog\Backend\ModelTrait;
use \Blog\Backend\Models\Image;
use \Blog\Backend\Exceptions\WrongObjectStateException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Backend\Exceptions\InvalidInputException;
use InvalidArgumentException;

class Post extends Model {
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
		if($this->is_new()){
			$this->import_longid($data['longid']);
		} else {
			$this->import_check_id_and_longid($data['id'], $data['longid']);
		}

		$this->import_overline($data['overline']);
		$this->import_headline($data['headline']);
		$this->import_subline($data['subline']);
		$this->import_teaser($data['teaser']);
		$this->import_author($data['author']);
		$this->import_timestamp($data['timestamp'] ?? time());
		$this->import_content($data['content']);
		$this->import_image($data);

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

	private function import_overline($overline) {
		if(!isset($overline)){
			$this->overline = null;
		} else if(!preg_match('/^.{0,64}$/', $overline)){
			throw new InvalidInputException('overline', '.{0,64}', $overline);
		} else {
			$this->overline = $overline;
		}
	}

	private function import_headline($headline) {
		if(!isset($headline)){
			throw new InvalidInputException('headline', '.{1,256}');
		} else if(!preg_match('/^.{1,256}$/', $headline)){
			throw new InvalidInputException('headline', '.{1,256}', $headline);
		} else {
			$this->headline = $headline;
		}
	}

	private function import_subline($subline) {
		if(!isset($subline)){
			$this->subline = null;
		} else if(!preg_match('/^.{0,256}$/', $subline)){
			throw new InvalidInputException('subline', '.{0,256}', $subline);
		} else {
			$this->subline = $subline;
		}
	}

	private function import_teaser($teaser) {
		$this->teaser = $teaser;
	}

	private function import_author($author) {
		if(!isset($author)){
			throw new InvalidInputException('author', '.{1,128}');
		} else if(!preg_match('/^.{1,128}$/', $author)){
			throw new InvalidInputException('author', '.{1,128}', $author);
		} else {
			$this->author = $author;
		}
	}

	public function import_timestamp($timestamp) {
		if(!isset($timestamp)){
			throw new InvalidInputException('timestamp', '[unix timestamp]');
		} else if(!is_numeric($timestamp)){
			throw new InvalidInputException('timestmap', '[unix timestamp]', $timestamp);
		} else {
			$this->timestamp = (int) $timestamp;
		}
	}

	private function import_content($content) {
		$this->content = $content;
	}

	private function import_image($data) {
		if($data['image_id']){
			try {
				$image = new Image();
				$image->pull($data['image_id']);
			} catch(EmptyResultException $e){
				throw new InvalidInputException('image_id', 'image id; No Image Found', $data['image_id']); // TODO better exc.-> api index.php
			} catch(DatabaseException $e){
				throw $e;
			}

			$this->image = $image;
		} else if(isset($data['image'])){
			try {
				$image = new Image();
				$image->generate();
				$image->import($data['image']);
				$image->push();
			} catch(Exception $e){
				throw new InvalidInputException('image', 'wrong exception but look in php', 'will be changed later'); // TODO
				// TODO exception handling with and in images
			}

			$this->image = $image;
		}
	}
}
?>
