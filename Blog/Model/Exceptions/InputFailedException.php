<?php
namespace Blog\Model\Exceptions;
use Exception;
use \Blog\Model\Exportable;

class InputFailedException extends Exception implements Exportable { // TODO rename to ImportFailedException
	public $exceptions;
	public $export_name;


	function __construct() {
		$this->exceptions = [];
		$this->export_name = 'import';
	}

	public function push(InputException $exception) {
		$this->exceptions[$exception->field] = $exception;
	}

	public function merge(InputFailedException $exception, $prefix = '') {
		foreach($exception->exceptions as $e){
			$this->exceptions[$prefix . ':' . $e->field] = $e;
		}
	}

	public function export() {
		$exlist = [];
		foreach($this->exceptions as $exception){
			$exlist[$exception->field] = $exception->export();
		}
		return $exlist;
	}

	public function is_empty() {
		return count($this->exceptions) == 0;
	}
}
?>
