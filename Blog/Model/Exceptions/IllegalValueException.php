<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if an input value       #
# provided to a DatabaseObject::import() function  #
# does not match the format requirements.          #
# ================================================ #

class IllegalValueException extends InputException {
	public $expected;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($field, $input, $expected = null) {
		$this->field = $field;
		$this->input = $input;
		$this->expected = $expected;

		$this->message = "Input value '$field' invalid. Given '$input', Expected format: '$expected'.";
	}

	public function export() {
		return [
			'type' => 'IllegalValue',
			'field' => $this->field,
			'input' => $this->input,
			'expected' => $this->expected
		];
	}
}
?>
