<?php
namespace Blog\Frontend\Web\Controllers;

class ControllerAction {
	private $name;
	private $state;
	// MAYBE public $errors;

	const STATE_READY = 1;
	const STATE_COMPLETED = 2;
	const STATE_FAILED = 3;


	function __construct($name) {
		$this->name = $name;
		$this->state = self::STATE_READY;
	}

	function __toString() {
		return $this->name;
	}

	public function set_state($state) {
		if($state == self::STATE_READY || $state == self::STATE_COMPLETED || $state == self::STATE_FAILED){
			$this->state = (int) $state;
		} else if($state == 'ready'){
			$this->state = self::STATE_READY;
		} else if($state == 'completed'){
			$this->state = self::STATE_COMPLETED;
		} else if($state == 'failed'){
			$this->state = self::STATE_FAILED;
		} else {
			// InvalidArgumentException
		}
	}

	public function ready() {
		return ($this->state == self::STATE_READY);
	}

	public function completed() {
		return ($this->state == self::STATE_COMPLETED);
	}

	public function failed() {
		return ($this->state == self::STATE_FAILED);
	}
}
?>
