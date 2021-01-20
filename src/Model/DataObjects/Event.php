<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataTypes\Timestamp;

class Event extends DataObject {

#						NAME				TYPE			REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 		$title;			#	str				*			.{1,50}		=			=
	public string 		$organisation;	#	str				*			.{1,40}		=			=
	public Timestamp 	$timestamp;		#	str(timestamp)	*						=			=
	public ?string 		$location;		#	str							.{0,60}		=			=
	public ?string 		$description;	#	str										=			=
	public ?bool 		$cancelled;		#	bool									=			= (int)

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'title' => '.{1,50}',
		'organisation' => '.{1,40}',
		'timestamp' => Timestamp::class,
		'location' => '.{0,60}',
		'description' => null,
		'cancelled' => null
	];


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['event_id'];
		$this->longid = $data['event_longid'];
		$this->title = $data['event_title'];
		$this->organisation = $data['event_organisation'];
		$this->timestamp = new Timestamp($data['event_timestamp']);
		$this->location = $data['event_location'];
		$this->description = $data['event_description'];
		$this->cancelled = (bool) $data['event_cancelled'];

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'organisation' => $this->organisation,
			'timestamp' => (string) $this->timestamp,
			'location' => $this->location,
			'description' => $this->description,
			'cancelled' => (int) $this->cancelled
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM events
WHERE event_id = :id OR event_longid = :id
SQL; #---|


	const COUNT_QUERY = null;


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


	const DELETE_QUERY = <<<SQL
DELETE FROM events
WHERE event_id = :id
SQL; #---|

}
?>
