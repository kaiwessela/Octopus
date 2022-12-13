<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Exception;

# An OrOp condition joins multiple conditions together using the mysql OR operator.
# The statement the OrOp condition resolves to evaluates to true iff
# AT LEAST ONE of its individual conditions’ statements evaluates to true.

class OrOp extends Condition {
	protected array $conditions;

	function __construct(Condition ...$conditions) {
		parent::__construct();

		$this->conditions = $conditions;
	}


	protected function resolve(int $index = 0) : int {
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

		$this->query = '('.implode(') OR (', $queries).')';

		return $index;
	}
}
