<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Person;
use Exception;

class PersonViewController {
	public $person;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_person;

	function __construct() {
		try {
			$this->person = new Person();
			$this->person->pull($_GET['identifier']);
			$this->show_person = true;
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_person = false;
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/person_view.php';
	}
}
?>
