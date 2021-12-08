<?php // CODE --, COMMENTS --, IMPORTS --
namespace Blog\Core\Model; // TODO rename to: StaticObject (for example)

interface DataType {

	function __construct(string $value);
	function __toString();
	public static function import(string $value) : DataType;
	public function staticize();



	// NEW NEW new

	public function load(mixed $value) : void {

	}
}
?>
