<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Event;
use Exception;

class EventNewController {
	public $event;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct() {
		$this->event = new Event();
		$this->event->generate();
		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->event->import($_POST);
				$this->event->push();
				$this->show_success = true;
				$this->show_form = false;
				$this->show_err_invalid = false;
			} catch(Exception $e){
				$this->show_err_invalid = true;
				$this->err_invalid_msg = $e->getMessage();
			}
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/event_new.php';
	}
}
?>
