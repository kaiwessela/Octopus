<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataTypes\MarkdownContent;

class Page extends DataObject {

#							NAME			TYPE	REQUIRED	PATTERN		DB NAME		DB VALUE
	public string 			$title;		#	str		*			.{1,60}		=			=
	public ?MarkdownContent $content;	#	str								=			=

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#	private $disabled;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'title' => '.{1,60}',
		'content' => MarkdownContent::class
	];


	public function load(array $data) : void {
		$this->req('empty');

		$this->load_single($data[0]);
	}


	public function load_single(array $data) : void {
		$this->req('empty');

		$this->id = $data['page_id'];
		$this->longid = $data['page_longid'];
		$this->title = $data['page_title'];

		$this->content = empty($data['page_content'])
			? null : new MarkdownContent($data['page_content']);

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'content' => (string) $this->content
		];

		if($this->is_new()){
			$values['longid'] = $this->longid;
		}

		return $values;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM pages
WHERE page_id = :id OR page_longid = :id
SQL; #---|

	const COUNT_QUERY = null;

	const INSERT_QUERY = <<<SQL
INSERT INTO pages (page_id, page_longid, page_title, page_content)
VALUES (:id, :longid, :title, :content)
SQL; #---|

	const UPDATE_QUERY = <<<SQL
UPDATE pages SET
	page_title = :title,
	page_content = :content
WHERE page_id = :id
SQL; #---|

	const DELETE_QUERY = <<<SQL
DELETE FROM pages
WHERE page_id = :id
SQL; #---|

}
?>
