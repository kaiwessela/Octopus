<?php
namespace Octopus\Modules\Astronauth;
use \Octopus\Core\Model\EntityList;
use \Octopus\Modules\Astronauth\Login;

class LoginList extends EntityList {
	const ENTITY_CLASS = Login::class;
}
?>