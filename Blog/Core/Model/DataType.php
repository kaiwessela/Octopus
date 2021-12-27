<?php // CODE --, COMMENTS --, IMPORTS --
namespace Blog\Core\Model; // IDEA rename to: StaticObject (for example)

abstract class DataType {


	abstract public function import(mixed $value) : void;
	abstract public function edit(mixed $value) : void;
	abstract public function export() : mixed;

	// arrayify, toString, ...
}
?>
