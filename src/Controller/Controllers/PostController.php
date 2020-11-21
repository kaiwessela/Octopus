<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\MarkdownContent;
use \Blog\Controller\Processors\Picture;
use \Blog\Controller\Processors\Timestamp;

class PostController extends Controller {
	const MODEL = 'Post';
	const LIST_MODEL = 'PostList';


	protected function export_each($post) {
		$export = $post->export();
		$export->timestamp = new Timestamp($post->timestamp);
		$export->content = new MarkdownContent($post->content);

		if(!$post->image->is_empty()){
			$export->image = new Picture($post->image);
		}

		return $export;
	}
}
?>
