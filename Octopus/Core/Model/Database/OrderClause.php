<?php
namespace Octopus\Core\Model\Database;
use Exception;
use Octopus\Core\Model\Attribute;

class OrderClause {
	private Attribute $by;
	private string $sequence;
	private int $original_index;


	function __construct(Attribute $by, mixed $sequence, int $original_index) {
		if(!in_array($sequence, ['ASC', 'DESC'])){
			throw new Exception("Invalid sequence «{$sequence}» in order clause #{$original_index}.");
		}

		$this->by = $by;
		$this->sequence = $sequence;
		$this->original_index = $original_index;
	}


	final public function get_query() : string {
		return "{$this->by->get_prefixed_db_column()} {$this->sequence}";
	}
}
?>