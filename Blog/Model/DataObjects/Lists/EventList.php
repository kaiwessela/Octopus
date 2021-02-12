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

	const OBJECT_CLASS = Event::class;
	const OBJECTS_ALIAS = 'events';


	protected function pull_query(?int $limit = null, ?int $offset = null, ?array $options = null) : string {
		$query = $this::SELECT_QUERY;

		if(is_array($options) && in_array('future', $options)){
			$query .= ' WHERE DATE(event_timestamp) >= DATE(NOW()) ORDER BY event_timestamp ';
		} else {
			$query .= ' ORDER BY event_timestamp DESC ';
		}

		$query .= ($limit) ? (($offset) ? " LIMIT $offset, $limit" : " LIMIT $limit") : null;

		return $query;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM events
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM posts
SQL; #---|

}
?>
