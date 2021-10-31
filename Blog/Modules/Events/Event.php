<?php # Event.php 2021-10-04 beta
namespace Blog\Modules\Events;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\DataTypes\MarkdownContent;
use \Blog\Modules\DataTypes\Timestamp;

class Event extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 			$title;
	protected string 			$organisation;
	protected Timestamp 		$timestamp;
	protected ?string 			$location;
	protected ?MarkdownContent 	$description;
	protected ?bool 			$cancelled;


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


	const DB_PREFIX = 'event';


	const PULL_QUERY = 'SELECT * FROM events WHERE event_id = :id OR event_longid = :id';

	const INSERT_QUERY = <<<SQL
INSERT INTO events (
	event_id,
	event_longid,
	event_title,
	event_organisation,
	event_timestamp,
	event_location,
	event_description,
	event_cancelled
) VALUES (
	:id,
	:longid,
	:title,
	:organisation,
	:timestamp,
	:location,
	:description,
	:cancelled
)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE events SET
	event_title = :title,
	event_organisation = :organisation,
	event_timestamp = :timestamp,
	event_location = :location,
	event_description = :description,
	event_cancelled = :cancelled
WHERE event_id = :id
SQL; #---|

	const DELETE_QUERY = 'DELETE FROM events WHERE event_id = :id';

}
?>
