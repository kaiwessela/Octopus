<?php
namespace Blog\Model\Exceptions;
use Exception;
use PDOStatement;
use PDOException;
use InvalidArgumentException;

class DatabaseException extends Exception {
	public $query;			# original query
	public $error_code;		# PDOStatement->errorCode
	public $error_info;		# PDOStatement->errorInfo

	function __construct(PDOStatement|PDOException $pdo) {
		if($pdo instanceof PDOStatement){
			// TODO
		} else if($pdo instanceof PDOException){

		}

		$this->query = $pdo->queryString;
		$this->error_code = $pdo->errorCode();
		$this->error_info = $pdo->errorInfo();

		parent::__construct("Database Exception - [$this->error_code]: " . implode('; ', $this->error_info) . "; '$this->query'");
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
