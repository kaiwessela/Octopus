<?php
namespace Octopus\Core\Model\Database;
use Exception;
use Octopus\Core\Model\Attribute;

class OrderClause {
	private Attribute $by;
	private string $sequence;
	private int $original_index;


	function __construct(Attribute $by, mixed $sequence, int $original_index) {
		$this->by = $by;
		$this->original_index = $original_index;

		$this->sequence = match($sequence){
			'+', 'ascending', 'asc', 'ASC' => 'ASC',
			'-', 'descending', 'desc', 'DESC' => 'DESC',
			default => throw new Exception("Invalid sequence in attribute instruction #{$original_index}.")
		};
	}


	final public function get_query() : string {
		return "{$this->by->get_prefixed_db_column()} {$this->sequence}";
	}


	final public function get_original_index() : int {
		return $this->original_index;
	}
}
?>