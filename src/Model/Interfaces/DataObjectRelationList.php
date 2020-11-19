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
		$this->relations[$relation->id] = $relations;
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

	public function import($data, DataObject $object) {


		$errors = new InputFailedException();

		// TODO check for unique

		foreach($data as $index => $relationdata){
			$action = $relationdata['action'];

			if($action == 'new'){
				$relation = $this::RELATION_PROTOTYPE;
				$relation = new $relation();

				try {
					$relation->generate($object);
					$relation->import($relationdata);
					$this->insertions[] = $relation;
					$this->relations[$relation->id] = $relation;
				} catch(InputFailedException $e){
					$errors->merge($e, $index);
				}

				continue;

			} else if($action == 'edit' || $action == 'delete') {
				$relation_id = $relationdata['relation_id'];
				$relation = $this->relations[$relation_id];

				if(!$relation instanceof DataObjectRelation){
					// Exception
					continue;
				}

				// TODO check if anything changed at all
				if($action == 'edit'){
					try {
						$relation->import($relationdata);
					} catch(InputFailedException $e){
						$errors->merge($e, $index);
					}

					$this->updates[] = $relation;
					$this->relations[$relation->id] = $relation;
					continue;

				} else if($action == 'delete'){
					$this->deletions[] = $relation;
					$this->relations[$relation->id] = null;
					continue;
				}

			} else {
				continue;
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}

		return $this->relations;
	}

	public function old_import($data) { // DEPRECATED
#	@params
#	  - data:
#		[
#			0 => [
#				'action' => 'new',
#				'primary_id' => string,
#				'secondary_id' => string,
#				…
#			],
#			1 => [
#				'action' => 'edit',
#				'relation_id' => string,
#				'primary_id' => string,
#				'secondary_id' => string,
#				…
#			],
#			2 => [
#				'action' => 'delete',
#				'relation_id' => string
#				'primary_id' => string,
#				'secondary_id' => string,
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

	private get_objects() {
		$objects = [];

		foreach($this->relations as $relation){
			$objects[] = $relation->object;
		}

		return $objects;
	}

	private get_object_ids() {
		$id_list = [];

		foreach($this->relations as $relation){
			$id_list[] = $relation->id;
		}

		return $id_list;
	}
}
?>
