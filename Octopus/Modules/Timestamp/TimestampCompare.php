<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Modules\Timestamp\Timestamp;

// TODO explainations

class TimestampCompare extends Condition {
	protected Attribute $attribute;
	protected string $operator;
	protected Timestamp $value;
	protected ?Timestamp $value2;


	function __construct(Attribute $attribute, string $operator, Timestamp $value) {
		parent::__construct();

		if(!in_array($operator, ['=', '<', '>', '<=', '>='])){
			throw new Exception("Invalid operator: «{$operator}».");
		}

		$this->attribute = $attribute;
		$this->operator = $operator;
		$this->value = $value;
		$this->value2 = null;

		if($this->value->get_granularity() === 1){
			// do nothing
		} else if($operator === '<' || $operator === '>='){
			$this->value->floor();
		} else if($operator === '>' || $operator === '<='){
			$this->value->ceil();
		} else if($operator === '='){
			$this->value2 = clone $this->value;

			$this->value->floor();
			$this->value2->ceil();
		}
	}


	public function resolve(int $index = 0) : int {
		if(isset($this->value2)){
			$index1 = $index+1;
			$this->query = "{$this->attribute->get_prefixed_db_column()} BETWEEN :cond_{$index} AND :cond_{$index1}";

			$this->values = [
				"cond_{$index}" => $this->value->to_db(),
				"cond_{$index1}" => $this->value2->to_db()
			];

			return $index+2;
		} else {
			$this->query = "{$this->attribute->get_prefixed_db_column()} {$this->operator} :cond_{$index}";
			$this->values = ["cond_{$index}" => $this->value->to_db()];

			return $index+1;
		}
	}
}
?>
