<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\Column;
use \Octopus\Core\Model\EntityList;

class ColumnList extends EntityList {
	const ENTITY_CLASS = Column::class;
}
?>
