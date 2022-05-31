<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use Exception;

// TODO explainations

class DeleteRequest extends Request {


	# function __construct() is handled by the parent


	protected function resolve() : void {
		$this->flow->step('resolve');

		if(is_null($this->condition)){
			throw new Exception('An IdentifierCondition must be set for this request.');
		}

		$this->query = "DELETE FROM `{$this->table}` WHERE {$this->condition->get_query()}";

		$this->set_values($this->condition->get_values());
	}


	protected function check_condition(?Condition $condition) : void {
		if(!$condition instanceof IdentifierCondition){
			throw new Exception('This request’s condition must be an IdentifierCondition.');
		}
	}
}
?>
