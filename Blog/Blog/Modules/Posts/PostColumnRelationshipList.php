<?php
namespace Blog\Modules\Posts;
use \Blog\Modules\Posts\PostColumnRelationship;
use \Octopus\Core\Model\RelationshipList;

class PostColumnRelationshipList extends RelationshipList {
	const RELATION_CLASS = PostColumnRelationship::class;
}
?>
