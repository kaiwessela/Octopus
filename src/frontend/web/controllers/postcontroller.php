<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Backend\Models\Post;
use Parsedown;

class PostController extends Controller {
	public $post;


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
		$post = $this->post;
		$server = (object) [
			'url' => Config::SERVER_URL
		];
		$content = $this->content;
		$timeformat = new TimeFormat();
		$parsedown = new Parsedown();

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
