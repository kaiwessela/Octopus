<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;
use \Blog\Backend\Models\Post;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Frontend\Web\Modules\Picture;

class PostListController implements Controller {
	public $posts;
	public $pagination;
	public $show_no_posts_found = false;


	function __construct($route, $settings) {
		$page = (int) ($_GET['post'] ?? 1);
		$post_count = Post::count();

		if($post_count == 0){
			$this->show_no_posts_found = true;
			return;
		}

		$this->pagination = new Pagination($post_count, $page);
		$this->pagination->load_items();

		if(!$this->pagination->current_page_exists()){
			throw new Exception('pagination: page does not exist');
		}

		$limit = $this->pagination->get_object_limit();
		$offset = $this->pagination->get_object_offset();
		$this->posts = Post::pull_all($limit, $offset);

		foreach($this->posts as &$post){
			if(!$post->image->is_empty()){
				$post->show_picture = true;
				$post->picture = new Picture($post->image);
			} else {
				$post->show_picture = false;
			}
		}
	}
}
?>
