<?php
namespace Blog\Backend\Exceptions;
use Exception;
use PDOStatement;
use InvalidArgumentException;

class DatabaseException extends Exception {
	public $query;			# original query
	public $error_code;		# PDOStatement->errorCode
	public $error_info;		# PDOStatement->errorInfo

	function __construct($pdo_statement) {
		parent::__construct('Database Exception; use DatabaseException methods for details');

		if(!$pdo_statement instanceof PDOStatement){
			throw new InvalidArgumentException('Invalid Argument; PDOStatement required; ' . serialize($pdo_statement));
		}

		$this->query = $pdo_statement->queryString;
		$this->error_code = $pdo_statement->errorCode();
		$this->error_info = $pdo_statement->errorInfo();
	}

	public function get_query() {
		return $this->query;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	public function get_error_info() {
		return $this->error_info;
	}
}
?>
