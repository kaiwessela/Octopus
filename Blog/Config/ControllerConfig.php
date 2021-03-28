<?php
namespace Blog\Config;

use \Blog\Controller\Controllers\DataObjectController;
use \Blog\Controller\Controllers\MediaController; # subclass of DataObjectController

use \Blog\Model\DataObjects\Medium;
use \Blog\Model\DataObjects\Media\Application;
use \Blog\Model\DataObjects\Media\Audio;
use \Blog\Model\DataObjects\Media\Image;
use \Blog\Model\DataObjects\Media\Video;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Event;
use \Blog\Model\DataObjects\Group;
use \Blog\Model\DataObjects\Motion;
use \Blog\Model\DataObjects\Page;
use \Blog\Model\DataObjects\Person;
use \Blog\Model\DataObjects\Post;

use \Blog\Model\DataObjects\Lists\MediaList;
use \Blog\Model\DataObjects\Lists\Media\ApplicationList;
use \Blog\Model\DataObjects\Lists\Media\AudioList;
use \Blog\Model\DataObjects\Lists\Media\ImageList;
use \Blog\Model\DataObjects\Lists\Media\VideoList;
use \Blog\Model\DataObjects\Lists\ColumnList;
use \Blog\Model\DataObjects\Lists\EventList;
use \Blog\Model\DataObjects\Lists\GroupList;
use \Blog\Model\DataObjects\Lists\MotionList;
use \Blog\Model\DataObjects\Lists\PageList;
use \Blog\Model\DataObjects\Lists\PersonList;
use \Blog\Model\DataObjects\Lists\PostList;

class ControllerConfig {

	const REGISTERED_CONTROLLERS = [
		'DataObjectController' 	=> DataObjectController::class,
		'MediaController' 		=> MediaController::class
	];

	const REGISTERED_DATA_OBJECTS = [
		'Medium' 		=> Medium::class,
		'Application' 	=> Application::class,
		'Audio' 		=> Audio::class,
		'Image' 		=> Image::class,
		'Video' 		=> Video::class,
		'Column' 		=> Column::class,
		'Event' 		=> Event::class,
		'Group' 		=> Group::class,
		'Motion' 		=> Motion::class,
		'Page' 			=> Page::class,
		'Person' 		=> Person::class,
		'Post' 			=> Post::class,
		'media' 		=> Medium::class, // TEMP from here
		'applications' 	=> Application::class,
		'audios' 		=> Audio::class,
		'images' 		=> Image::class,
		'videos' 		=> Video::class,
		'columns' 		=> Column::class,
		'events' 		=> Event::class,
		'groups' 		=> Group::class,
		'motions' 		=> Motion::class,
		'pages' 		=> Page::class,
		'persons' 		=> Person::class,
		'posts' 		=> Post::class,
	];

	const DATA_OBJECT_LISTS = [
		Medium::class 		=> MediaList::class,
		Application::class 	=> ApplicationList::class,
		Audio::class 		=> AudioList::class,
		Image::class 		=> ImageList::class,
		Video::class 		=> VideoList::class,
		Column::class 		=> ColumnList::class,
		Event::class 		=> EventList::class,
		Group::class 		=> GroupList::class,
		Motion::class 		=> MotionList::class,
		Page::class 		=> PageList::class,
		Person::class 		=> PersonList::class,
		Post::class 		=> PostList::class
	];

	const DATA_OBJECT_CONTROLLERS = [
		Medium::class 		=> DataObjectController::class,
		Application::class 	=> DataObjectController::class,
		Audio::class 		=> DataObjectController::class,
		Image::class 		=> DataObjectController::class,
		Video::class 		=> DataObjectController::class,
		Column::class 		=> DataObjectController::class,
		Event::class 		=> DataObjectController::class,
		Group::class 		=> DataObjectController::class,
		Motion::class 		=> DataObjectController::class,
		Page::class 		=> DataObjectController::class,
		Person::class 		=> DataObjectController::class,
		Post::class 		=> DataObjectController::class
	];

	// TODO Alias tables

}
?>
