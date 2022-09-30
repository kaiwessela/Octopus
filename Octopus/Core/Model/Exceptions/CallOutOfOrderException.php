<?php
namespace Octopus\Core\Model\Exceptions;
use \Exception;

class CallOutOfOrderException extends Exception {


	function __construct() {
		parent::__construct('Call out of order.');
	}
}
?>
