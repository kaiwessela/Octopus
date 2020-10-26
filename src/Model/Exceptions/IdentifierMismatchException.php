<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if the longid or id     #
# value provided to a DatabaseObject::import()     #
# function does not equal the longid or id value   #
# of the existing DatabaseObject.                  #
# ================================================ #

class IdentifierMismatchException extends InputException {
	public $object;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($field, $input, $object) {
		$this->field = $field;
		$this->input = $input;
		$this->object = $object;

		$this->message = "Identifier Mismatch: $field '$input' does not match with existing Object id '$object->id' (longid: '$object->longid').";
	}

	public function export() {
		return [
			'type' => 'IdentifierMismatch',
			'field' => $this->field,
			'input' => $this->input,
			'existing_id' => $this->object->id,
			'existing_longid' => $this->object->longid
		];
	}
}
?>
