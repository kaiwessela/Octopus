<?php
namespace Blog\Model\DataObjects\Relations\Lists;

class PostColumnRelationList extends DataObjectRelationList {

#	@inherited
#	public $container;
#	public $relations;
#
#	private $insertions;
#	private $deletions;
#	private $updates;

	const CONTAINER_ALIAS = 'column'; // IDEA maybe this is not necessary


	private function insert_pair() {
		$query = 'INSERT INTO postcolumnrelations (postcolumnrelation_id, postcolumnrelation_column_id, postcolumnrelation_post_id) VALUES ';
		$values = [];

		$queries = [];
		foreach($this->insertions as $i => $insertion){
			$queries[$i] = "(:${i}_id, :${i}_column_id, :${i}_post_id)";
			$values[$i . '_id'] = $insertion->id;
			$values[$i . '_column_id'] = $insertion->container->id;
			$values[$i . '_post_id'] = $insertion->object->id;
		}

		return [
			'query' => $query . implode(', ', $queries),
			'values' => $values
		];
	}

	private function update_pair() {
		$query = 'INSERT INTO postcolumnrelations (postcolumnrelation_id, postcolumnrelation_column_id, postcolumnrelation_post_id) VALUES ';
	}
}
