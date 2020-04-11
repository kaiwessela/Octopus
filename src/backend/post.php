<?php
class Post {
	public $id; 	# String(8)[base16]
	public $longid; # String(9-128)[a-z0-9-] // IDEA why not 1-128?
	public $title; 	# String(1-256)

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
		$obj = new Page();
		
		$obj->id = $data['page_id'];
		$obj->longid = $data['page_longid'];
		$obj->title = $data['page_title'];

		return $obj;
	}
}
?>
