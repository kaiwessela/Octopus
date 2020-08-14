<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Frontend\Web\Modules\Picture;
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

		if(!$this->post->image->is_empty()){
			$this->show_picture = true;
			$this->post->picture = new Picture($this->post->image);
		} else {
			$this->show_picture = false;
		}

		$parsedown = new Parsedown();
		$this->content = $parsedown->text($this->post->content);

		return true;
	}

	public function display() {
		$post = $this->post;
		$controller = $this;

		include 'frontend/web/templates/' . $this->route['template'] . '.tmp.php';
	}
}
?>
