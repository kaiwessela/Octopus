<?php # Page.php 2021-10-04 beta
namespace Blog\Modules\Pages;
use \Blog\Core\Model\DataObject;
use \Blog\Modules\DataTypes\MarkdownContent;

class Page extends DataObject {
	# inherited from DataObject:
	# protected string $id;
	# protected string $longid;

	protected string 			$title;
	protected ?MarkdownContent $content;


	const PROPERTIES = [
		'id' => 'id',
		'longid' => 'longid',
		'title' => '.{1,100}',
		'content' => MarkdownContent::class
	];


	const DB_PREFIX = 'page';


	const PULL_QUERY = 'SELECT * FROM pages WHERE page_id = :id OR page_longid = :id';

	const INSERT_QUERY = <<<SQL
INSERT INTO pages (page_id, page_longid, page_title, page_content)
VALUES (:id, :longid, :title, :content)
SQL; #---|

	const UPDATE_QUERY = 'UPDATE pages SET page_title = :title, page_content = :content WHERE page_id = :id';

	const DELETE_QUERY = 'DELETE FROM pages WHERE page_id = :id';

}
?>
