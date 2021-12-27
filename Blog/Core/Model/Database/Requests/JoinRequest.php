<?php
namespace Octopus\Core\Model\Database\Requests;
use \Octopus\Core\Model\Database\Requests\Request;
use \Octopus\Core\Model\Database\Requests\Conditions\Condition;
use \Octopus\Core\Model\DataObject;
use \Octopus\Core\Model\DataObjectRelationList;
use \Octopus\Core\Model\DataType;
use Exception;

class JoinRequest extends Request {
	# inherited from Request
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;
	# private ?array $values;
	private string $base_table;
	private array $joins;


	function __construct(string $class, string $origin_class) {
		parent::__construct($class);

		$this->base_table = $origin_class::DB_PREFIX; // TEMP;

		foreach($this->object_class::get_property_definitions() as $property => $definition){
			$column = "{$this->table}.{$column} AS {$this->column_prefix}_{$column}";

			if($definition->type_is('primitive') || $definition->type_is('identifier')){
				$this->columns[] = $column;
			} else if($def->type_is('object')){
				if($definition->class_is($origin_class){ // TODO check this
					continue;
				}

				if($definition->supclass_is(DataType::class)){
					$this->columns[] = $column;
				} else if($definition->supclass_is(DataObject::class)){
					$this->joins[] = new JoinRequest($definition->get_class(), $origin_class);
				} else if($definition->supclass_is(DataObjectRelationList::class)){
					$this->joins[] = new JoinRequest($definition->get_class(), $origin_class);
				}
			}
		}
	}


	public function get_query() : string {

		$other_joins = '';
		foreach($this->joins as $join){
			$other_joins .= $join->get_query() . ' ';
		}

		return "LEFT JOIN {$this->table} ON {$this->table}.id = {$this->base_table}.id {$other_joins}";
	}


	protected function validate_condition(?Condition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
