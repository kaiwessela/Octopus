<?php
if($exception?->get_original_exception()){
	$exception = $exception->get_original_exception();

	var_dump($exception);
}

throw $exception;
?>
