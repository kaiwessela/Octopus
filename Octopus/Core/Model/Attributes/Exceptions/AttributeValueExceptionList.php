<?php
namespace Octopus\Core\Model\Attributes\Exceptions;
use \Octopus\Core\Model\Attributes\Exceptions\AttributeValueException;
use \Exception;

class AttributeValueExceptionList extends Exception {
	public array $exceptions;


	function __construct() {
		$this->exceptions = [];
	}


	public function push(AttributeValueException $exception) : void {
		$this->exceptions[$exception->attribute->get_name()] = $exception;
	}


	public function merge(AttributeValueExceptionList $exceptions, ?string $prefix = null) : void {
		foreach($exceptions->exceptions as $exception){
			if(is_null($prefix)){
				$this->exceptions[$exception->attribute->get_name()] = $exception;
			} else {
				$this->exceptions["{$prefix}:{$exception->attribute->get_name()}"] = $exception;
			}
		}
	}


	public function is_empty() : bool {
		return count($this->exceptions) === 0;
	}
}
?>
