<?php
namespace Blog\Modules\Pages;
use Octopus\Core\Model\Attributes\StaticObjectAttribute;
use Octopus\Core\Model\Entity;
use Octopus\Modules\Identifiers\ID;
use Octopus\Modules\Identifiers\StringIdentifier;
use Octopus\Modules\MarkdownText\MarkdownText;
use Octopus\Modules\Standard\Model\Attributes\Strng;

class Page extends Entity {
	protected ID $id;
	protected StringIdentifier $longid;
	protected Strng $title;
	protected MarkdownText $content;

	const DB_TABLE = 'pages';


	protected static function define_attributes() : array {
		return [
			'id' => ID::define(),
			'longid' => StringIdentifier::define(is_editable:false),
			'title' => Strng::define(min:1, max:100),
			// 'content' => Strng::define()
			'content' => MarkdownText::define()
		];
	}


	protected const PRIMARY_IDENTIFIER = 'id';
}
?>
