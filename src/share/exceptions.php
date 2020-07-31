<?php
class InvalidInputException extends Exception {
	public $subject;	# name of the property / variable
	public $required;	# required value (i.e. '/^[a-z0-9]{1,32}$/')
	public $input;		# received input value

	function __construct($subject, $required = 'N/A', $input = null) {
		$this->subject = $subject;
		$this->required = $required;
		$this->input = $input;

		if($input == null){
			parent::__construct('Missing Input Value: ' . $subject . '; Required: ' . $required);
		} else {
			parent::__construct('Invalid Input Value: ' . $subject . '; Required: ' . $required . '; Input: ' . $input);
		}
	}

	public function get_subject() {
		return $this->subject;
	}

	public function get_required() {
		return $this->required;
	}

	public function get_input() {
		return $this->input;
	}
}

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

class DatabaseException extends Exception {
	public $query;			# original query
	public $debug_info;		# PDOStatement->debugDumpParams
	public $error_code;		# PDOStatement->errorCode
	public $error_info;		# PDOStatement->errorInfo

	function __construct($pdo_statement) {
		parent::__construct('Database Exception; use DatabaseException methods for details');

		if(!$pdo_statement instanceof PDOStatement){
			throw new InvalidArgumentException('Invalid Argument; PDOStatement required; ' . serialize($pdo_statement));
		}

		$this->query = $pdo_statement->queryString;
		$this->debug_info = $pdo_statement->debugDumpParams();
		$this->error_code = $pdo_statement->errorCode();
		$this->error_info = $pdo_statement->errorInfo();
	}

	public function get_query() {
		return $this->query;
	}

	public function get_debug_info() {
		return $this->debug_info;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	public function get_error_info() {
		return $this->error_info;
	}
}

class ImageManagerException extends Exception {
	function __construct($message) {
		parent::__construct('ImageManager >> ERROR: ' . $message);
	}
}
?>
