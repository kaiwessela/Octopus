<?php
namespace Blog\Model\Exceptions;
use Exception;

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
?>
