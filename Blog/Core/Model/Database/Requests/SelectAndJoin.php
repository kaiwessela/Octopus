<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\JoinRequest;
use \Octopus\Core\Model\Properties\PropertyDefinition;
use Exception;

// TODO explainations

trait SelectAndJoin {


	public function add_join(JoinRequest $request) : void {
		$this->cycle->check_step('build');

		if($request->get_foreign_property()->get_db_table() !== $this->table){
			throw new Exception('Foreign Property db table must match this request’s table');
		}

		$this->joins[] = $request;
	}


	public function get_columns() : array {
		if(!$this->cycle->is_at('resolve')){
			$this->resolve();
		}

		return $this->columns;
	}


	protected static function create_column_string(PropertyDefinition $property) : string {
		$column = "{$property->get_db_table()}.{$property->get_db_column()}";
		$alias = "{$property->get_db_prefix()}_{$property->get_db_column()}";
		return "	{$column} AS '{$alias}'";
	}


}
?>
