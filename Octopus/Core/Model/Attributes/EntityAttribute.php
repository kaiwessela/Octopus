<?php
namespace Octopus\Core\Model\Attributes;

class EntityAttribute extends Attribute {
	protected string $class;


	public static function define(string $class) : EntityAttribute {
		if(!class_exists($class) || !is_subclass_of($class, Entity::class)){
			throw new Exception("Invalid class «{$class}».");
		}

		// TODO
		$this->class = $class;
	}


	public function load(null|string|int|float $value) : void {
		throw new Exception('do not call!');
	}


	public function edit(mixed $value) : void {
		throw new Exception('do not call!');
	}


	public function get_db_column() : string {
		return $this->name.'_id';
	}


	public function get_class() : string {
		return $this->class;
	}
}
?>
