<?php
namespace Octopus\Modules\Posts\Columns;
use \Octopus\Core\Model\EntityList;
use \Octopus\Modules\Posts\Post;

class PostList extends EntityList {
	const ENTITY_CLASS = Post::class;
}
?>
