<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\Post;
use \Octopus\Core\Model\EntityList;

class PostList extends EntityList {
	const ENTITY_CLASS = Post::class;
}
?>
