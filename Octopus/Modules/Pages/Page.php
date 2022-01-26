<?php
namespace Octopus\Modules\Pages;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\StaticObjects\MarkdownText;

class Page extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 		$title;
	protected ?MarkdownText $content;

	const DB_TABLE = 'pages';
	const DB_PREFIX = 'page';

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		'content' => MarkdownText::class
	];
}
?>
