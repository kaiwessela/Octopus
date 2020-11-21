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


	protected static function load_each($data){
		$obj = new Column();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM columns
LEFT JOIN postcolumnrelations ON postcolumnrelation_column_id = column_id
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN images ON image_id = post_image_id
ORDER BY column_name
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM columns
SQL; #---|

}
