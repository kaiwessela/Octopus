<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Media\Application;
use \Blog\Model\DataObjects\Lists\Media\ApplicationList;

class ApplicationController extends Controller {
	const MODEL = Application::class;
	const LIST_MODEL = ApplicationList::class;
}
?>
