<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Exceptions\EmptyRequestException;
use Exception;

// TODO explainations

class InsertRequest extends Request {


	# function __construct() is handled by the parent


	protected function resolve() : void {
		if(empty($this->attributes)){
			throw new EmptyRequestException($this);
		}

		$this->flow->step('resolve');

		$columns = [];
		foreach($this->attributes as $attribute){
			$columns[] = "	`{$attribute->get_db_column()}` = :{$attribute->get_name()}";
		}

		$this->query = "INSERT INTO `{$this->table}` SET".PHP_EOL;
		$this->query .= implode(','.PHP_EOL, $columns);
	}


	protected function check_condition(?Condition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
