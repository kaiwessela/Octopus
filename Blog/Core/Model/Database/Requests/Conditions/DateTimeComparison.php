<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use DateTime;
use Exception;

class DateTimeComparison extends Condition {
	protected PropertyDefinition $property;
	protected string $operator;
	protected DateTime|string $value;


	function __construct(PropertyDefinition $property, string $operator, DateTime|string $value = 'now') {
		parent::__construct();

		if(!in_array($operator, ['=', '<', '>', '<=', '>='])){
			throw new Exception("Invalid operator: «{$operator}».");
		}

		$this->property = $property;
		$this->operator = $operator;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->property->get_db_table()}.{$this->property->get_db_column()} {$this->operator} ";

		if($value === 'now'){
			$this->query .= 'NOW()';
			$this->values = [];
			return $index;
		} else {
			$this->query .= ":cond_{$index}";
			$this->values = ["cond_{$index}" => $this->value->format('Y-m-d H:i:s')];
			return $index + 1;
		}
	}
}
?>
