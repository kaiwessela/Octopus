<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Controller\Processors\Timestamp;

class EventController extends Controller {
	const MODEL = 'Event';
	const LIST_MODEL = 'EventList';


	protected function export_each($event) {
		$export = $event->export();
		// $export->timestamp = new Timestamp($event->timestamp);

		return $export;
	}
}
?>
