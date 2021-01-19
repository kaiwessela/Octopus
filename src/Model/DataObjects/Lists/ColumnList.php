<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Column;

class ColumnList extends DataObjectList {

	#	@inherited
	#	public $objects;	{alias $columns}
	#	public $count;
	#
	#	private $new;
	#	private $empty;

	const OBJECTS_ALIAS = 'columns';


	protected static function load_each(array $data) : Column {
		$obj = new Column();
		$obj->load_single($data);
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
