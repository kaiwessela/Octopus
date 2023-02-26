<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Database\Requests\Join;
use Octopus\Core\Model\Entity;
use Octopus\Core\Model\RelationshipList;


abstract class Relationship extends Entity {
	# protected ID $id;
	# protected EntityReference $[name of 1st entity];
	# protected EntityReference $[name of 2nd entity];
	# ...other attributes

	protected string $pivot_attribute;
	protected string $relatum_attribute;

	const DISTINCT = false;


	# Constants to be defined by each child class:
	protected const LIST_CLASS = RelationshipList::class;


	final protected function init() : void {
		// TODO fix this

		foreach($this->get_attributes() as $name){
			if($this->$name instanceof EntityReference){
				if(!isset($this->context_entity)){
					continue; // TODO check, this is a hotfix
				} else if($this->$name->get_class() === $this->context_entity::class){
					if(isset($this->pivot_attribute)){
						throw new Exception("Pivot collision: There can only be one pivot attribute.");
					}

					// $this->$name->load($this->context_entity);
					$this->pivot_attribute = $name;
				} else {
					if(isset($this->relatum_attribute)){
						throw new Exception("Relatum collision: There can only be one relatum attribute.");
					}

					$this->relatum_attribute = $name;
				}
			} else if(!$this->$name->is_pullable()){
				throw new Exception("Invalid attribute defined: «{$name}».");
			}
		}

		// if(!isset($this->pivot_attribute) || !isset($this->relatum_attribute)){
		// 	throw new Exception('Attribute error.'); // TODO
		// }
	}


	public function get_db_prefix() : ?string {
		if(!isset($this->context_attribute)){
			return null;
		}

		return "{$this->context_attribute->get_prefixed_db_table()}.{$this->context_attribute->get_name()}";
	}



	public function join_reverse(array $include_attributes) : Join {
		$request = new Join($this, $this->get_pivot_attribute(), $this->context_entity->get_primary_identifier());
		$this->resolve_pull_attributes($request, $include_attributes);
		return $request;
	}


	public function get_pivot_attribute() : EntityReference {
		return $this->{$this->pivot_attribute};
	}


	public function get_relatum_attribute() : EntityReference {
		return $this->{$this->relatum_attribute};
	}


	public function get_relatum() : Entity {
		return $this->get_relatum_attribute()->get_value();
	}
}
?>
