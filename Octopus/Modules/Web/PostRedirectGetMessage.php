<?php
namespace Octopus\Modules\Web;

class PostRedirectGetMessage {
	private int $lifetime;
	private mixed $content;


	function __construct(mixed $content) {
		$this->content = $content;
		$this->lifetime = 1;
	}


	public function get_content() : mixed {
		return $this->content;
	}


	public function countdown() : void {
		$this->lifetime--;
	}


	public function is_dead() : bool {
		return $this->lifetime < 0;
	}
}