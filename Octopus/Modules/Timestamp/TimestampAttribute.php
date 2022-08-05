<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Modules\Timestamp\Timestamp;

class TimestampAttribute extends StaticObjectAttribute {

	protected const OBJECT_CLASS = Timestamp::class;


	public function resolve_pull_condition(mixed $option) : ?Condition {
		// TEMP
		if(is_string($option)){
			return new TimestampCompare($this, '=', $option);
		} else {
			throw new Exception();
		}
	}

}
?>
