<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Image;

class ImageList extends DataObjectList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Image::class;
	const OBJECTS_ALIAS = 'images';


	const SELECT_QUERY = <<<SQL
SELECT * FROM images
ORDER BY image_longid
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM images
SQL; #---|

}
?>
