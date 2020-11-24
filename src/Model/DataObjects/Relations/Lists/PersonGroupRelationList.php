<?php
namespace Blog\Model\DataObjects\Relations\Lists;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;
use \Blog\Model\Exceptions\InputFailedException;

class PersonGroupRelationList extends DataObjectRelationList {

#	@inherited
#	public $container;
#	public $relations;
#
#	private $insertions;
#	private $deletions;
#	private $updates;


	protected function get_relation_prototype() {
		return new PersonGroupRelation();
	}
}
?>
