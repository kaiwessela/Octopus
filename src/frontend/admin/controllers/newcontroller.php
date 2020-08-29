<?php
namespace Blog\Frontend\Admin\Controllers;
use Exception;

class NewController {
	public $template;
	public $obj;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct($template, $model) {
		$this->template = $template;

		$model = "\Blog\Backend\Models\\$model";
		$this->obj = new $model();
		$this->obj->generate();
		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->obj->import($_POST);
				$this->obj->push();
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
