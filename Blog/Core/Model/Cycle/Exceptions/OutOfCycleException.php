<?php
namespace Octopus\Core\Model\Cycle\Exceptions;
use Exception;

# This exception is thrown by the (-->) Cycle when an illegal step is attempted. A step is illegal if it
# has not been defined as a valid cycle step when creating the cycle.

class OutOfCycleException extends Exception {
	public int|string $current_stadium;
	public int|string $invalid_step;

	function __construct(int|string $current_stadium, int|string $invalid_step) {
		$this->current_stadium = $current_stadium;
		$this->invalid_step = $invalid_step;

		parent::__construct("Cycle: Illegal Step from Stadium «{$current_stadium}» to Stadium «{$invalid_step}».");
	}
}
?>
