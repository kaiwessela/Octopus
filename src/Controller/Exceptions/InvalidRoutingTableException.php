<?php
namespace Blog\Controller\Exceptions;
use Exception;

class InvalidRoutingTableException extends Exception {

	function __construct($message) {
		parent::__construct('Invalid Routing Table: ' . $message);
	}
}
?>
