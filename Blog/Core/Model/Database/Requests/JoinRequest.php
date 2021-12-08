<?php
namespace Octopus\Core\Model\Database\Requests;

class JoinRequest extends DatabaseRequest {
	# inherited from DatabaseRequest
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;
	private string $base_table;
	private array $joins;


	function __construct(string $class, string $origin_class) {
		parent::__construct($class);

		foreach($class::PROPERTIES as $property => $definition){
			$def = new PropertyDefinition($property, $definition);
			$column = "{$this->table}.{$column} AS {$this->column_prefix}_{$column}";

			if($def->type_is('primitive') || $def->type_is('identifier')){
				$this->columns[] = $column;
			} else if($def->type_is('object')){
				if($def->class_is($origin_class){ // TODO check this
					continue;
				}

				if($def->supclass_is(DataType::class)){
					$this->columns[] = $column;
				} else if($def->supclass_is(DataObject::class)){
					$this->joins[] = new JoinRequest($def->get_class(), $origin_class);
				} else if($def->supclass_is(DataObjectRelationList::class)){
					$this->joins[] = new JoinRequest($def->get_class(), $origin_class);
				}
			}
		}
	}


	public function get_query() : string {
		$query = [];

		$query[] = 'LEFT JOIN';
		$query[] = $this->table;
		$query[] = 'ON';
		$query[] = "{$this->table}.id";
		$query[] = '=';
		// TODO

		foreach($joins as $join){
			$query[] = $join->get_query();
		}

		return implode($query, ' ');
	}

	protected function check_condition(?RequestCondition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
