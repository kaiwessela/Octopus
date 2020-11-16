<?php
namespace Blog\Model\DataObjects\Lists;

class ColumnList extends DataObjectList {

	#	@inherited
	#	public $objects;	{alias $columns}
	#
	#	private $new;
	#	private $empty;

	const OBJECTS_ALIAS = 'columns';


	private static function load_each($data){
		$obj = new Column();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM columns
ORDER BY column_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM columns
SQL; #---|

}
