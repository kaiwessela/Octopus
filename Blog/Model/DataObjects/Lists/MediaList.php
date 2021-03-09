<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Medium;

class MediaList extends DataObjectList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Medium::class;
	const OBJECTS_ALIAS = 'media';


	const SELECT_QUERY = <<<SQL
SELECT * FROM media
ORDER BY medium_longid
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM media
SQL; #---|

}
?>
