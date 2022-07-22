<?php
namespace Octopus\Modules\Events;
use \Octopus\Modules\Events\EventList;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\IDAttribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\StringAttribute;
use \Octopus\Core\Model\Attributes\BoolAttribute;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\StaticObjects\Timestamp;

class Event extends Entity {
	protected IDAttribute $id;
	protected IdentifierAttribute $identifier;
	protected StringAttribute $title;
	protected StringAttribute $organisation;
	protected StaticObjectAttribute $timestamp;
	protected StringAttribute $location;
	protected StaticObjectAttribute $description;
	protected BoolAttribute $cancelled;


	protected static array $attributes;

	const DB_TABLE = 'events';

	const LIST_CLASS = EventList::class;


	protected static function define_attributes() : array {
		return [
			'id' => IDAttribute::define(),
			'longid' => IdentifierAttribute::define(is_editable:false),
			'title' => StringAttribute::define(min:1, max:100),
			'organisation' => StringAttribute::define(min:1, max:60),
			'timestamp' => StaticObjectAttribute::define(class:Timestamp::class),
			'location' => StringAttribute::define(min:0, max:100),
			'description' => StaticObjectAttribute::define(class:MarkdownText::class),
			'cancelled' => BoolAttribute::define()
		];
	}

	const DEFAULT_PULL_ATTRIBUTES = [
		'id' => true,
		'longid' => true,
		'title' => true,
		'organisation' => true,
		'timestamp' => true,
		'location' => true,
		'description' => true,
		'cancelled' => true
	];
}
?>
