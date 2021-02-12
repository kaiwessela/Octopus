<?php
namespace Blog\Controller\Exceptions;
use Exception;

class InvalidRouteAttributeException extends Exception {

	function __construct($name, $value, $explaination = '') {
		parent::__construct("Invalid Route Attribute '$name': $value ($explaination)");
	}
}
?>
