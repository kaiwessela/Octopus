<?php
namespace Octopus\Core\Model\FlowControl\Exceptions;
use Exception;

# This exception is thrown by the (-->) Flow when an illegal step is attempted. A step is illegal if it
# has not been defined as a valid flow step when creating the flow.

class OutOfFlowException extends Exception {
	public int|string $current_stadium;
	public int|string $invalid_step;

	function __construct(int|string $current_stadium, int|string $invalid_step) {
		$this->current_stadium = $current_stadium;
		$this->invalid_step = $invalid_step;

		parent::__construct("Flow: Illegal Step from Stadium «{$current_stadium}» to Stadium «{$invalid_step}».");
	}
}
?>
