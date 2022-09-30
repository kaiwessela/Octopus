<?php
namespace Octopus\Core\Model\Events;
use \Octopus\Core\Model\Events\Prevention;
use \Exception;

abstract class Event {
	protected array $listeners;
	protected bool $is_firing;


	final function __construct() {
		$this->listeners = [];
		$this->is_firing = false;
	}


	final public function add_listener(callable $handler) : void {
		$this->listeners[] = $handler;
	}


	// abstract public function fire() : void;


	final protected function call_listeners() : void {
		if($this->is_firing){
			throw new Exception('already firing.');
		}

		$this->is_firing = true;

		foreach($this->listeners as $listener){
			$listener($this);
		}

		$this->is_firing = false;
	}


	final public function prevent(?Exception $exception = null) : void {
		if(!$this->is_firing){
			throw new Exception('not firing.');
		}

		throw new Prevention($exception);
	}
}
?>
