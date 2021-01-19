<?php
namespace Blog\Model\DataObjects\Relations\Lists;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\DataObjects\Relations\PostColumnRelation;
use \Blog\Model\Exceptions\InputFailedException;

class PostColumnRelationList extends DataObjectRelationList {

#	@inherited
#	public $container;
#	public $relations;
#
#	private $insertions;
#	private $deletions;
#	private $updates;


	protected function get_relation_prototype() : PostColumnRelation {
		return new PostColumnRelation();
	}
}
?>
