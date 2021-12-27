<?php
namespace Octopus\Core\Model\Properties\Exceptions;
use \Octopus\Core\Model\Properties\Exceptions\PropertyValueException;
use Exception;

class PropertyValueExceptionList extends Exception {
	public array $exceptions;


	function __construct() {
		$this->exceptions = [];
	}


	public function push(PropertyValueException $e) : void {
		$this->exceptions[$e->name] = $e; # $e->name is the property name
	}


	public function merge(PropertyValueExceptionList $el, $prefix = '') : void {
		foreach($el->exceptions as $e){
			$this->exceptions["{$prefix}:{$e->name}"] = $e; // TODO this is not perfect
		}
	}


	public function is_empty() : bool {
		return count($this->exceptions) === 0;
	}
}
?>
