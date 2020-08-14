<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Image;
use Exception;

class ImageNewController {
	public $image;
	public $show_err_invalid;
	public $err_invalid_msg;
	public $show_form;
	public $show_success;

	function __construct() {
		$this->image = new Image();
		$this->image->generate();
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
		include __DIR__ . '/../templates/image_new.php';
	}
}
?>
