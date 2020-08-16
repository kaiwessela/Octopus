<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Person;
use Exception;

class PersonNewController {
	public $person;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct() {
		$this->person = new Person();
		$this->person->generate();
		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->person->import($_POST);
				$this->person->push();
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
		include __DIR__ . '/../templates/person_new.php';
	}
}
?>
