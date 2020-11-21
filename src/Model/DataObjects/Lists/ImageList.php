<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Image;

class ImageList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $images}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'images';


	protected static function load_each($data){
		$obj = new Image();
		$obj->load($data);
		return $obj;
	}


	const SELECT_QUERY = <<<SQL
SELECT * FROM images
ORDER BY image_longid
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM images
SQL; #---|

}
?>
