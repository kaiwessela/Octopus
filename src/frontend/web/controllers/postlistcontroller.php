<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;
use \Blog\Backend\Models\Post;

class PostListController extends Controller {
	public $posts;
	public $pagination;


	public function load() {
		$this->post = new Post();
		try {
			$this->post->pull($_GET['post']);
		} catch(EmptyResultException $e){
			return false;
		} catch(DatabaseException $e){
			return false;
		}

		return true;
	}

	public function display() {
		$server = (object) [
			'url' => Config::SERVER_URL
		];
		$content = $this->content;

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
