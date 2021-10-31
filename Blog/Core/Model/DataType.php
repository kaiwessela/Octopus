<?php // CODE --, COMMENTS --, IMPORTS --
namespace Blog\Core\Model;

interface DataType {

	function __construct(string $value);
	function __toString();
	public static function import(string $value) : DataType;
	public function staticize();
}
?>
