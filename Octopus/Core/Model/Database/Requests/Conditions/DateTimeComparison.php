<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use DateTime;
use Exception;

// TODO explainations

class DateTimeComparison extends Condition {
	protected Attribute $attribute;
	protected string $operator;
	protected DateTime|string $value;


	function __construct(Attribute $attribute, string $operator, DateTime|string $value = 'now') {
		parent::__construct();

		if(!in_array($operator, ['=', '<', '>', '<=', '>='])){
			throw new Exception("Invalid operator: «{$operator}».");
		}

		$this->attribute = $attribute;
		$this->operator = $operator;
		$this->value = $value;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_prefixed_db_column()} {$this->operator} ";

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
