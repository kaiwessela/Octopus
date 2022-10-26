<?php
namespace Octopus\Core\Controller\Controllers;
use Octopus\Core\Controller\Request;
use Octopus\Core\Controller\Endpoint;
use Octopus\Core\Controller\Router\ControllerCall;

abstract class Controller {
	protected Endpoint $endpoint;
	protected Request $request;
	protected ControllerCall $call;
	private string $importance; # primary | essential | accessory
	private int $status_code;
	private array $cookies;
	private ?string $redirect;


	function __construct(string $importance) {
		$this->importance = $importance;
		$this->status_code = 0;
		$this->cookies = [];
		$this->redirect = null;
	}


	final public function load_environment(Endpoint &$endpoint, Request &$request, ControllerCall &$call) : void {
		$this->endpoint = $endpoint;
		$this->request = $request;
		$this->call = $call;
	}


	abstract public function load() : void;


	abstract public function execute() : void;


	public function finish() : void {
		return;
	}


	final protected function set_cookie(string $name, string $value, int $duration = 0, string $path = '', string $domain = '') : void {
		$this->cookies[$name] = [
			'name' => $name,
			'value' => $value,
			'duration' => $duration,
			'path' => $path,
			'domain' => $domain
		];
	}


	final protected function delete_cookie(string $name) : void {
		$this->set_cookie($name, '', -1);
	}


	final public function get_cookies() : array {
		return $this->cookies;
	}


	final protected function redirect(?string $location = null, ?int $status_code = null) : void {
		if(!is_null($status_code)){
			$this->set_status_code($status_code);
		}

		$this->redirect = $location;
	}


	final public function get_importance() : string {
		return $this->importance;
	}


	final protected function set_status_code(int $code) : void {
		$this->status_code = $code;
	}


	final public function get_status_code() : int {
		return $this->status_code;
	}


	final public function status_code_is(mixed $code) : bool {
		return $this->status_code == $code;
	}
}
?>
