<?php
namespace Blog\Model\ImageManager\Exceptions;
use \Blog\Model\ImageManager\Exceptions\ImageManagerException;

class FileException extends ImageManagerException {

	function __construct($msg) {
		parent::__construct('FileException » ' . $msg);
	}
}
?>
