<?php
namespace Astronauth\Core\Controller\Controllers;

abstract class AuthenticationController extends Controller {
	# inherited from Controller:
	# protected string $importance;
	# protected int $status;

	abstract public function require_permission(string $permission) : void;


	abstract public function has_permission(string $permission) : bool;
}
?>
