<?php
namespace Octopus\Modules\Web;
use Exception;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Routine;
use Octopus\Modules\Web\WebEnvironment;
use Octopus\Modules\Web\WebRoutineNew;

trait WebRoutine {
	protected string $status;
	

	public function run(Environment &$env) : void {
		$this->check_environment($env);
	}


	protected function check_environment(Environment $env) : void {
		if(!$env instanceof WebEnvironment){
			throw new Exception('environment is not a WebEnvironment.');
		}
	}


	protected function set_status(string $status) : void {
		$this->status = $status;
	}


	public function get_status() : string {
		return $this->status;
	}


	public function status_is(string $status) : bool {
		return $this->status === $status;
	}
}