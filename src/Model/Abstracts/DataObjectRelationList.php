<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjectTrait;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Exceptions\InputFailedException;

abstract class DataObjectRelationList {
	public $relations;

	private $insertions;
	private $deletions;
	private $updates;

	const UNIQUE = true;

	use DataObjectTrait;


	function __construct() {
		$this->relations = [];
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

	public function export() {
		$export = [];
		foreach($this->relations as $relation){
			if(empty($relation)){
				continue;
			}

			$export[] = $relation->export();
		}
		return $export;
	}

	public function import($data, DataObject $object) {


		$errors = new InputFailedException();

		// TODO check for unique

		foreach($data as $index => $relationdata){
			$action = $relationdata['action'];

			if($action == 'new'){
				$relation = $this->get_relation_prototype();

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
				$relation_id = $relationdata['id'];
				$relation = $this->relations[$relation_id];

				if(!$relation instanceof DataObjectRelation){
					// TODO Exception
					continue;
				}

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
	}
}
?>
