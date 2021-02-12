<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Image;
use \Blog\Model\DataObjects\Lists\ImageList;

class ImageController extends Controller {
	const MODEL = Image::class;
	const LIST_MODEL = ImageList::class;
}
?>
