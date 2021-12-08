<?php
namespace Octopus\Core\Model\Database\Requests;

class UpdateRequest extends DatabaseRequest {
	# inherited from DatabaseRequest
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;


	function __construct(string $class) {
		parent::__construct($class);

		foreach($class::PROPERTIES as $property => $definition){
			$def = new PropertyDefinition($property, $definition);

			if(($def->type_is('primitive') || $def->type_is('identifier')) && $def->is_updatable()){
				$this->columns[] = $property;
			} else if($def->type_is('object')){
				if($def->supclass_is(DataType::class)){
					$this->columns[] = $property;
				} else if($def->supclass_is(DataObject::class)){
					$this->columns[] = $property.'_id';
				}
			}
		}
	}


	public function get_query() : string {
		$this->check_condition($this->condition);

		$query = [];

		$query[] = 'UPDATE';
		$query[] = $this->table;
		$query[] = 'SET';

		// TODO

		$query[] = 'WHERE';

		$condition = $this->condition->resolve(0);
		$this->values = $condition['values'];

		$query[] = $condition['query'];

		return $query;
	}

	protected function check_condition(?RequestCondition $condition) : void {
		if(!$condition instanceof IdentifierCondition){
			throw new Exception('condition must be of type IdentifierCondition and cannot be null.');
		}
	}
}
