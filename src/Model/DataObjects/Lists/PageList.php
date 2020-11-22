<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Page;

class PageList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $posts}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'pages';


	protected static function load_each($data) {
		$obj = new Page();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM pages
ORDER BY page_title
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM pages
SQL; #---|

}
?>
