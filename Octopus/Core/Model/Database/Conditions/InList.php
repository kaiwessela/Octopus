<?php
namespace Octopus\Core\Model\Database\Conditions;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Database\Condition;

# An InList condition checks if any attribute (column) value is contained in a given array of comparison values
# using the SQL IN operator.
# The comparison values array must be a non-empty list of scalar values.

class InList extends Condition {
	protected Attribute $attribute;
	protected array $comparison_list;


	function __construct(Attribute $column, array $values) {
		parent::__construct();

		if(empty($values)){
			throw new Exception("The array of comparison values must not be empty.");
		}

		if(!array_is_list($values)){
			throw new Exception("The array of comparison values is not a list.");
		}

		foreach($values as $value){
			if(!is_scalar($value)){
				throw new Exception("The array of comparison values contains non-scalar values.");
			}
		}

		$this->attribute = $column;
		$this->comparison_list = $values;
	}


	protected function simplified_resolve() : string {
		foreach($this->comparison_list as $value){
			$placeholders[] = $this->substitute($value);
		}

		$in = implode(', ', $placeholders);

		return "{$this->attribute->get_prefixed_db_column()} IN ({$in})";
	}
}
?>
