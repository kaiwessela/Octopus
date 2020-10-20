<?php
namespace Blog\Frontend\Web\Controllers\Exceptions;

class InvalidParameterException extends Exception {
	public $parameters;
	public $subject;
	public $required;

	function __construct($subject, $required, $parameters) {
		$this->subject = $subject;
		$this->required = $required;
		$this->parameters = $parameters;

		parent::__construct("Invalid Parameter: $this->subject; Required: $this->required");
	}
}
?>
