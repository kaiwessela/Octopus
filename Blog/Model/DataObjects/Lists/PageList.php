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

	const OBJECT_CLASS = Page::class;
	const OBJECTS_ALIAS = 'pages';


	protected static function load_each(array $data) : Page {
		$obj = new Page();
		$obj->load_single($data);
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
