<?php
namespace Blog\Backend\Models;
use \Blog\Backend\Model;
use \Blog\Backend\Exceptions\WrongObjectStateException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\EmptyResultException;
use \Blog\Backend\Exceptions\InvalidInputException;
use InvalidArgumentException;

class Page extends Model {
	public $title;
	public $content;

	/* @inherited
	public $id;
	public $longid;

	private $new;
	private $empty;
	*/


	function __construct() {
		parent::__construct();
	}

	public function pull($identifier) {
		$pdo = self::open_pdo();

		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$query = 'SELECT * FROM pages WHERE page_id = :id OR page_longid = :id';
		$values = ['id' => $identifier];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else if($s->rowCount() != 1){
			throw new EmptyResultException($query, $values);
		} else {
			$this->load($s->fetch());
		}
	}

	public static function pull_all($limit = null, $offset = null) {
		$pdo = self::open_pdo();

		$query = 'SELECT * FROM pages ORDER BY page_longid';

		if($limit != null){
			if(!is_int($limit)){
				throw new InvalidArgumentException('Invalid argument: limit must be an integer.');
			}

			if($offset != null){
				if(!is_int($offset)){
					throw new InvalidArgumentException('Invalid argument: offset must be an integer.');
				}

				$query .= " LIMIT $offset, $limit";
			} else {
				$query .= " LIMIT $limit";
			}
		}

		$s = $pdo->prepare($query);

		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else if($s->rowCount() == 0){
			throw new EmptyResultException($query);
		} else {
			$res = [];
			while($r = $s->fetch()){
				$obj = new Page();
				$obj->load($r);
				$res[] = $obj;
			}
			return $res;
		}
	}

	public function load($data) {
		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$this->id = $data['page_id'];
		$this->longid = $data['page_longid'];
		$this->title = $data['page_title'];
		$this->content = $data['page_content'];

		$this->empty = false;
		$this->new = false;
	}

	public static function count() {
		$pdo = self::open_pdo();

		$query = 'SELECT COUNT(*) FROM posts';

		$s = $pdo->prepare($query);
		if(!$s->execute([])){
			throw new DatabaseException($s);
		} else {
			return (int) $s->fetch()[0];
		}
	}

	public function push() {
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		$values = [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content
		];

		if($this->is_new()){
			$query = 'INSERT INTO pages (page_id, page_longid, page_title, page_content)
				VALUES (:id, :longid, :title, :content)';

			$values['longid'] = $this->longid;
		} else {
			$query = 'UPDATE pages SET page_title = :title, page_content = :content
				WHERE page_id = :id';
		}

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = false;
		}
	}

	public function import($data) {
		if($this->is_new()){
			$this->import_longid($data['longid']);
		} else {
			$this->import_check_id_and_longid($data['id'], $data['longid']);
		}

		$this->import_title($data['title']);
		$this->import_content($data['content']);

		$this->empty = false;
	}

	public function delete() {
		$pdo = self::open_pdo();

		if($this->is_empty()){
			throw new WrongObjectStateException('not empty');
		}

		if($this->is_new()){
			throw new WrongObjectStateException('not new');
		}

		$query = 'DELETE FROM pages WHERE page_id = :id';
		$values = ['id' => $this->id];

		$s = $pdo->prepare($query);
		if(!$s->execute($values)){
			throw new DatabaseException($s);
		} else {
			$this->new = true;
		}
	}

	public function export() {
		if($this->is_empty()){
			return null;
		}

		$obj = (object) [];
		$obj->id = $this->id;
		$obj->longid = $this->longid;
		$obj->title = $this->title;
		$obj->content = $this->content;

		return $obj;
	}

	private function import_title($title) {
		if(!isset($title)){
			throw new InvalidInputException('title', '.{1,60}');
		} else if(!preg_match('/^.{1,60}$/', $title)){
			throw new InvalidInputException('title', '.{1,60}', $title);
		} else {
			$this->title = $title;
		}
	}

	private function import_content($content) {
		$this->content = $content;
	}
}
?>
