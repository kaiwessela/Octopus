<?php
namespace Blog\Frontend\Admin\Controllers;

class NotFoundController {


	function __construct() {

	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/notfound.php';
	}
}
?>
