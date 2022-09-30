<?php
namespace Blog\Modules\Images;
use \Blog\Modules\Images\Image;
use \Octopus\Core\Model\EntityList;

class ImageList extends EntityList {
	const ENTITY_CLASS = Image::class;
}
?>
