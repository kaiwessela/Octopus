<?php
namespace Blog\Model\ImageManager\Exceptions;
use Exception;

class ImageManagerException extends Exception {
	
	function __construct($msg, $fe = false) {
		if($fe){
			parent::__construct('ImageManager | ' . $msg);
		} else {
			parent::__construct('ImageManager » ' . $msg);
		}
	}
}
?>
