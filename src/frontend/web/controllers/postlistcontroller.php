<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;
use \Blog\Backend\Models\Post;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Frontend\Web\Modules\Picture;

class PostListController extends Controller {
	public $posts;
	public $pagination;


	public function load() {
		$page = (int) ($_GET['post'] ?? 1);
		$post_count = Post::count();

		if($post_count == 0){
			return false;
			// prepare showing error message
		}

		$this->pagination = new Pagination($post_count, $page);
		$this->pagination->load_items();

		if(!$this->pagination->current_page_exists()){
			return false;
		}

		try {
			$limit = $this->pagination->get_object_limit();
			$offset = $this->pagination->get_object_offset();
			$this->posts = Post::pull_all($limit, $offset);
		} catch(Exception $e){
			return false;
		}

		foreach($this->posts as &$post){
			if(!$post->image->is_empty()){
				$post->show_picture = true;
				$post->picture = new Picture($post->image);
			} else {
				$post->show_picture = false;
			}
		}

		return true;
	}

	public function display() {
		$server = (object) [
			'url' => Config::SERVER_URL
		];
		$content = $this->content;
		$pagination = $this->pagination;
		$timeformat = new TimeFormat();
		$posts = $this->posts;

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
