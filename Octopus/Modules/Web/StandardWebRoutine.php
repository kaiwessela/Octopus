<?php
namespace Octopus\Modules\Web;
use Octopus\Core\Controller\StandardRoutine;
use Octopus\Modules\Web\WebRoutine;

abstract class StandardWebRoutine extends StandardRoutine implements WebRoutine {
	protected string $status;


	public function get_status() : string {
		return $this->status;
	}


	protected function set_status(string $status) : void {
		$this->status = $status;
	}


	public function status_is(string $status) : bool {
		return $this->status === $status;
	}
}