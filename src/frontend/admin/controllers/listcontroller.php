<?php
namespace Blog\Frontend\Admin\Controllers;
use Exception;

class ListController {
	public $template;
	public $objs;
	public $show_warn_no_fount;
	public $show_list;

	function __construct($template, $model) {
		$this->template = $template;

		try {
			$model = "\Blog\Backend\Models\\$model";
			$this->objs = $model::pull_all();
			$this->show_warn_no_found = false;
			$this->show_list = true;
		} catch(Exception $e){
			$this->show_warn_no_found = true;
			$this->show_list = false;
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . "/../templates/$this->template.php";
	}
}
?>
