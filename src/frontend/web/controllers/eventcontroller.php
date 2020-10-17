<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controller;
use \Blog\Frontend\Web\Modules\Pagination\Pagination;
use \Blog\Frontend\Web\Modules\Timestamp;


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
