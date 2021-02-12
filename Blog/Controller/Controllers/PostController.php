<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Post;
use \Blog\Model\DataObjects\Lists\PostList;

class PostController extends Controller {
	const MODEL = Post::class;
	const LIST_MODEL = PostList::class;
}
?>
