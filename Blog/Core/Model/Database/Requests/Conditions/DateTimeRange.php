<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use DateTime;
use Exception;

class DateTimeRange extends Condition {
	protected PropertyDefinition $property;
	protected DateTime $from;
	protected DateTime $to;


	function __construct(PropertyDefinition $property, DateTime $from, DateTime $to) {
		parent::__construct();

		$this->property = $property;
		$this->from = $from;
		$this->to = $to;
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->property->get_db_table()}.{$this->property->get_db_column()} BETWEEN :cond_{$index} AND :cond_{$index+1}";
		$this->values = [
			"cond_{$index}" => $this->from->format('Y-m-d H:i:s'),
			"cond_{$index+1}" => $this->to->format('Y-m-d H:i:s')
		];

		return $index + 2;
	}
}
?>
