<?php
namespace Octopus\Core\Controller;
use Octopus\Core\Controller\Environment;
use Octopus\Core\Controller\Routine;

class StandardRoutine implements Routine {
	protected Environment $environment;
	protected ?string $name;


	public function bind(Environment &$environment, ?string $name) : void {
		$this->environment = $environment;
		$this->name = $name;
	}


	public function run() : void {
		return;
	}
}