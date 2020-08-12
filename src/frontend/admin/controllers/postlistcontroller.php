<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Post;
use Exception;

class PostListController {
	public $show_warn_no_fount;
	public $show_list;
	public $posts;

	function __construct() {
		try {
			$this->posts = Post::pull_all();
			$this->show_warn_no_found = false;
			$this->show_list = true;
		} catch(Exception $e){
			$this->show_warn_no_found = true;
			$this->show_list = false;
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/post_list.php';
	}
}
?>
