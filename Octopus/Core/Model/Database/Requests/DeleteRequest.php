<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Database\Requests\Request;
use Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;

# DeleteRequest creates an SQL query for a DELETE operation, used to delete a row (= an object) from the database.
# There can only be deleted exactly one row/object per request. An IdentifierEquals condition has to be provided that
# unequivocally determines which row/object to delete (by specifying the value of a unique column).
# The DeleteRequest does not need or use any attributes as it is applied to an entire row/object.
# See also  --> Conditions\IdentifierEquals.
#
# This class is a child of Request. See there for further documentation.

final class DeleteRequest extends Request {
	protected IdentifierEquals $condition; # The condition determining which row/object to delete.


	# Set the condition determining which row/object to delete.
	final public function set_condition(IdentifierEquals $condition) : void {
		$this->require_unresolved();
		
		$this->condition = $condition;
	}


	# Compute an SQL query from the object and condition.
	final protected function resolve() : void {
		# Check that a condition determining which row/object to delete has been provided.
		if(!isset($this->condition)){
			throw new Exception('An IdentifierEquals condition must be provided for the delete request.');
		}

		# example:
		# DELETE FROM `objects` WHERE `objects`.`id` = :cond_0;
		# values = ['cond_0' => 'abcdef01'];

		$this->query = "DELETE FROM `{$this->object->get_db_table()}` WHERE {$this->condition->get_query()}";
		$this->values = $this->condition->get_values();
	}
}
?>
