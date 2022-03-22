<?php
namespace Octopus\Modules\Events;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Events\EventList;
use \Octopus\Modules\StaticObjects\MarkdownText;
use \Octopus\Modules\StaticObjects\Timestamp;

class Event extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 		$title;
	protected ?string 		$organisation;
	protected ?Timestamp 	$timestamp;
	protected ?string 		$location;
	protected ?MarkdownText $description;
	protected ?bool 		$cancelled;

	protected static array $attributes;

	const DB_TABLE = 'events';
	const DB_PREFIX = 'event';

	const LIST_CLASS = EventList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		'organisation' => '.{1,60}',
		'timestamp' => Timestamp::class,
		'location' => '.{0,100}',
		'description' => MarkdownText::class,
		'cancelled' => 'bool'
	];
}
?>
