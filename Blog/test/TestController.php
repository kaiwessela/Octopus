<?php
namespace Test;
use \Octopus\Core\Controller\Controllers\Controller;
use \Octopus\Core\Controller\Request;
use \Octopus\Core\Controller\Router\ControllerCall;

class TestController extends Controller {
	private $value;


	public function load(Request $request, ControllerCall $call) : void {
		$this->value = new \Blog\Modules\Posts\Post(null, $this->endpoint->get_db());
	}


	public function execute(Request $request) : void {
		$this->value->pull(identifier:'c371aabe', identify_by:'id', attributes:[
			'id' => true
		]);

		$this->status_code = 200;
	}


	public function finish() : void {
		// var_dump($this->value->id);
		var_dump($this->value->arrayify());
	}
}
?>
