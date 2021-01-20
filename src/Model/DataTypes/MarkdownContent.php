<?php
namespace Blog\Model\DataTypes;
use \Blog\Model\Abstracts\DataType;
use \Parsedown\Parsedown;

class MarkdownContent implements DataType {
	private string $raw;
	private ?string $parsed;


	function __construct($value) {
		$this->raw = $value;
	}

	function __toString() {
		return $this->raw;
	}

	public function import($value) {

	}

	public function parse() : string {
		if(empty($this->parsed)){
			$this->parsed = Parsedown::instance()->text($this->raw);
		}

		return $this->parsed;
	}
}
?>
