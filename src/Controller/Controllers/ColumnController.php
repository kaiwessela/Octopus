<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\MarkdownContent;
use \Blog\Controller\Processors\Picture;
use \Blog\Controller\Processors\Timestamp;

class ColumnController extends Controller {
	const MODEL = 'Column';
	const LIST_MODEL = 'ColumnList';

	const PAGINATABLE = true;


	protected function export_each($column) {
		$export = $column->export();

		foreach($column->posts as $i => $post){
			$export->posts[$i]->timestamp = new Timestamp($post->timestamp);
			$export->posts[$i]->content = new MarkdownContent($post->content);

			if(!$post->image->is_empty()){
				$export->posts[$i]->image = new Picture($post->image);
			}
		}

		return $export;
	}
}
?>
