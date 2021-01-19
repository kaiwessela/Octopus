<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Group;

class GroupList extends DataObjectList {

	#	@inherited
	#	public $objects;	{alias $groups}
	#	public $count;
	#
	#	private $new;
	#	private $empty;

	const OBJECTS_ALIAS = 'groups';


	protected static function load_each(array $data) : Group {
		$obj = new Group();
		$obj->load_single($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM groups
ORDER BY group_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM groups
SQL; #---|
}
?>
