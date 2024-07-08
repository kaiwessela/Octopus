<?php
namespace Octopus\Modules\Web;
use Octopus\Core\Controller\StandardRoutine;
use Octopus\Modules\Web\WebRoutine;

abstract class StandardWebRoutine extends StandardRoutine implements WebRoutine {
	protected string $status;


	public function get_status() : string {
		return $this->status;
	}


	public function status_is(string $status) : bool {
		return $this->status === $status;
	}
}