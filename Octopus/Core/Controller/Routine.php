<?php
namespace Octopus\Core\Controller;
use Octopus\Core\Controller\Environment;

interface Routine {

	public function run(Environment &$env) : void;
}