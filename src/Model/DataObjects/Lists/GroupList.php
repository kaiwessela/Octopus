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


	protected static function load_each($data){
		$obj = new Group();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM groups
LEFT JOIN persongrouprelations ON persongrouprelation_group_id = group_id
LEFT JOIN persons ON person_id = persongrouprelation_person_id
LEFT JOIN images ON image_id = person_image_id
ORDER BY group_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM groups
SQL; #---|
}
?>
