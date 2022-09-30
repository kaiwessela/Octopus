<?php
namespace Octopus\Modules\Pages;
use \Octopus\Core\Model\Entity;
use \Octopus\Core\Model\Attributes\IDAttribute;
use \Octopus\Core\Model\Attributes\IdentifierAttribute;
use \Octopus\Core\Model\Attributes\StringAttribute;
use \Octopus\Core\Model\Attributes\StaticObjectAttribute;
use \Octopus\Modules\Pages\PageList;
use \Octopus\Modules\StaticObjects\MarkdownText;

class Page extends Entity {
	protected StringAttribute 		$title;
	protected StaticObjectAttribute $content;

	protected static array $attributes;

	const DB_TABLE = 'pages';
	const DB_PREFIX = 'page';

	const LIST_CLASS = PageList::class;

	protected static function define_attributes() : array {
		return [
			'id' => IDAttribute::define(),
			'longid' => IdentifierAttribute::define(editable:false),
			'title' => StringAttribute::define(min:1, max:100),
			'content' => StaticObjectAttribute::define(class:MarkdownText::class)
		];
	}
}
?>
