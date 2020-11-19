<?php
namespace Blog\Model\DataObjects\Lists;

class ImageList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $images}
#
#	private $new;
#	private $empty;

	const OBJECTS_ALIAS = 'images';


	private static function load_each($data){
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
