<?php
namespace Octopus\Core\Model\Database\Requests;

class DeleteRequest extends DatabaseRequest {
	# inherited from DatabaseRequest
	# private string $object_class;
	# private string $table;
	# private string $column_prefix;
	# private array $columns;


	function __construct(string $class) {
		parent::__construct($class);
	}


	public function get_query() : string {
		$this->check_condition($this->condition);

		$query = [];

		$query[] = 'DELETE FROM';
		$query[] = $this->table;
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
?>
