<?php
class Column extends DataObject {

#			NAME				TYPE		REQUIRED	PATTERN		DB NAME		DB VALUE
	public $name;			#	str			*			.{1,30}		=			=
	public $description;	#	str						.*			=			=
	public $posts;			#	arr[Post]

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;
#
#	const IGNORE_PULL_LIMIT = false;

	const FIELDS = [
		'name' => [
			'type' => 'string',
			'required' => true,
			'pattern' => '.{1,30}'
		],
		'description' => [
			'type' => 'string'
		],
		'posts' => [
			'type' => 'relationlist'
		]
	];


	public function load($data, $block_recursion = false) {
		$this->req('empty');

		$this->name = $data[0]['column_name'];
		$this->description = $data[0]['column_description'];

		if(!$block_recursion){
			$relations = [];
			foreach($data as $postdata){
				$post = new Post();
				$post->load($postdata, true);
				$this->posts[] = $post;

				$relation = new PostColumnRelation();
				$relation->load(&$this, &$post, $postdata);
				$relations[$relation->id] = $relation;
			}

			$this->relationlist->load($relations);
		}

		$this->set_new(false);
		$this->set_empty(false);
	}


	public function export($block_recursion = false) {
		$obj = (object) [];

		$obj->name = $this->name;
		$obj->description = $this->description;

		if(!$block_recursion){
			$obj->posts = [];
			foreach($this->posts as $post){
				$obj->posts[] = $post->export(true);
			}
		}

		$obj->relations = $this->relationlist->export();

		return $obj;
	}


	private function db_export() {
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


	private function push_children() {
		return;
	}


	const PULL_QUERY = <<<SQL
SELECT * FROM columns
LEFT JOIN postcolumns ON postcolumn_column_id = column_id
LEFT JOIN posts ON post_id = postcolumn_post_id
LEFT JOIN images ON image_id = post_image_id
WHERE column_id = :identifier OR column_longid = :identifier
ORDER BY post_timestamp DESC
SQL; #---|


	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM postcolumns WHERE postcolumn_column_id = :id
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
