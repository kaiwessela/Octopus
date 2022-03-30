<?php
namespace Octopus\Core\Model\Database\Exceptions;
use PDOStatement;
use PDOException;
use Exception;

# A DatabaseException is thrown by entitys or relationships if a request to the database failed unexpectedly

class DatabaseException extends Exception {
	protected PDOException $exception;
	protected ?PDOStatement $request;
	protected ?string $query;			# original query
	protected int|string $error_code;	# PDOStatement->errorCode
	protected ?array $error_info;		# PDOStatement->errorInfo


	function __construct(PDOException $exception, ?PDOStatement $request = null) {
		$this->exception = $exception;
		$this->request = $request;
		$this->query = $request?->queryString;
		$this->error_code = $request?->errorCode() ?? '(unknown code)';
		$this->error_info = $request?->errorInfo();

		parent::__construct("Database Exception - [{$this->error_code}]: " . implode('; ', $this->error_info ?? []) . '.');
	}


	public function get_request() : ?PDOStatement {
		return $this->request;
	}


	public function get_query() : ?string {
		return $this->query;
	}


	public function get_error_code() : int|string {
		return $this->error_code;
	}


	public function get_error_info() : ?array {
		return $this->error_info;
	}
}
?>
