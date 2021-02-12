<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Lists\PersonList;

class PersonController extends Controller {
	const MODEL = Person::class;
	const LIST_MODEL = PersonList::class;
}
?>
