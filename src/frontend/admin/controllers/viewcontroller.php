<?php
namespace Blog\Frontend\Admin\Controllers;
use Exception;

class ViewController {
	public $template;
	public $obj;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_obj;

	function __construct($template, $model) {
		$this->template = $template;

		try {
			$model = "\Blog\Backend\Models\\$model";
			$this->obj = new $model();
			$this->obj->pull($_GET['identifier']);
			$this->show_obj = true;
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_obj = false;
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . "/../templates/$this->template.php";
	}
}
?>
