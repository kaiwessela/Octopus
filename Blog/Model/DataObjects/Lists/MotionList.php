<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Motion;

class MotionList extends DataObjectList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Motion::class;
	const OBJECTS_ALIAS = 'motions';


	const SELECT_QUERY = <<<SQL
SELECT * FROM motions
ORDER BY motion_timestamp DESC
SQL; #---|

	const SELECT_IDS_QUERY = <<<SQL
SELECT * FROM motions
WHERE motion_id IN 
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM motions
SQL; #---|

}
?>
