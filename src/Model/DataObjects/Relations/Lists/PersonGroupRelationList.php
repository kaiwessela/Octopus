<?php
namespace Blog\Model\DataObjects\Relations\Lists;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\DataObjects\Relations\PersonGroupRelation;

class PersonGroupRelationList extends DataObjectRelationList {

#	@inherited
#	public array $relations;
#
#	private array $deletions;
#	private array $updates;
#
#	private bool $disabled;

	const RELATION_CLASS = PersonGroupRelation::class;


	protected function db_valuestring(int $index) : string {
		return "(:id_$index, :person_id_$index, :group_id_$index, :number_$index, :role_$index)";
	}


	protected function db_idstring(int $index) : string {
		return "persongrouprelation_id = :id_$index";
	}


	protected function db_values(int $index, string $relation_id) : array {
		return [
			"id_$index" => $this->relations[$relation_id]->id,
			"person_id_$index" => $this->relations[$relation_id]->person->id,
			"group_id_$index" => $this->relations[$relation_id]->group->id,
			"number_$index" => $this->relations[$relation_id]->number,
			"role_$index" => $this->relations[$relation_id]->role
		];
	}


	const PUSH_QUERY = <<<SQL
INSERT INTO persongrouprelations (
	persongrouprelation_id,
	persongrouprelation_person_id,
	persongrouprelation_group_id,
	persongrouprelation_number,
	persongrouprelation_role
) VALUES %VALUESTRING% ON DUPLICATE KEY UPDATE
	persongrouprelation_number=VALUES(persongrouprelation_number),
	persongrouprelation_role=VALUES(persongrouprelation_role)
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM persongrouprelations WHERE %IDSTRING%
SQL; #---|

}
?>
