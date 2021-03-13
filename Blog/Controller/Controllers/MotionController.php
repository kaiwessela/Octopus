<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Motion;
use \Blog\Model\DataObjects\Lists\MotionList;

class MotionController extends Controller {
	const MODEL = Motion::class;
	const LIST_MODEL = MotionList::class;
}
?>
