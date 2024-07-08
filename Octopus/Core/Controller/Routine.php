<?php
namespace Octopus\Core\Controller;
use Octopus\Core\Controller\Environment;

interface Routine {

	public function bind(Environment &$environment, ?string $name) : void;

	public function run() : void;
}