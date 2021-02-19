<?php
namespace Blog\Model\Abstracts\Traits;
use \Blog\Model\Exceptions\WrongObjectStateException;

trait StateTrait {

#	REQUIRED CLASS PROPERTIES:
#	private bool $new = false;
#	private bool $empty = true;


	public function is_new() : bool {
		return $this->new;
	}

	public function is_empty() : bool {
		return $this->empty;
	}

	protected function set_new() : void {
		$this->new = true;
	}

	protected function set_not_new() : void {
		$this->new = false;
	}

	protected function set_empty() : void {
		$this->empty = true;
	}

	protected function set_not_empty() : void {
		$this->empty = false;
	}

	protected function require_new() : void {
		if(!$this->is_new()){
			throw new WrongObjectStateException('new');
		}
	}

	protected function require_not_new() : void {
		if($this->is_new()){
			throw new WrongObjectStateException('not new');
		}
	}

	protected function require_empty() : void {
		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}
	}

	protected function require_not_empty() : void {
		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}
	}
}
?>
