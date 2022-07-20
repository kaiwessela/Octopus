<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\Database\Requests\Conditions\IdentifierCondition;
use \Octopus\Core\Model\Database\Requests\Conditions\AndCondition;
use \Octopus\Core\Model\Attributes\Attribute;
use \Exception;

// TODO explainations

trait SelectAndJoin {

	public function add_join(JoinRequest $request) : void {
		if($this instanceof JoinRequest && $request->is_reverse_join()){
			throw new Exception('Reverse joins can only be performed on the first level.');
		}

		$this->joins[] = $request;
	}


	protected static function create_column_string(Attribute $attribute) : string {
		return "{$attribute->get_prefixed_db_column()} AS `{$attribute->get_result_column()}`";
	}


	protected function has_unique_identifier(?Condition $condition = null) : bool {
		if($this instanceof JoinRequest && $this->is_forward_join()){
			return true;
		} else if($condition instanceof IdentifierCondition){
			return $condition->get_attribute()->get_prefixed_db_table() === $this->object->get_prefixed_db_table();
		} else if($condition instanceof AndCondition){
			foreach($condition->get_conditions() as $cond){
				if($this->has_unique_identifier($cond)){
					return true;
				}
			}

			return false;
		} else {
			return false;
		}
	}
}
?>
