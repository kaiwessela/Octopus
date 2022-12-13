<?php
namespace Octopus\Modules\Astronauth;
use Exception;

class AstronauthException extends Exception {
	private string $error_code;


	function __construct(string $code, string $message = '') {
		parent::__construct($message);

		$this->error_code = $code;
	}


	public function get_message() : string {
		return $this->getMessage();
	}


	public function get_code() : string {
		return $this->error_code;
	}
}
?>