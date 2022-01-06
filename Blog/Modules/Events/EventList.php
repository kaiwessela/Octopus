<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Event;

class EventList extends DataObjectList {
	const OBJECT_CLASS = Event::class;


	protected static function shape_select_request(SelectRequest &$request, array $options) : void {
		if(isset($options['future'])){
			// TODO condition

			$request->set_order(static::$properties['timestamp'], desc:false);
		} else {
			$request->set_order(static::$properties['timestamp'], desc:true);
		}
	}


	protected function get_pull_condition(?array $options) : ?Condition {
		if(isset($options['future'])){
			return new TimeCondition($this->properties['timestamp'], '>=', 'now', 'date');
		}
	}

	protected function get_pull_order(?array $options) : ?array {
		if(isset($options['future'])){
			return [
				'by' => $this->properties['timestamp']
			];
		} else {
			return [
				'by' => $this->properties['timestamp'],
				'desc' => true
			];
		}
	}


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


	const SELECT_IDS_QUERY = <<<SQL
SELECT * FROM events
WHERE event_id IN
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM events
SQL; #---|

}
?>
