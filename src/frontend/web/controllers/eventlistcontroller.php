<?php
namespace Blog\Frontend\Web\Controllers;
use \Blog\Frontend\Web\Controllers\Controller;
use \Blog\Config\Config;
use \Blog\Backend\Models\Event;

class EventListController implements Controller {
	public $events;
	public $show_no_events_found = false;


	function __construct($route, $settings) {
		$this->show_no_events_found = false;

		try {
			$this->events = Event::pull_future();
		} catch(EmptyResultException $e){
			$this->show_no_events_found = true;
		}
	}
}
?>
