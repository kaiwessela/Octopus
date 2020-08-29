<?php
namespace Blog\Frontend\Admin\Controllers;
use Exception;

class DeleteController {
	public $template;
	public $obj;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct($template, $model) {
		$this->template = $template;

		try {
			$model = "\Blog\Backend\Models\\$model";
			$this->obj = new $model();
			$this->obj->pull($_GET['identifier']);
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}

		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->obj->delete();
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
		include __DIR__ . "/../templates/$this->template.php";
	}
}
?>
