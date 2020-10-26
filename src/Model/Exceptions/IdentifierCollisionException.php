<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if a longid value       #
# provided to a DatabaseObject::import() function  #
# of a new DatabaseObject is already set for       #
# another existing DatabaseObject of the same      #
# class.                                           #
# ================================================ #

class IdentifierCollisionException extends InputException {
	public $existing;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($input, $existing) {
		$this->field = 'longid';
		$this->input = $input;
		$this->existing = $existing;

		$this->message = "Identifier Collision: longid '$input' is already assigned to object '$existing->id'.";
	}

	public function export() {
		return [
			'type' => 'IdentifierCollision',
			'field' => $this->field,
			'input' => $this->input,
			'existing_id' => $this->existing->id
		];
	}
}
?>
