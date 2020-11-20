<?php
namespace Blog\Model\DataObjects\Lists;

class GroupList extends DataObjectList {

	#	@inherited
	#	public $objects;	{alias $groups}
	#	public $count;
	#
	#	private $new;
	#	private $empty;

	const OBJECTS_ALIAS = 'groups';


	private static function load_each($data){
		$obj = new Group();
		$obj->load($data);
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
