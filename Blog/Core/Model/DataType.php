<?php
namespace Blog\Model\Abstracts;

interface DataType {

	function __construct(string $value);
	function __toString();
	public static function import(string $value) : DataType;
	public function staticize();
}
?>
