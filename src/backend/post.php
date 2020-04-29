<?php
class Post {
	public $id; 		# String(8)[base16]
	public $longid; 	# String(1-128)[a-z0-9-]
	public $overline;	# String(0-64)
	public $headline; 	# String(1-256)
	public $subline;	# String(0-256)
	public $teaser;		# String					// IDEA limit length
	public $author;		# String(1-128)				// IDEA use profile id
	public $timestamp;	# Integer[unix timestamp]	// IDEA publishdate, last edited, ...
	public $image;		# Image
	public $content;	# String


	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM posts WHERE post_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function pull_by_longid($longid) {
		global $pdo;

		$query = 'SELECT * FROM posts WHERE post_longid = :longid';
		$values = ['longid' => $longid];

		$s = $pdo->prepare($query);
		$s->execute($values);

		if($s->rowCount() == 1){
			return self::load($s->fetch());
		} else {
			throw new ObjectNotFoundException();
		}
	}

	public static function pull_all() {
		global $pdo;

		$query = 'SELECT * FROM posts';

		$s = $pdo->prepare($query);
		$s->execute(array());

		if($s->rowCount() > 0){
			$res = [];
			while($r = $s->fetch()){
				$res[] = self::load($r);
			}
			return $res;
		} else {
			throw new ObjectNotFoundException();
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
		$obj->image = Image::pull_by_id($data['post_image_id']);
		$obj->content = $data['post_content'];

		return $obj;
	}

	public function insert($data) {
		global $pdo;

		if(isset($data['longid'])){
			if(preg_match('/^[a-z0-9-]{1,128}$/', $data['longid'])){
				$found = true;
				try {
					$test_image = self::pull_by_longid($data['longid']);
				} catch(ObjectNotFoundException $e){
					$found = false;
				}

				if($found){
					return false;
				} else {
					$this->longid = $data['longid'];
				}
			} else {
				return false;
			}
		} else {
			return false;
		}

		if(isset($data['overline'])){
			if(preg_match('/^.{0,64}$/', $data['overline'])){
				$this->overline = $data['overline'];
			}
		}

		if(isset($data['headline'])){
			if(preg_match('/^.{1,256}$/', $data['headline'])){
				$this->headline = $data['headline'];
			}
		} else {
			return false;
		}

		if(isset($data['subline'])){
			if(preg_match('/^.{0,256}$/', $data['subline'])){
				$this->subline = $data['subline'];
			}
		}

		$this->teaser = $data['teaser'] ?? '';

		if(isset($data['author'])){
			if(preg_match('/^.{1,128}$/', $data['author'])){
				$this->author = $data['author'];
			}
		} else {
			return false;
		}

		$this->timestamp = time();

		// image routine (id or upload)

		$this->content = $data['content'] ?? '';

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
			'image_id' => $this->image->id,
			'content' => $this->content
		];

		$s = $pdo->prepare($query);
		$s->execute($values);
	}

	public function update($data) {
		global $pdo;

		if($data['id'] !== $this->id || $data['longid'] !== $this->longid){
			return false;
		}

		if(isset($data['overline'])){
			if(preg_match('/^.{0,64}$/', $data['overline'])){
				$this->overline = $data['overline'];
			}
		}

		if(isset($data['headline'])){
			if(preg_match('/^.{1,256}$/', $data['headline'])){
				$this->headline = $data['headline'];
			}
		}

		if(isset($data['subline'])){
			if(preg_match('/^.{0,256}$/', $data['subline'])){
				$this->subline = $data['subline'];
			}
		}

		$this->teaser = $data['teaser'] ?? '';

		if(isset($data['author'])){
			if(preg_match('/^.{1,128}$/', $data['author'])){
				$this->author = $data['author'];
			}
		}

		// image routine (id or upload)

		$this->content = $data['content'] ?? '';

		$query = 'UPDATE posts SET post_overline = :overline, post_headline = :headline, post_subline = :subline,
			post_teaser = :teaser, post_author = :author, post_image_id = :image_id, post_content = :content
			WHERE post_id = :id';

		$values = [
			'overline' => $this->overline,
			'headline' => $this->headline,
			'subline' => $this->subline,
			'teaser' => $this->teaser,
			'author' => $this->author,
			'image_id' => $this->image->id,
			'content' => $this->content,
			'id' => $this->id
		];

		$s = $pdo->prepare($query);
		$s->execute($values);
	}
}
?>
