<?php
namespace Blog\Model\Abstracts\Traits;
use \Blog\Model\Abstracts\DataObject;
use \Blog\Model\Exceptions\DatabaseException;

trait Paginatable {
	public ?int $count;


	public function count() : int {
		if(empty($this->count)){
			if($this instanceof DataObject){
				$this->require_not_empty();

				$values = ['id' => $this->id];
			} else {
				$values = [];
			}

			$pdo = $this->open_pdo();

			$s = $pdo->prepare($this::COUNT_QUERY);
			if(!$s->execute($values)){
				throw new DatabaseException($s);
			} else {
				$this->count = (int) $s->fetch()[0];
			}
		}

		return $this->count;
	}
}
?>
