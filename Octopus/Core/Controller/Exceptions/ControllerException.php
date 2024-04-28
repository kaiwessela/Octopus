<?php
namespace Octopus\Core\Controller\Exceptions;
use \Exception;

class ControllerException extends Exception {
	private int $status_code;
	private ?Exception $exception;

	function __construct(int $code, ?string $message = '', ?Exception $exception = null) {
		parent::__construct($message);

		$this->status_code = $code;
		$this->exception = $exception;
	}


	public function get_status_code() : int {
		return $this->status_code;
	}


	public function get_original_exception() : ?Exception {
		return $this->exception;
	}
}
?>
