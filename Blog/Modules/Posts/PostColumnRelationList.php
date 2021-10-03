<?php
namespace Blog\Model\DataObjects\Relations\Lists;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Abstracts\DataObjectRelationList;
use \Blog\Model\DataObjects\Relations\PostColumnRelation;

class PostColumnRelationList extends DataObjectRelationList {

#	@inherited
#	public array $relations;
#
#	private array $deletions;
#	private array $updates;
#
#	private bool $disabled;

	const RELATION_CLASS = PostColumnRelation::class;


	protected function db_valuestring(int $index) : string {
		return "(:id_$index, :post_id_$index, :column_id_$index)";
	}


	protected function db_idstring(int $index) : string {
		return "postcolumnrelation_id = :id_$index";
	}


	protected function db_values(int $index, string $relation_id) : array {
		return [
			"id_$index" => $this->relations[$relation_id]->id,
			"post_id_$index" => $this->relations[$relation_id]->post->id,
			"column_id_$index" => $this->relations[$relation_id]->column->id
		];
	}


	const PUSH_QUERY = <<<SQL
INSERT INTO postcolumnrelations (
	postcolumnrelation_id,
	postcolumnrelation_post_id,
	postcolumnrelation_column_id
) VALUES %VALUESTRING%
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM postcolumnrelations WHERE %IDSTRING%
SQL; #---|

}
?>
