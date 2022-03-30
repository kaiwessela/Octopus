<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use Exception;

// TODO explainations

class UpdateRequest extends Request {


	# function __construct() is handled by the parent


	protected function resolve() : void {
		$this->flow->step('resolve');

		if(is_null($this->condition)){
			throw new Exception('An IdentifierCondition must be set for this request.');
		}

		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	{$attribute->get_db_column()} = :{$attribute->get_name()}";
		}

		$this->query = "UPDATE {$this->table} SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns).PHP_EOL;
		$this->query .= "WHERE {$this->condition->get_query()}".PHP_EOL;

		$this->set_values($this->condition->get_values());
	}


	protected function check_condition(?Condition $condition) : void {
		if(!$condition instanceof IdentifierCondition){
			throw new Exception('This request’s condition must be an IdentifierCondition.');
		}
	}
}
