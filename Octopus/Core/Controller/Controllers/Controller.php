<?php
namespace Octopus\Core\Controller\Controllers;
use \Octopus\Core\Controller\Request;
use Octopus\Core\Controller\Response;
use \Octopus\Core\Controller\Endpoint;
use \Octopus\Core\Controller\Router\ControllerCall;

abstract class Controller {
	protected Endpoint $endpoint;
	protected Request $request;
	protected Response $response;
	protected string $importance; # primary | essential | accessory
	protected ?int $status_code;


	function __construct(string $importance) {
		$this->importance = $importance;
		$this->status_code = null;
	}


	final public function load_environment(Endpoint &$endpoint, Request &$request, Response &$response) : void {
		$this->endpoint = $endpoint;
		$this->request = $request;
		$this->response = $response;
	}


	abstract public function load(Request $request, ControllerCall $call) : void;


	abstract public function execute(Request $request) : void;


	abstract public function finish() : void;


	final public function get_importance() : string {
		return $this->importance;
	}


	final public function get_status_code() : ?int {
		return $this->status_code;
	}
}
?>
