<?php
namespace Blog\Model\DataObjects;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\DataObjects\Lists\PostList;
use \Blog\Model\DataObjects\Relations\Lists\PostColumnRelationList;

class Column extends DataObject {
	public string 								$name;
	public ?string 								$description;
	public PostList|array|null 					$posts;
	public PostColumnRelationList|array|null 	$postrelations;

#	@inherited
#	public string $id;
#	public string $longid;
#
#	public ?int $count;
#
#	private bool $new;
#	private bool $empty;
#	private bool $disabled;
#
#	const IGNORE_PULL_LIMIT = false;

	const PROPERTIES = [
		'name' => '.{1,30}',
		'description' => null,
		'posts' => PostList::class,
		'postrelations' => PostColumnRelationList::class
	];


	public function load(array $data, bool $norecursion = false) : void {
		$this->req('empty');

		if(is_array($data[0])){
			$row = $data[0];
		} else {
			$row = $data;
		}

		$this->id = $row['column_id'];
		$this->longid = $row['column_longid'];
		$this->name = $row['column_name'];
		$this->description = $row['column_description'];

		if(!$norecursion){
			$this->postrelations = empty($row['postcolumnrelation_id']) ? null : new PostColumnRelationList();
			$this->postrelations?->load($data, $this);
			$this->posts = empty($this->postrelations?->relations) ? null : new PostList();
			$this->posts?->load_from_relationlist($this->postrelations);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	protected function db_export() : array {
		$export = [
			'id' => $this->id,
			'name' => $this->name,
			'description' => $this->description
		];

		if($this->is_new()){
			$export['longid'] = $this->longid;
		}

		return $export;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM columns
LEFT JOIN postcolumnrelations ON postcolumnrelation_column_id = column_id
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN images ON image_id = post_image_id
WHERE column_id = :id OR column_longid = :id
ORDER BY post_timestamp DESC
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM postcolumnrelations WHERE postcolumnrelation_column_id = :id
SQL; #---|


	const INSERT_QUERY = <<<SQL
INSERT INTO columns
	(column_id, column_longid, column_name, column_description)
VALUES
	(:id, :longid, :name, :description)
SQL; #---|


	const UPDATE_QUERY = <<<SQL
UPDATE columns SET
	column_name = :name,
	column_description = :description
WHERE column_id = :id
SQL; #---|


	const DELETE_QUERY = <<<SQL
DELETE FROM columns WHERE column_id = :id
SQL; #---|

}
?>
