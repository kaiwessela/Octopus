<?php
namespace Octopus\Core\Model\Cycle\Exceptions;
use Exception;

# What is an InvalidCycleException?
# This exception is thrown on construction of a Cycle object in the event that the array defining the cycle
# (argument $cycle) is malformed or has semantic errors that prevent the construction of a functioning Cycle.
# see Cycle class for more.

class InvalidCycleException extends Exception {
	function __construct(string $message) {
		parent::__construct($message);
	}
}
?>
