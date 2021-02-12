<?php
namespace Blog\Model\Exceptions;
use Exception;

class EmptyResultException extends Exception {
	public $query;	# original query
	public $values;	# original values

	function __construct($query, $values = []) {
		parent::__construct('MySQL Query unexpectedly returned no results');

		$this->query = $query;
		$this->values = $values;
	}

	public function get_query() {
		return $this->query;
	}

	public function get_values() {
		return $this->values;
	}
}
?>
