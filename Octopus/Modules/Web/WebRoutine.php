<?php
namespace Octopus\Modules\Web;
use Octopus\Modules\Web\WebEnvironment;

interface WebRoutine {

	public function load(array $options) : void;


	// public function check_environment(Environment $env) : void;


	// public function set_status(string $status) : void;


	public function get_status() : string;

	public function status_is(string $status) : bool;
}