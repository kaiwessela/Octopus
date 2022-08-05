<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Attributes\Attribute;
use \Octopus\Modules\Timestamp\Timestamp;
use \DateTimeImmutable;
use \DateTimeZone;
use \Exception;

// TODO explainations

class DateTimeRange extends Condition {
	protected Attribute $attribute;
	protected DateTimeImmutable $from;
	protected DateTimeImmutable $to;


	function __construct(Attribute $attribute, DateTimeImmutable|string $from, DateTimeImmutable|string $to) {
		parent::__construct();

		$this->attribute = $attribute;

		if(is_string($from)){
			$this->from = Timestamp::parse_input($from, round_up:false);
		} else {
			$this->from = $from;
		}

		if(is_string($to)){
			$this->to = Timestamp::parse_input($to, round_up:true);
		} else {
			$this->to = $to;
		}
	}


	public function resolve(int $index = 0) : int {
		$this->query = "{$this->attribute->get_prefixed_db_column()} BETWEEN :cond_{$index} AND :cond_{$index+1}";
		$this->values = [
			"cond_{$index}" => $this->from->setTimezone(new DateTimeZone('UTC')->format('Y-m-d H:i:s'),
			"cond_{$index+1}" => $this->to->setTimezone(new DateTimeZone('UTC')->format('Y-m-d H:i:s')
		];

		return $index + 2;
	}
}
?>
