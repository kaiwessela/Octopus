<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Person;
use Exception;

class PersonDeleteController {
	public $person;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct() {
		try {
			$this->person = new Person();
			$this->person->pull($_GET['identifier']);
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}

		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->person->delete();
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
		include __DIR__ . '/../templates/person_delete.php';
	}
}
?>
