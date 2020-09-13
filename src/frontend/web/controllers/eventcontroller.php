<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Backend\Models\Event;
use \Blog\Frontend\Web\Modules\Pagination;


class EventController extends Controller {
	const MODEL = 'Event';

	public $errors = [
		'404' => false
	];

	/* @inherited
	const MODEL;

	private $params;
	private $models;

	public $objects;
	public $errors;
	*/

	public $pagination;
}
?>
