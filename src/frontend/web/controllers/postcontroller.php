<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Backend\Post;

class PostController extends Controller {
	public $post;


	public function load() {
		$this->post = new Post();
		try {
			$this->post->pull($_GET['post']);
		} catch(EmptyResultException $e){

		} catch(DatabaseException $e){

		}


	}

	public function display() {
		$post = $this->post;

		parent::display();
	}
}
?>
