<?php
namespace Octopus\Modules\Posts;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Modules\Posts\PostColumnRelationship;

class PostColumnRelationshipList extends RelationshipList {
	const RELATION_CLASS = PostColumnRelationship::class;
}
?>
