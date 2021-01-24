<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Group;
use \Blog\Model\DataObjects\Lists\GroupList;

class GroupController extends Controller {
	const MODEL = Group::class;
	const LIST_MODEL = GroupList::class;

	const PAGINATABLE = true;
}
?>
