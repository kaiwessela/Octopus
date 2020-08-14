<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Image;
use Exception;

class ImageEditController {
	public $image;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct() {
		try {
			$this->image = new Image();
			$this->image->pull($_GET['identifier']);
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}

		$this->show_success = false;
		$this->show_form = true;

		if($_POST){
			try {
				$this->image->import($_POST);
				$this->image->push();
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
		include __DIR__ . '/../templates/image_edit.php';
	}
}
?>
