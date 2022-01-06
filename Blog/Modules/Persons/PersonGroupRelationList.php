<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Modules\Persons\PersonGroupRelation;

class PersonGroupRelationList extends DataObjectRelationList {

#	@inherited
#	public array $relations;
#
#	private array $deletions;
#	private array $updates;
#
#	private bool $disabled;

	const RELATION_CLASS = PersonGroupRelation::class;
}
?>
