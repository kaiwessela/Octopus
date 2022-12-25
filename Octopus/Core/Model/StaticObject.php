<?php
namespace Octopus\Core\Model;

abstract class StaticObject {


	// abstract public function load(mixed $data) : void;
	// abstract public function edit(mixed $data) : void;
	// abstract public function export() : mixed;
	// abstract public function arrayify() : mixed;

	abstract public function equals(mixed $object) : bool;
}
?>
