<?php
namespace Blog\Frontend\Admin\Controllers;

class StartController {


	function __construct() {

	}

	public function display() {
		$controller = $this;
		include __DIR__ . '/../templates/start.php';
	}
}
?>
