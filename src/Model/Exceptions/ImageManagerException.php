<?php
namespace Blog\Model\Exceptions;
use Exception;

class ImageManagerException extends Exception {
	function __construct($message) {
		parent::__construct('ImageManager >> ERROR: ' . $message);
	}
}
?>
