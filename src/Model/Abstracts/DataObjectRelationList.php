<?php
namespace Blog\Model\Abstracts;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjectTrait;
use \Blog\Model\Abstracts\DataObjectRelation;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\DatabaseException;

abstract class DataObjectRelationList {
	public array $relations;

	private array $deletions;
	private array $updates;

	private bool $disabled;

	const RELATION_CLASS = null;

	use DataObjectTrait;


	function __construct() {
		$this->relations = [];
		$this->disabled = false;
		$this->updates = [];
		$this->deletions = [];
	}


	public function load(array $data, DataObject $object) : void {
		$class = $this::RELATION_CLASS;

		foreach($data as $row){
			$rel = new $class();
			$rel->load($row, $object);
			$this->relations[$rel->id] = $rel;
		}
	}


	public function push() : void {
		$pdo = $this->open_pdo();

		if(!empty($this->updates)){
			if(count($this->updates) == 1){
				$this->relations[$this->updates[0]]->push();
			} else {
				$valuestrings = [];
				$values = [];
				foreach($this->updates as $i => $id){
					$valuestrings[] = $this->db_valuestring($i);
					$values = array_merge($values, $this->db_values($i, $id));
				}

				$valuestring = implode(', ', $valuestrings);
				$query = str_replace('%VALUESTRING%', $valuestring, $this::PUSH_QUERY);

				$s = $pdo->prepare($query);
				if(!$s->execute($values)){
					throw new DatabaseException($s);
				} else {
					$this->updates = [];
				}
			}
		}

		if(!empty($this->deletions)){
			$idstrings = [];
			$values = [];
			foreach($this->deletions as $i => $id){
				$idstrings[] = $this->db_idstring($i);
				$values["id_$i"] = $id;
			}

			$idstring = implode(', ', $idstrings);
			$query = str_replace('%IDSTRING%', $idstring, $this::DELETE_QUERY);

			$s = $pdo->prepare($query);
			if(!$s->execute($values)){
				throw new DatabaseException($s);
			} else {
				$this->deletions = [];
			}
		}
	}


	public function import(array $data, DataObject $object) : void {
		$errors = new InputFailedException();

		foreach($data as $index => $relationdata){
			$action = $relationdata['action'];
			$class = $this::RELATION_CLASS;

			if($action == 'new'){
				$relation = new $class();

				try {
					$relation->generate($object);
					$relation->import($relationdata);
					$this->relations[$relation->id] = $relation;
					$this->updates[] = $relation->id;
				} catch(InputFailedException $e){
					$errors->merge($e, $index);
				}

			} else if($action == 'edit' || $action == 'delete'){
				$relation = $this->relations[$relationdata['id']];

				if(!$relation instanceof DataObjectRelation){
					// TODO maybe exception
					continue;
				}

				if($action == 'edit'){
					try {
						$relation->import($relationdata);
					} catch(InputFailedException $e){
						$errors->merge($e, $index);
					}

					$this->updates[] = $relation->id;
					$this->relations[$relation->id] = $relation;

				} else if($action == 'delete'){
					$this->deletions[] = $relation->id;
					unset($this->relations[$relation->id]);
				}

			}
		}


		// TODO check for unique

		if(!$errors->is_empty()){
			throw $errors;
		}
	}


	public function export(?string $perspective = null) {
		if(empty($this->relations)){
			return null;
		}

		foreach($this->relations as $relation){
			$relation->export($perspective);
		}

		return array_values($this->relations);
	}


	abstract protected function db_valuestring(int $index) : string;
	abstract protected function db_idstring(int $index) : string;
	abstract protected function db_values(int $index, string $relation_id) : array;
}
?>
