<?php
namespace Octopus\Modules\Timestamp;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Modules\Timestamp\Timestamp;

class TimestampAttribute extends StaticObjectAttribute {

	protected const OBJECT_CLASS = Timestamp::class;

}
?>
