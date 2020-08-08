<?php
namespace \Blog\Backend;
use \Blog\Backend\Exceptions\InvalidInputException;
use \Blog\Backend\Exceptions\DatabaseException;
use \Blog\Config\Config;
use PDO;
use Exception;

interface Model {
	public $id;
	public $longid;

	private $pdo;
	private $new;
	private $empty;


	public function generate();
	public function pull();
	public function push();
	public function load();
	public function import();
}

trait Model {
	public function import_check_id_and_longid($id, $longid) {
		if($id != $this->id || $longid != $this->longid){
			throw new InvalidInputException('id/longid', 'original id and longid', $data['id'] . ' ' . $data['longid']);
		}
	}

	public function import_longid($longid) {
		if(!isset($longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}');
		}

		if(!preg_match('/^[a-z0-9-]{9,128}$/', $longid)){
			throw new InvalidInputException('longid', '[a-z0-9-]{9,128}', $longid);
		}

		try {
			$test = $this->pull_by_longid($longid);
		} catch(Exception $e){
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

	public function generate_id() {
		$this->id = bin2hex(random_bytes(4));
	}

	public static function open_pdo() {
		return new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}
}
?>
