<?php
namespace Octopus\Core\Model\Database\Conditions;
use Exception;
use Octopus\Core\Model\Database\Condition;

# An And Operator condition joins multiple conditions together using the mysql AND operator.
# The statement the And Operator condition resolves to evaluates to true iff
# ALL of its individual conditions’ statements evaluate to true.

class AndOp extends Condition {
	protected array $conditions;

	function __construct(Condition ...$conditions) {
		parent::__construct();

		$this->conditions = $conditions;
	}


	public function resolve(int $index = 0) : int {
		$queries = [];

		foreach($this->conditions as $condition){
			$new_index = $condition->resolve($index);
			$queries[] = $condition->get_query();
			$values = $condition->get_values();

			if(($new_index - $index) !== count($values)){
				throw new Exception('Corrupt Indices: index difference is not equal to number of values.');
			}

			$this->values = array_merge($this->values, $values);
			$index = $new_index;
		}

		$this->query = '('.implode(') AND (', $queries).')';

		return $index;
	}
}
