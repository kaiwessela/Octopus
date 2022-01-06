<?php
namespace Octopus\Modules\Events;
use \Octopus\Core\Model\DataObject;
use \Octopus\Modules\DataTypes\MarkdownContent;
use \Octopus\Modules\DataTypes\Timestamp;

class Event extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected ?string 			$title;
	protected ?string 			$organisation;
	protected ?Timestamp 		$timestamp;
	protected ?string 			$location;
	protected ?MarkdownContent 	$description;
	protected ?bool 			$cancelled;

	const DB_TABLE = 'events';
	const DB_PREFIX = 'event';

	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		'organisation' => '.{1,60}',
		'timestamp' => Timestamp::class,
		'location' => '.{0,100}',
		'description' => MarkdownContent::class,
		'cancelled' => 'bool'
	];
}
?>
