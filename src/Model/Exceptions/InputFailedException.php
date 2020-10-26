<?php
namespace Blog\Model\Exceptions;
use Exception;

class InputFailedException extends Exception { // TODO rename to ImportFailedException
	public $exceptions;


	function __construct() {
		$this->exceptions = [];
	}

	public function push(InputException $exception) {
		$this->exceptions[$exception->field] = $exception;
	}

	public function merge(ImportFailedException $exception, $prefix) {
		foreach($exception->exceptions as $e){
			$this->exceptions[$prefix . '_' . $e->field] = $e;
		}
	}

	public function msg() { // TEMP
		$msg;
		foreach($this->exceptions as $exception){
			$msg .= $exception->getMessage() . \PHP_EOL;
		}
		return $msg;
	}

	public function is_empty() {
		return count($this->exceptions) == 0;
	}
}
?>
