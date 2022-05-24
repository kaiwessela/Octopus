<?php
namespace Octopus\Core\Model\Database\Exceptions;
use \Octopus\Core\Model\Database\Request;
use \Exception;

class EmptyRequestException extends Exception {
	protected Request $request;


	function __construct(Request $request) {
		parent::__construct('Empty Database request - no attributes set.');

		$this->request = $request;
	}


	public function get_request() : Request {
		return $this->request;
	}
}
?>
