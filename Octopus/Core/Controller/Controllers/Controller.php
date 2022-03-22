<?php
namespace Octopus\Core\Controller\Controllers;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Router\ControllerCall;

abstract class Controller {
	protected string $importance; # main | essential | auxiliary
	protected int $status;


	function __construct() {
		$this->status = 0;
	}


	abstract public function load(Request &$request, ControllerCall $call) : void;


	abstract public function execute() : void;


	abstract public function finish() : void;


	final public function get_status() : int {
		return $this->status;
	}
}
?>
