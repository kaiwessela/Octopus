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
		$this->flow->check_step('build');

		if($request->get_foreign_attribute()->get_db_table() !== $this->table){
			throw new Exception('Foreign Attribute db table must match this request’s table');
		}

		$this->joins[] = $request;
	}


	protected static function create_column_string(Attribute $attribute) : string {
		return "{$attribute->get_prefixed_db_column()} AS `{$attribute->get_result_column()}`";
	}


	protected function has_unique_identifier(Condition $condition) : bool {
		// TODO table might lead to trouble because of alias tables
		if($condition instanceof IdentifierCondition){
			return $condition->get_attribute()->get_db_table() === $this->table;
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


	protected function is_multidimensional() : bool { // TODO could be improved, can produce false positives
		$md = false;

		foreach($this->joins as $join){
			$md |= ($join->is_multijoin() || $join->is_multidimensional());
		}

		return $md;
	}


}
?>
