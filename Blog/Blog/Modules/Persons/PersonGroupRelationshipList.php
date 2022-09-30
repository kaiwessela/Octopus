<?php
namespace Octopus\Modules\Persons;
use \Octopus\Core\Model\RelationshipList;
use \Octopus\Modules\Persons\PersonGroupRelationship;

class PersonGroupRelationshipList extends RelationshipList {
	const RELATION_CLASS = PersonGroupRelationship::class;
}
?>
