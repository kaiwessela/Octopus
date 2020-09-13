<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;


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
