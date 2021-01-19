<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Event;

class EventList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $posts}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'events';


	protected static function load_each(array $data) : Event {
		$obj = new Event();
		$obj->load_single($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM events
ORDER BY event_timestamp
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM posts
SQL; #---|

}
?>
