<?php
namespace Octopus\Modules\MarkdownText;
use Parsedown\Parsedown;

class MarkdownObject {
	protected ?string $raw;
	protected ?string $parsed;
	protected bool $allow_html;


	function __construct(?string $raw, bool $allow_html = false) {
		$this->raw = $raw;
		$this->allow_html = $allow_html;
	}


	function __toString() {
		return $this->raw ?? '';
	}


	public function to_db() : ?string {
		return $this->raw;
	}


	public function parse() : ?string { // TODO improve
		if(empty($this->parsed)){
			$this->parsed = Parsedown::instance()->text($this->raw ?? '');
		}

		return $this->parsed;
	}


	public function arrayify() : array {
		return [
			'raw' => $this->raw,
			'parsed' => $this->parse()
		];
	}
}
?>