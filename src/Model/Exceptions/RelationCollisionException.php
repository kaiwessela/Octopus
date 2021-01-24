<?php
namespace Blog\Model\Exceptions;
use \Blog\Model\Exceptions\InputException;

# ================== USE CASE ==================== #
# This Exception is thrown if a Relation is        #
# classified as UNIQUE but there is an attempt to  #
# create a new relation with both objects the same #
# as an existing relation.                         #
# ================================================ #

class RelationCollisionException extends InputException {
	public $existing_id;

	/* @inherited
	public $field;
	public $input;
	*/


	function __construct($field, $input, $existing_id) {
		$this->field = $field;
		$this->input = $input;
		$this->existing_id = $existing_id;

		$this->message = "Relation Collision: A relation like the one specified in $field ($input) already exists (id: $existing_id).";
	}

	public function export() {
		return [
			'type' => 'RelationCollision',
			'field' => $this->field,
			'input' => $this->input,
			'existing_id' => $this->existing_id
		];
	}
}
?>
