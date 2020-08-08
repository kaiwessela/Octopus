<?php
namespace \Blog\Backend\Exceptions;
use Exception;

class WrongObjectStateException extends Exception {
	function __construct($required = null) {
		$message = 'Wrong Object State';
		if($required == 'empty'){
			$message .= ': object must be empty.';
		} else if($required == 'not empty'){
			$message .= ': object must not be empty.';
		} else if($required == 'new'){
			$message .= ': object must be new.';
		} else if($required == 'not new'){
			$message .= ': object must not be new.';
		}

		parent::__construct($message);
	}
}
?>
