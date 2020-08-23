<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Event;
use Exception;

class EventListController {
	public $show_warn_no_fount;
	public $show_list;
	public $events;

	function __construct() {
		try {
			$this->events = Event::pull_all();
			$this->show_warn_no_found = false;
			$this->show_list = true;
		} catch(Exception $e){
			$this->show_warn_no_found = true;
			$this->show_list = false;
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/event_list.php';
	}
}
?>
