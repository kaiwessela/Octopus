<?php
namespace Blog\Controller;
use \Astronauth\Main as Astronauth;
use \Blog\Controller\Request;
use \Blog\Controller\Call;

abstract class Controller {
	public Request $request;
	public Astronauth $astronauth;

	public int $status;
	public Call $call;


	final function __construct(Request &$request, Astronauth &$astronauth) {
		$this->request = &$request;
		$this->astronauth = &$astronauth;
	}


	public function prepare(Call $call) : void {
		$this->call = $call;
	}

	public abstract function execute() : void;
	public abstract function process() : void;


	public function status(int|string $status) : bool {
		if(is_string($status)){
			$status = array_flip($this::STATUS)[$status] ?? 0;
		}

		return ($this->status == $status);
	}

	const STATUS = [
		20 => 'found',
		21 => 'created',
		22 => 'edited',
		23 => 'deleted',
		24 => 'empty',
		40 => 'bad request',
		41 => 'unprocessable',
		42 => 'forbidden',
		44 => 'not found',
	];
}
?>
