<?php
namespace Blog\Model\Abstracts;

interface DataType {

	function __construct($value);
	function __toString();
	function import($value);
}
?>
