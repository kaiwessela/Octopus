<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Modules\Timestamp\Timestamp;
use \DateTimeImmutable;
use \DateTimeZone;
use \Exception;

// TODO explainations

class TimestampCompare extends Condition {
	protected Attribute $attribute;
	protected string $operator;
	protected DateTimeImmutable $value;
	protected ?DateTimeImmutable $value2;


	function __construct(Attribute $attribute, string $operator, DateTimeImmutable|string $value) {
		parent::__construct();

		if(!in_array($operator, ['=', '<', '>', '<=', '>='])){
			throw new Exception("Invalid operator: «{$operator}».");
		}

		$this->attribute = $attribute;
		$this->operator = $operator;
		$this->value2 = null;

		if($value instanceof DateTimeImmutable){
			$this->value = $value;
		} else if($operator === '<' || $operator === '>='){
			$this->value = Timestamp::parse_input($value, round_up:false);
		} else if($operator === '>' || $operator === '<='){
			$this->value = Timestamp::parse_input($value, round_up:true);
		} else if($operator === '='){
			$this->value = Timestamp::parse_input($value, round_up:false);
			$value2 = Timestamp::parse_input($value, round_up:true);

			if($this->value !== $value2){
				$this->value2 = $value2;
			}
		}
	}


	public function resolve(int $index = 0) : int {
		if(isset($this->value2)){
			$index1 = $index+1;
			$this->query = "{$this->attribute->get_prefixed_db_column()} BETWEEN :cond_{$index} AND :cond_{$index1}";

			$this->values = [
				"cond_{$index}" => $this->value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
				"cond_{$index1}" => $this->value2->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s')
			];

			return $index+2;
		} else {
			$this->query = "{$this->attribute->get_prefixed_db_column()} {$this->operator} :cond_{$index}";
			$this->values = ["cond_{$index}" => $this->value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s')];

			return $index+1;
		}
	}
}
?>
