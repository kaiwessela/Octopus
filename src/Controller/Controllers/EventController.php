<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Event;
use \Blog\Model\DataObjects\Lists\EventList;

class EventController extends Controller {
	const MODEL = Event::class;
	const LIST_MODEL = EventList::class;
}
?>
