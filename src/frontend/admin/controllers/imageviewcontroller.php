<?php
namespace Blog\Frontend\Admin\Controllers;
use \Blog\Backend\Models\Image;
use Exception;

class ImageViewController {
	public $image;
	public $show_err_not_found;
	public $err_not_found_msg;
	public $show_image;

	function __construct() {
		try {
			$this->image = new Image();
			$this->image->pull($_GET['identifier']);
			$this->show_image = true;
			$this->show_err_not_found = false;
		} catch(Exception $e){
			$this->show_image = false;
			$this->show_err_not_found = true;
			$this->err_not_found_msg = $e->getMessage();
		}
	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/image_view.php';
	}
}
?>
