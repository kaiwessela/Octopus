<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if an input value       #
# provided to a DatabaseObject::import() function  #
# is required but not set, null or empty.          #
# ================================================ #

class MissingValueException extends InputException {
	public $expected;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($field, $expected = null) {
		$this->field = $field;
		$this->input = null;
		$this->expected = $expected;

		$this->message = "Required input value '$field' missing. Expected format '$expected'.";
	}

	public function export() {
		return [
			'type' => 'MissingValue',
			'field' => $this->field,
			'input' => $this->input,
			'expected' => $this->expected
		];
	}
}
?>
