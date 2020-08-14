<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Post;
use Exception;

class PostViewController {
	public $post;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_post;

	function __construct() {
		try {
			$this->post = new Post();
			$this->post->pull($_GET['identifier']);
			$this->show_post = true;
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_post = false;
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/post_view.php';
	}
}
?>
