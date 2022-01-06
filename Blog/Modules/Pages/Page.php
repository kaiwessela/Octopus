<?php
namespace Octopus\Modules\Pages;
use \Octopus\Core\Model\DataObject;
use \Octopus\Modules\DataTypes\MarkdownContent;

class Page extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 			$title;
	protected ?MarkdownContent 	$content;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		//1'content' => MarkdownContent::class
	];

	const DB_TABLE = 'pages';
	const DB_PREFIX = 'page';
}
?>
