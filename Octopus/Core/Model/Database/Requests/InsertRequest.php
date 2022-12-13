<?php
namespace Octopus\Core\Model\Database\Requests;
use Octopus\Core\Model\Database\Requests\Request;
use Octopus\Core\Model\Database\Exceptions\EmptyRequestException;

# InsertRequest creates an SQL query for an INSERT operation to insert a new row (= a new object) into the database.
# There can only be inserted exactly one row/object per request.
# All columns reflecting attributes provided to the request will be set to the attribute's value. If no attributes are
# provided, an EmptyRequestException will be thrown.
# No conditions can be provided.
#
# This class is a child of Request. See there for further documentation.

final class InsertRequest extends Request {

	# Compute an SQL query from the object and attributes.
	final protected function resolve() : void {
		# If no attributes were provided to insert, throw an EmptyRequestException.
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		# example:
		# INSERT INTO `objects` SET
		# 	`id` = :id,
		# 	`field1` = :field1,
		# 	`field2` = :field2;
		# values = ['id' => 'abcdef01', 'field1' => 'xyz', 'field2' => 234];


		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	`{$attribute->get_db_column()}` = :{$attribute->get_name()}";
			$this->values[$attribute->get_name()] = $attribute->get_push_value();
		}

		$this->query = "INSERT INTO `{$this->object->get_db_table()}` SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns);
	}
}
?>
