<?php
namespace Octopus\Core\Model;
use Exception;
use Octopus\Core\Model\Attributes\EntityReference;
use Octopus\Core\Model\Entity;


abstract class Relationship extends Entity {
	# protected ID $id;
	# protected EntityReference $[name of 1st entity];
	# protected EntityReference $[name of 2nd entity];
	# ...other attributes

	protected string $context_attribute;
	protected string $relatum_attribute;

	const DISTINCT = false;


	final protected function init() : void {
		foreach($this->get_attributes() as $name){
			if($this->$name instanceof EntityReference){
				if($this->$name->get_class() === $this->context::class){
					if(isset($this->context_attribute)){
						throw new Exception("Context collision: There can only be one context attribute.");
					}

					// $this->$name->load($this->context);
					$this->context_attribute = $name;
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

		if(!isset($this->context_attribute) || !isset($this->relatum_attribute)){
			throw new Exception('Attribute error.'); // TODO
		}
	}


	public function get_context_attribute() : EntityReference {
		return $this->{$this->context_attribute};
	}


	public function get_relatum_attribute() : EntityReference {
		return $this->{$this->relatum_attribute};
	}


	public function get_relatum() : Entity {
		return $this->get_relatum_attribute()->get_value();
	}
}
?>
