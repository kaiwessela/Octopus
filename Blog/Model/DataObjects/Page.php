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
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;
#
#	const PAGINATABLE = false;

	const PROPERTIES = [
		'title' => '.{1,60}',
		'content' => MarkdownContent::class
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->require_empty();

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['page_id'];
		$this->longid = $row['page_longid'];
		$this->title = $row['page_title'];

		$this->content = empty($row['page_content'])
			? null : new MarkdownContent($row['page_content']);

		$this->set_not_new();
		$this->set_not_empty();
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
