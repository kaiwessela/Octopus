<?php
namespace Blog\Controller\Exceptions;
use Exception;

class NoRouteFoundException extends Exception {

	function __construct() {
		parent::__construct('No Route found.');
	}
}
?>
