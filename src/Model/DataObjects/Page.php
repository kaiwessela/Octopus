<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataTypes\MarkdownContent;

class Page extends DataObject {
	public string 			$title;
	public ?MarkdownContent $content;

#	@inherited
#	public string $id;
#	public string $longid;
#
#	public ?int $count;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;

	const IGNORE_PULL_LIMIT = true;

	const PROPERTIES = [
		'title' => '.{1,60}',
		'content' => MarkdownContent::class
	];


	public function load(array $data) : void {
		$this->req('empty');

		if(is_array($data[0]))){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['page_id'];
		$this->longid = $row['page_longid'];
		$this->title = $row['page_title'];

		$this->content = empty($row['page_content'])
			? null : new MarkdownContent($row['page_content']);

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
