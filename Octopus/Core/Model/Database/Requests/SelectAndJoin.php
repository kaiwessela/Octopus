<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Attributes\Attribute;
use Exception;

// TODO explainations

trait SelectAndJoin {


	public function add_join(JoinRequest $request) : void {
		$this->flow->check_step('build');

		if($request->get_foreign_attribute()->get_db_table() !== $this->table){
			throw new Exception('Foreign Attribute db table must match this request’s table');
		}

		$this->joins[] = $request;
	}


	public function get_columns() : array {
		if(!$this->flow->is_at('resolve')){
			$this->resolve();
		}

		return $this->columns;
	}


	protected static function create_column_string(Attribute $attribute) : string {
		return "	{$attribute->get_full_db_column()} AS '{$attribute->get_prefixed_db_column()}'";
	}


}
?>
