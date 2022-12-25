<?php
namespace Octopus\Core\Model\Attributes\Events;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Events\Event;

class AttributeEditEvent extends Event {
	protected Attribute $attribute;


	public function fire(Attribute $attribute) : void {
		$this->attribute = $attribute;
		$this->call_listeners();
	}
}
?>
