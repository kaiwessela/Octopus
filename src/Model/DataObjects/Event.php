<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;

class Event extends DataObject {

#			NAME				TYPE			REQUIRED	PATTERN		DB NAME		DB VALUE
	public $title;			#	str				*			.{1,50}		=			=
	public $organisation;	#	str				*			.{1,40}		=			=
	public $timestamp;		#	str(timestamp)	*						=			=
	public $location;		#	str							.{0,60}		=			=
	public $description;	#	str										=			=
	public $cancelled;		#	bool									=			= (int)

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const FIELDS = [
		'title' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,50}'
		],
		'organisation' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,40}'
		],
		'timestamp' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}( [0-2][0-9]:[0-5][0-9](:[0-5][0-9])?)?'
		],
		'location' => [
			'type' => 'string',
			'required' => false,
			'pattern' => '.{0,60}'
		],
		'description' => [
			'type' => 'string'
		],
		'cancelled' => [
			'type' => 'boolean'
		]
	];


	public function load($data) {
		$this->req('empty');

		$this->load_single($data[0]);
	}


	public function load_single($data) {
		$this->req('empty');

		$this->id = $data['event_id'];
		$this->longid = $data['event_longid'];
		$this->title = $data['event_title'];
		$this->organisation = $data['event_organisation'];
		$this->timestamp = $data['event_timestamp'];
		$this->location = $data['event_location'];
		$this->description = $data['event_description'];
		$this->cancelled = (bool) $data['event_cancelled'];

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->title = $this->title;
		$obj->organisation = $this->organisation;
		$obj->timestamp = $this->timestamp;
		$obj->location = $this->location;
		$obj->description = $this->description;
		$obj->cancelled = $this->cancelled;

		return $obj;
	}


	protected function db_export() {
		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'organisation' => $this->organisation,
			'timestamp' => $this->timestamp,
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
