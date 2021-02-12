<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exportable;
use Exception;

/*#=========== InputException ===========

MissingValueException			- if input value is missing
IllegalValueException			- if input value is invalid
IdentifierCollisionException	- if the longid input of a new object is already in use
IdentifierMismatchException		- if the id or longid input of a edit request object does not match the original one
RelationNonexistentException	- if i.e. a new post object references an image object that does not exist

*/#======================================

class InputException extends Exception implements Exportable {
	public $field;
	public $input;

	function __construct($field, $message) {
		$this->field = $field;
		$this->message = "Unexpected input on field '$field': $message";
	}

	public function export() {
		return [
			'type' => 'Other',
			'field' => $this->field
		];
	}
}
?>
