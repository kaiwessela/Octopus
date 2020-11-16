<?php
namespace Blog\Model;
use Blog\Config\Config;
use PDO;

trait DataObjectTrait {
	public $new;
	public $empty;


	function __construct() {
		$this->new = false;
		$this->empty = true;
	}

	protected static function open_pdo() {
		return new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}

	public function is_new() {
		return $this->new;
	}

	public function is_empty() {
		return $this->empty;
	}

	protected function set_new(bool $state = true) {
		$this->new = $state;
	}

	protected function set_empty(bool $state = true) {
		$this->empty = $state;
	}

	protected function req(string $state){
		if($state == 'new'){
			if(!$this->is_new()){
				throw new WrongObjectStateException('new');
			}
		} else if($state == 'empty'){
			if($this->is_new()){
				throw new WrongObjectStateException('not new');
			}
		} else if($state == 'not new'){
			if(!$this->is_empty()){
				throw new WrongObjectStateException('empty');
			}
		} else if($state == 'not empty'){
			if($this->is_empty()){
				throw new WrongObjectStateException('not empty');
			}
		} else {
			throw new InvalidArgumentException('$state must be one of those: new, empty, not new, not empty');
		}
	}
}
?>
