A relation between the the $obj1_class with longid $obj1_id and $obj2_class with longid $obj2_id already exists,
which is not allowed for $relationClass.

<?php
namespace Blog\Model\Properties\Exceptions;

class RelationCollisionException extends PropertyValueException {
	public DataObjectRelation $relation;


	function __construct(DataObjectRelation $relation) {
		$this->relation = $relation;

		$this->message = ""
	}
}
?>
