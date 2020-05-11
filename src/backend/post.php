<?php
class Post extends ContentObject {
	public $overline;	# String(0-64)
	public $headline; 	# String(1-256)
	public $subline;	# String(0-256)
	public $teaser;		# String					// IDEA limit length
	public $author;		# String(1-128)				// IDEA use profile id
	public $timestamp;	# Integer[unix timestamp]	// IDEA publishdate, last edited, ...
	public $image;		# Image
	public $content;	# String


	public static function new() {
		$obj = new self();
		$obj->id = generate_id();
		return $obj;
	}
	
	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM posts LEFT JOIN images ON image_id = post_image_id WHERE post_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);

		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			return self::load($s->fetch());
		}
	}

	public static function pull_by_longid($longid) {
		global $pdo;

		$query = 'SELECT * FROM posts LEFT JOIN images ON image_id = post_image_id WHERE post_longid = :longid';
		$values = ['longid' => $longid];

		$s = $pdo->prepare($query);

		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			return self::load($s->fetch());
		}
	}

	public static function pull_all() {
		global $pdo;

		$query = 'SELECT * FROM posts';

		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$res = [];
			while($r = $s->fetch()){
				$res[] = self::load($r);
			}
			return $res;
		}
	}

	public static function load($data) {
		$obj = new self();

		$obj->id = $data['post_id'];
		$obj->longid = $data['post_longid'];
		$obj->overline = $data['post_overline'];
		$obj->headline = $data['post_headline'];
		$obj->subline = $data['post_subline'];
		$obj->teaser = $data['post_teaser'];
		$obj->author = $data['post_author'];
		$obj->timestamp = $data['post_timestamp'];

		if(isset($data['image_id'])){
			$obj->image = Image::load($data);
		}

		$obj->content = $data['post_content'];

		return $obj;
	}

	public function insert($data) {
		global $pdo;

		$this->import_longid($data['longid']);
		$this->import_overline($data['overline']);
		$this->import_headline($data['headline']);
		$this->import_subline($data['subline']);
		$this->import_teaser($data['teaser']);
		$this->import_author($data['author']);
		$this->import_content($data['content']);

		$this->timestamp = time();

		// TODO image routine (id or upload)

		$query = 'INSERT INTO posts (post_id, post_longid, post_overline, post_headline, post_subline, post_teaser,
			post_author, post_timestamp, post_image_id, post_content) VALUES (:id, :longid, :overline, :headline,
			:subline, :teaser, :author, :timestamp, :image_id, :content)';

		$values = [
			'id' => $this->id,
			'longid' => $this->longid,
			'overline' => $this->overline,
			'headline' => $this->headline,
			'subline' => $this->subline,
			'teaser' => $this->teaser,
			'author' => $this->author,
			'timestamp' => $this->timestamp,
			'content' => $this->content
		];

		if(isset($this->image)){
			$values['image_id'] = $this->image->id;
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		}
	}

	public function update($data) {
		global $pdo;

		$this->import_check_id_and_longid($data['id'], $data['longid']);
		$this->import_overline($data['overline']);
		$this->import_headline($data['headline']);
		$this->import_subline($data['subline']);
		$this->import_teaser($data['teaser']);
		$this->import_author($data['author']);
		$this->import_content($data['content']);

		// TODO image routine (id or upload)

		$query = 'UPDATE posts SET post_overline = :overline, post_headline = :headline, post_subline = :subline,
			post_teaser = :teaser, post_author = :author, post_image_id = :image_id, post_content = :content
			WHERE post_id = :id';

		$values = [
			'overline' => $this->overline,
			'headline' => $this->headline,
			'subline' => $this->subline,
			'teaser' => $this->teaser,
			'author' => $this->author,
			'content' => $this->content,
			'id' => $this->id
		];

		if(isset($this->image)){
			$values['image_id'] = $this->image->id;
		} else {
			$values['image_id'] = null;
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		}
	}

	public function delete() {
		global $pdo;

		$query = 'DELETE FROM posts WHERE post_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		}
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

	private function import_content($content) {
		$this->content = $content;
	}
}
?>
