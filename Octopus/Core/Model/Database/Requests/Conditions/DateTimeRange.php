<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use DateTime;
use Exception;

// TODO explainations

class DateTimeRange extends Condition {
	protected Attribute $attribute;
	protected DateTime $from;
	protected DateTime $to;


	function __construct(Attribute $attribute, DateTime $from, DateTime $to) {
		parent::__construct();

		$this->attribute = $attribute;
		$this->from = $from;
		$this->to = $to;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_prefixed_db_column()} BETWEEN :cond_{$index} AND :cond_{$index+1}";
		$this->values = [
			"cond_{$index}" => $this->from->format('Y-m-d H:i:s'),
			"cond_{$index+1}" => $this->to->format('Y-m-d H:i:s')
		];

		return $index + 2;
	}
}
?>
