<?php
namespace Blog\Controller\Exceptions;
use Exception;

class InvalidNotationException extends Exception {

	function __construct($notation) {
		parent::__construct("Invalid Notation: $notation");
	}
}
?>
