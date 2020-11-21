<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Picture;

class ImageController extends Controller {
	const MODEL = 'Image';
	const LIST_MODEL = 'ImageList';


	protected function export_each($image) {
		$export = new Picture($image);
	}
}
?>
