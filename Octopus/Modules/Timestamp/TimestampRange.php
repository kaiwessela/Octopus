<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Modules\Timestamp\Timestamp;

// TODO explainations

class TimestampRange extends Condition {
	protected Attribute $attribute;
	protected Timestamp $from;
	protected Timestamp $to;


	function __construct(Attribute $attribute, Timestamp $from, Timestamp $to) {
		parent::__construct();

		$this->attribute = $attribute;
		$this->from = $from;
		$this->to = $to;

		$this->from->floor();
		$this->to->ceil();
	}


	public function resolve(int $index = 0) : int {
		$index1 = $index+1;
		$this->query = "{$this->attribute->get_prefixed_db_column()} BETWEEN :cond_{$index} AND :cond_{$index1}";
		$this->values = [
			"cond_{$index}" => $this->from->to_db(),
			"cond_{$index1}" => $this->to->to_db()
		];

		return $index + 2;
	}
}
?>
