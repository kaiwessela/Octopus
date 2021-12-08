<?php
namespace Blog\Model\Abstracts\Traits;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\EmptyResultException;

trait Paginatable {
	public ?int $count;


	public function pull_relations(?int $limit = null, ?int $offset = null /* maybe options */) : void {
		$this->require_not_empty();
		$this->require_not_new();
		$pdo = $this->open_pdo();

		$query = $this::PULL_OBJECTS_QUERY;
		$query .= ($limit) ? (($offset) ? " LIMIT $offset, $limit" : " LIMIT $limit") : null;

		$s = $pdo->prepare($query);
		if(!$s->execute(['id' => $this->id])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$this->load_relations($s->fetchAll());
		}
	}

	/*

	const PULL_OBJECTS_QUERY = <<<SQL
SELECT * FROM postcolumnrelations
LEFT JOIN posts ON post_id = postcolumnrelation_post_id
LEFT JOIN media ON medium_id = post_image_id
WHERE postcolumnrelation_column_id = :id
ORDER BY post_timestamp DESC
SQL; #---|

*/


	abstract public function load_relations(array $data) : void;


	public function count() : ?int {
		return $this->count;
	}


	abstract public function amount() : int;


	public function empty() : bool {
		return ($this->amount() == 0);
	}
}
?>
