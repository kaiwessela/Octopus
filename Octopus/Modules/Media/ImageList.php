<?php
namespace Blog\Model\DataObjects\Lists\Media;
use \Blog\Model\DataObjects\Lists\MediaList;
use \Blog\Model\DataObjects\Media\Image;

class ImageList extends MediaList {

#	@inherited
#	public $objects;
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Image::class;
	const OBJECTS_ALIAS = 'images';


	const SELECT_QUERY = <<<SQL
SELECT * FROM media WHERE medium_class = 'image'
ORDER BY medium_longid
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM media WHERE medium_class = 'image'
SQL; #---|

}
?>
