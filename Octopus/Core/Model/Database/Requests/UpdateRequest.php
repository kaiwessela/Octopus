<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Octopus\Core\Model\Database\Request;
use Octopus\Core\Model\Database\Requests\Conditions\IdentifierEquals;

# UpdateRequest creates an SQL query for an UPDATE operation, used to update a row (= an object) in the database.
# There can only be updated exactly one row/object per request. An IdentifierEquals condition has to be provided that
# unequivocally determines which row/object to update (by specifying the value of a unique column).
# All columns reflecting attribues provided to the request will be updated to the attribute's value. If no attributes
# are provided, an EmptyRequestException will be thrown.
# See also  --> Conditions\IdentifierEquals.
#
# This class is a child of Request. See there for further documentation.

final class UpdateRequest extends Request {
	protected IdentifierEquals $condition; # The condition determining which row/object to update.


	# Set the condition determining which row/object to update.
	final public function where(IdentifierEquals $condition) : void {
		$this->require_unresolved();
		
		$this->condition = $condition;
	}


	# Compute an SQL query from the object, attribues and condition.
	final protected function resolve() : void {
		# If no attributes were provided to update, throw an EmptyRequestException.
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		# Check that a condition determining which row/object to update has been provided.
		if(!isset($this->condition)){
			throw new Exception('An IdentifierEquals condition must be provided for the update request.');
		}

		# example:
		# UPDATE `objects` SET
		# 	`field1` = :field1,
		# 	`field2` = :field2
		# WHERE `objects`.`id` = :cond_0;
		# values = ['field1' => 'abc', 'field2' => 123, 'cond_0' =>];

		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	`{$attribute->get_db_column()}` = :{$attribute->get_name()}";
			$this->values[$attribute->get_name()] = $attribute->get_push_value();
		}

		$this->query = "UPDATE `{$this->object->get_db_table()}` SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns).PHP_EOL;
		$this->query .= "WHERE {$this->condition->get_query()}";

		$this->values = [...$this->values, ...$this->condition->get_values()];
	}
}
