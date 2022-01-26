<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use Exception;

class AttributeValueExceptionList extends Exception {
	public array $exceptions;


	function __construct() {
		$this->exceptions = [];
	}


	public function push(AttributeValueException $e) : void {
		$this->exceptions[$e->name] = $e; # $e->name is the attribute name
	}


	public function merge(AttributeValueExceptionList $el, $prefix = '') : void {
		foreach($el->exceptions as $e){
			$this->exceptions["{$prefix}:{$e->name}"] = $e; // TODO this is not perfect
		}
	}


	public function is_empty() : bool {
		return count($this->exceptions) === 0;
	}
}
?>
