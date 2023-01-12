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


	// DEPRECATED
	// final function __construct(Entity $context, string $db_prefix) {
	// 	$this->db_prefix = $db_prefix;

	// 	$this->init_attributes();

	// 	foreach($this->get_attributes() as $name){
	// 		if($this->$name instanceof EntityReference){
	// 			if($this->$name->get_class() === $context::class){
	// 				if(isset($this->context_attribute)){
	// 					throw new Exception("Context collision: There can only be one context attribute.");
	// 				}

	// 				$this->$name->load($context);
	// 				$this->context_attribute = $name;
	// 				$this->context = &$context;
	// 			} else {
	// 				if(isset($this->relatum_attribute)){
	// 					throw new Exception("Relatum collision: There can only be one relatum attribute.");
	// 				}

	// 				$this->relatum_attribute = $name;
	// 			}
	// 		} else if(!($this->$name instanceof IdentifierAttribute || $this->$name instanceof PropertyAttribute)){
	// 			throw new Exception("Invalid attribute defined: «{$name}».");
	// 		}
	// 	}

	// 	if(!isset($this->context_attribute) || !isset($this->relatum_attribute)){
	// 		throw new Exception('Attribute error.'); // TODO
	// 	}
	// }


	// DEPRECATED, move into RelationshipList or EntityList
	// final public function join(array $include_attributes) : Join {
	// 	$request = new Join($this, $this->get_context_attribute(), $this->context->get_primary_identifier());
	// 	$this->resolve_pull_attributes($request, $include_attributes);
	// 	return $request;
	// }


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
