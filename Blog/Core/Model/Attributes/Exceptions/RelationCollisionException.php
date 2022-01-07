<?php // CODE --, COMMENTS --, IMPORTS ok
namespace Blog\Core\Model\Properties\Exceptions;
use \Blog\Core\Model\DataObjectRelation;
use \Blog\Core\Model\Properties\Exceptions\PropertyValueException;


class RelationCollisionException extends PropertyValueException {
	public DataObjectRelation $relation;


	function __construct(DataObjectRelation $relation) {
		$this->relation = $relation;

		// A relation between the the $obj1_class with longid $obj1_id and $obj2_class with longid $obj2_id already exists,
		// which is not allowed for $relationClass.
		$this->message = ""
	}
}
?>
