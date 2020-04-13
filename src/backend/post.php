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
	public $image_id;	# String(8)[Image:id]
	public $content;	# String


	public static function pull_by_id($id) {
		global $pdo;

		$query = 'SELECT * FROM posts WHERE post_id = :id';
		$values = ['id' => $id];

		$s = $pdo->prepare($query);
		$s->execute($values);
			return self::load($s->fetch());
		if($s->rowCount() == 1){

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

	public static function load($data) {
		$obj = new self();

		$obj->id = $data['post_id'];
		$obj->longid = $data['post_longid'];
		$obj->title = $data['post_title'];

		return $obj;
	}
}
?>
