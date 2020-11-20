<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Pagination\Pagination;
use \Blog\Controller\Processors\Timestamp;


class EventController extends Controller {
	const MODEL = 'Event';

	public $pagination;

	/* @inherited
	protected $request;
	public $status;
	public $objects;
	public $exceptions;

	protected $count;
	*/


	public function process_each(&$object, &$obj) {
		$obj->timestamp = new Timestamp($object->timestamp);
	}
}
?>
