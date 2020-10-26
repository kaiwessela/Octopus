<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if no DatabaseObject    #
# can be identified by an input value provided to  #
# a DatabaseObject::import() function required to  #
# refer to another DatabaseObject (i.e. Image in a #
# Post).                                           #
# ================================================ #

class RelationNonexistentException extends InputException {
	public $class;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($field, $input, $class) {
		$this->field = $field;
		$this->input = $input;
		$this->class = $class;

		$this->message = "Relation Nonexistent: Object '$input' of class '$class' specified in '$field' does not exist.";
	}
}
?>
