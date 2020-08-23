<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Event;
use Exception;

class EventViewController {
	public $event;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_event;

	function __construct() {
		try {
			$this->event = new Event();
			$this->event->pull($_GET['identifier']);
			$this->show_event = true;
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_event = false;
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/event_view.php';
	}
}
?>
