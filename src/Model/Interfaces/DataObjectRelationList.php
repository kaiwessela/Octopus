<?php


abstract class DataObjectRelationList {
	public $relations;

	private $insertions;
	private $deletions;
	private $updates;

	const UNIQUE = true;


	function __construct() {

	}

	public function load($relations) {
		$this->relations = $relations;
	}

	public function push() {
		$pdo = $this->open_pdo();

		// TEMP
		if(!empty($this->insertions)){
			foreach($this->insertions as $insertion){
				$insertion->push();
			}
		}

		if(!empty($this->deletions)){
			foreach($this->deletions as $deletion){
				$deletion->delete();
			}
		}

		if(!empty($this->updates)){
			foreach($this->updates as $update){
				$update->push();
			}
		}
	}

	public function import($data) {
#	@params
#	  - data:
#		[
#			0 => [
#				'action' => 'new',
#				…
#			],
#			1 => [
#				'action' => 'empty',
#				'relation_id' => string,
#				…
#			],
#			2 => [
#				'action' => 'delete',
#				'relation_id' => string
#			]
#		];

		$errors = new InputFailedException();

		foreach($data as $key => $field){
			$action = $field['action'];
			$relation_id = $field['relation_id'];

			if($action == 'new'){
				$relation = new DataObjectRelation();

				try {
					$relation->generate($this->container);
					$relation->import($field);
				} catch(InputFailedException $e){
					$errors->merge($e, $key);
					continue;
				}

				// TODO check for unique

				$this->relations[$relation->id] = $relation;
				$this->insertions[$relation->id] = $relation;
				continue;

			} else {
				if(empty($relation_id)){
					$errors->push(new MissingValueException('relation_id', 'PostColumnRelation->id'));
					continue;
				}

				$relation = $this->relations[$relation_id];

				if(!$relation instanceof DataObjectRelation){
					$errors->push(new RelationNonexistentException('relation_id', $relation_id, 'PostColumnRelation'));
					continue;
				}

				if($action == 'edit'){
					try {
						$relation->import($field);
					} catch(InputFailedException $e){
						$errors->merge($e, $key);
						continue;
					}

					$this->relations[$relation->id] = $relation;
					$this->updates[$relation->id] = $relation;
					continue;

				} else if($action == 'delete'){
					$this->deletions[$relation->id] = $relation;
					$this->updates[$relation->id] = $relation;
					continue;

				}
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}

	private object_ids() {
		$id_list = [];

		foreach($this->relations as $relation){
			$id_list[] = $relation->id;
		}

		return $id_list;
	}
}
?>
