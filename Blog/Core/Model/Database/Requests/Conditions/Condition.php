<?php
namespace Octopus\Core\Model\Database\Requests\Conditions;

abstract class Condition {
	protected string $query;
	protected array $values;


	function __construct() {
		$this->values = [];
	}


	abstract protected function resolve(int $index = 0) : int;


	final public function get_query() : string {
		if(!isset($this->query)){
			$this->resolve();
		}

		return $this->query;
	}

	final public function get_values() : array {
		if(!isset($this->query)){
			$this->resolve();
		}

		return $this->values;
	}
}
?>
