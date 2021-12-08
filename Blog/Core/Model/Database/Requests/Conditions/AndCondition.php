<?php


class AndCondition {
	protected array $conditions;

	function __construct(Condition ...$conditions) {
		$this->conditions = $conditions;
	}


	public function resolve(int $index) : array {
		$queries = [];
		$values = [];

		$index++;

		foreach($this->conditions as $condition){
			$result = $condition->resolve($index);
			$queries[] = $result['query'];
			$values = array_merge($values, $result['values']);

			if($condition instanceof AndCondition || $condition instanceof OrCondition){
				$index = $result['index']+1;
			} else {
				$index++;
			}
		}


		return [
			'query' => '('.implode($queries, ') AND (').')',
			'values' => $values,
			'index' => $index
		];
	}
}
