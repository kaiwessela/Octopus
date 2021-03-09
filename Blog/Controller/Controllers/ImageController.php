<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Media\Image;
use \Blog\Model\DataObjects\Lists\Media\ImageList;

class ImageController extends Controller {
	const MODEL = Image::class;
	const LIST_MODEL = ImageList::class;
}
?>
