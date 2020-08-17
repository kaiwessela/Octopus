<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Frontend\Web\Modules\TimeFormat;
use \Blog\Frontend\Web\Modules\Picture;
use \Blog\Backend\Models\Post;
use Parsedown;

class PostController implements Controller {
	public $post;
	public $picture;
	public $parsed;
	public $show_picture = false;


	public function __construct($route, $settings) {
		$this->post = new Post();
		$this->post->pull($_GET['post']);

		if(!$this->post->image->is_empty()){
			$this->show_picture = true;
			$this->picture = new Picture($this->post->image);
		}

		$this->parsed = Parsedown::instance()->text($this->post->content);
	}
}
?>
