<?php // CODE ??, COMMENTS --, IMPORTS ok
namespace Blog\Core\Model\Cycle\Exceptions;
use Exception;

class OutOfCycleException extends Exception {
	function __construct(int|string $current_stadium, int|string $invalid_step) {
		parent::__construct("Cycle » Illegal Step from Stadium $current_stadium to Stadium $invalid_step.");
	}
}
?>
