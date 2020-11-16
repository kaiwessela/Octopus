<?php
class Column extends DataObjectContainer {

#			NAME				TYPE		REQUIRED	PATTERN		DB NAME					DB VALUE
	public $name;			#	str			*			.{1,30}		=						=
	public $description;	#	str						.*			=						=
	public $posts;			#	arr[Post]							~postcolumns_post_id	[]->id

#	@inherited
#	public $id;
#	public $longid;
#
#	private $new;
#	private $empty;
#
#	private $relationlist;

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


	public function load($data) {
		$this->req('empty');

		$this->name = $data[0]['column_name'];
		$this->description = $data[0]['column_description'];

		$relations = [];
		foreach($data as $postdata){
			$post = new Post();
			$post->load($postdata);
			$this->posts[] = $post;

			$relation = new PostColumnRelation();
			$relation->load(&$this, &$post, $postdata);
			$relations[$relation->id] = $relation;
		}

		$this->relationlist->load($relations);

		$this->set_new(false);
		$this->set_empty(false);
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

}
?>
