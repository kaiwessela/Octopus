<?php
namespace Octopus\Core\Controller\Exceptions;
use \Exception;

class ControllerException extends Exception {
	private int $status_code;

	function __construct(int $code, ?string $message = null, ?Exception $exception = null) {
		parent::__construct($message);

		$this->status_code = $code;
	}
}
?>
