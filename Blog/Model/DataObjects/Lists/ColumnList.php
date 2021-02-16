<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Column;

class ColumnList extends DataObjectList {

	#	@inherited
	#	public $objects;
	#	public $count;
	#
	#	private $new;
	#	private $empty;

	const OBJECT_CLASS = Column::class;
	const OBJECTS_ALIAS = 'columns';


	const SELECT_QUERY = <<<SQL
SELECT * FROM columns
ORDER BY column_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM columns
SQL; #---|

}
