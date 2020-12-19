<?php
namespace Blog\Controller\Processors;
use \Parsedown\Parsedown;

class MarkdownContent {
	public $raw;
	public $parsed;


	function __construct($raw) {
		$this->raw = (string) $raw;
		$this->parsed = Parsedown::instance()->text($this->raw);
	}

	function __toString() {
		return $this->raw;
	}
}
?>
