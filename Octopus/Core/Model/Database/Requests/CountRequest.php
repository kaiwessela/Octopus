<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\SelectRequest;

// TODO explainations

class CountRequest extends Request {
	protected array $joins;


	function __construct(SelectRequest $request) {
		parent::__construct($request->table);

		$this->joins = $request->get_joins();
		$this->condition = $request->get_condition();
	}


	protected function resolve() : void {
		$this->flow->step('resolve');

		$this->query = "SELECT COUNT(*) AS 'total' FROM `{$this->table}`".PHP_EOL;

		foreach($this->joins as $join){
			$this->query .= $join->get_query().PHP_EOL;
		}

		if(!is_null($this->condition)){
			$this->query .= "WHERE {$this->condition->get_query()}";
			$this->set_values($this->condition->get_values());
		}
	}
}
?>
