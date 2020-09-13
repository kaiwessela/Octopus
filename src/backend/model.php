<?php
namespace Blog\Backend;
use \Blog\Backend\Exceptions\InvalidInputException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Backend\Exceptions\WrongObjectStateException;
use \Blog\Config\Config;
use PDO;
use Exception;

abstract class Model {
	public $id;
	public $longid;

	protected $new;
	protected $empty;


	abstract public static function count();
	abstract public static function pull_all($limit, $offset);
	abstract public function pull($identifier);
	abstract public function push();
	abstract public function load($data);
	abstract public function import($data);
	abstract public function export();
	abstract public function delete();

	function __construct() {
		$this->new = false;
		$this->empty = true;
	}

	public function generate() {
		if(!$this->is_empty()){
			throw new WrongObjectStateException('empty');
		}

		$this->generate_id();

		$this->new = true;
		$this->empty = false;
	}

	protected function import_check_id_and_longid($id, $longid) {
		if($id != $this->id || $longid != $this->longid){
			throw new InvalidInputException('id/longid', 'original id and longid', $data['id'] . ' ' . $data['longid']);
		}
	}

	protected function import_longid($longid) {
		if(!isset($longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}');
		}

		if(!preg_match('/^[a-z0-9-]{9,128}$/', $longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}', $longid);
		}

		try {
			$test = $this->pull($longid);
		} catch(Exception $e){ // TODO rewrite this
			if($e instanceof DatabaseException){
				throw $e;
			}

			$not_found = true;
		}

		if($not_found){
			$this->longid = $longid;
		} else {
			throw new InvalidInputException('longid', ';already-exists', $longid); // TODO to special exception
		}
	}

	protected static function open_pdo() {
		return new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}

	protected function generate_id() {
		$this->id = bin2hex(random_bytes(4));
	}


	protected function is_empty() {
		return $this->empty;
	}

	protected function is_new() {
		return $this->new;
	}
}
?>
