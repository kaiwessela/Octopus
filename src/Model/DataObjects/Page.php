<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;

class Page extends DataObject {

#			NAME			TYPE	REQUIRED	PATTERN		DB NAME		DB VALUE
	public $title;		#	str		*			.{1,60}		=			=
	public $content;	#	str								=			=

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;

	const IGNORE_PULL_LIMIT = true;

	const FIELDS = [
		'title' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,60}'
		],
		'content' => [
			'type' => 'string'
		]
	];


	public function load($data) {
		$this->req('empty');

		$this->load_single($data[0]);
	}


	public function load_single($data) {
		$this->req('empty');

		$this->id = $data['page_id'];
		$this->longid = $data['page_longid'];
		$this->title = $data['page_title'];
		$this->content = $data['page_content'];

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->title = $this->title;
		$obj->content = $this->content;

		return $obj;
	}


	protected function db_export() {
		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content
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
