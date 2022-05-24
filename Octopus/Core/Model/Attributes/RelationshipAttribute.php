<?php
namespace Octopus\Core\Model\Attributes;

class RelationshipAttribute extends Attribute {
	protected string $class;


	public static function define(string $class) : RelationshipAttribute {
		if(!class_exists($class) || !is_subclass_of($class, RelationshipList::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		// TODO
		$this->class = $class;
	}


	public function load(null|string|int|float $data) : void {
		throw new Exception('do not call!');
	}


	public function edit(mixed $value) : void {
		throw new Exception('do not call!');
	}


	public function get_db_column() : string {
		throw new Exception('do not call!');
	}


	public function get_class() : string {
		return $this->class;
	}
}
?>
