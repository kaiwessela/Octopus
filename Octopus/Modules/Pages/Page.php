<?php
namespace Octopus\Modules\Pages;
use \Octopus\Core\Model\Entity;
use \Octopus\Modules\Pages\PageList;
use \Octopus\Modules\StaticObjects\MarkdownText;

class Page extends Entity {
	# inherited from Entity:
	# protected readonly string $id;
	# protected ?string $longid;

	protected ?string 		$title;
	protected ?MarkdownText $content;

	protected static array $attributes;

	const DB_TABLE = 'pages';
	const DB_PREFIX = 'page';

	const LIST_CLASS = PageList::class;

	const ATTRIBUTES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		'content' => MarkdownText::class
	];
}
?>
