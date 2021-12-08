<?php
namespace Octopus\Core\Model\Database\Requests;

class InsertRequest extends DatabaseRequest {
	# inherited from DatabaseRequest
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;


	function __construct(string $class) {
		parent::__construct($class);

		foreach($class::PROPERTIES as $property => $definition){
			$def = new PropertyDefinition($property, $definition);

			if($def->type_is('primitive') || $def->type_is('identifier')){
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
		$query = [];

		$query[] = 'INSERT INTO';
		$query[] = $this->table;
		$query[] = '(';
		$query[] = implode($this->columns, ', ');
		$query[] = ') VALUES (:';
		$query[] = implode($this->columns, ', :');
		$query[] = ')';

		return implode($query, ' ');
	}

	protected function check_condition(?RequestCondition $condition) : void {
		if(!is_null($condition)){
			throw new Exception('condition must be null for this type of request.');
		}
	}
}
?>
