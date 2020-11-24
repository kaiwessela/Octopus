<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Post;

class PostList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $posts}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'posts';


	protected static function load_each($data) {
		$obj = new Post();
		$obj->load_single($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM posts
LEFT JOIN images ON image_id = post_image_id
ORDER BY post_timestamp DESC
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM posts
SQL; #---|

}
?>
