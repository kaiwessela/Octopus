<?php
namespace Blog\Model;
use \Blog\Model\Exceptions\DatabaseException;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\IdentifierCollisionException;
use \Blog\Model\Exceptions\IdentifierMismatchException;
use \Blog\Config\Config;
use PDO;
use Exception;

abstract class DatabaseObject {
	public $id;		# String(8)
	public $longid;	# String(9-60)

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
		if($id != $this->id){
			throw new IdentifierMismatchException('id', $id, $this);
		}

		if($longid != $this->longid){
			throw new IdentifierMismatchException('longid', $longid, $this);
		}
	}

	protected function import_longid($longid) {
		$pattern = '^[a-z0-9-]{9,128}$';

		if(empty($longid)){
			throw new MissingValueException('longid', $pattern);
		}

		if(!preg_match("/$pattern/", $longid)){
			throw new IllegalValueException('longid', $longid, $pattern);
		}

		try {
			$existing = new $this;
			$existing->pull($longid);
			$found = true;
		} catch(EmptyResultException $e){
			$found = false;
		}

		if($found){
			throw new IdentifierCollisionException($longid, $existing);
		} else {
			$this->longid = $longid;
		}
	}

	protected function import_standardized($data, $config, &$errorlist) {
		foreach($config as $field => $settings){
			$required = $settings['required'];
			$pattern = $settings['pattern'];

			if(empty($data[$field]) && !$required){
				$this->$field = null;
			} else if(empty($data[$field]) && $required){
				$errorlist->push(new MissingValueException($field, $pattern));
			} else if(!preg_match("/$pattern/", $data[$field])){
				$errorlist->push(new IllegalValueException($field, $data[$field], $pattern));
			} else {
				$this->$field = $data[$field];
			}
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

	public function is_empty() {
		return $this->empty;
	}

	public function is_new() {
		return $this->new;
	}
}
?>
