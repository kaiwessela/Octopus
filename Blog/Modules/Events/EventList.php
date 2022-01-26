<?php
namespace Octopus\Modules\Events;
use \Octopus\Core\Model\EntityList;
use \Octopus\Core\Model\Database\Requests\SelectRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\DateTimeComparison;
use \Octopus\Modules\Events\Event;
use DateTime;

class EventList extends EntityList {
	const ENTITY_CLASS = Event::class;


	protected static function shape_select_request(SelectRequest &$request, array $options) : void {
		if(isset($options['future'])){
			$request->set_condition(new DateTimeComparison(
				Event::get_attribute_definitions()['datetime'],
				'>=',
				new DateTime('today')
			));

			$request->set_order(Event::get_attribute_definitions()['datetime'], desc:false);
		} else {
			$request->set_order(Event::get_attribute_definitions()['datetime'], desc:true);
		}
	}
}
?>
