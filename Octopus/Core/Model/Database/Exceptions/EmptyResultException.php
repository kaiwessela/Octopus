<?php
namespace Octopus\Core\Model\Database\Exceptions;
use \Exception;
use \PDOStatement;

# An EmptyResultException is thrown if a database request was executed without errors but returned no data, usually
# because no object was found that matched the given parameters (e.g. an entity was requested with an id that does not
# exist).

class EmptyResultException extends Exception {
	protected PDOStatement $request;


	function __construct(PDOStatement $request) {
		parent::__construct('Database request executed successfully but no matching rows were found.');

		$this->request = $request;
	}


	public function get_request() : PDOStatement {
		return $this->request;
	}


	public function get_query() : string {
		return $this->request->queryString;
	}
}
?>
