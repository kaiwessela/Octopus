<?php
namespace Blog\Model;
use \Blog\Config\Config;
use \Blog\Model\Exceptions\WrongObjectStateException;
use \Blog\Model\Exceptions\EmptyResultException;
use \Blog\Model\Exceptions\InputFailedException;
use \Blog\Model\Exceptions\MissingValueException;
use \Blog\Model\Exceptions\IllegalValueException;
use \Blog\Model\Exceptions\IdentifierCollisionException;
use \Blog\Model\Exceptions\IdentifierMismatchException;
use InvalidArgumentException;
use PDO;

trait DataObjectTrait {

	protected static function open_pdo() {
		return new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}

	protected function import_id_and_longid($id, $longid) {
		$errors = new InputFailedException();

		if($this->is_new()){
			$pattern = '^[a-z0-9-]{9,128}$';

			if(empty($longid)){
				$errors->push(new MissingValueException('longid', $pattern));
			}

			if(!preg_match("/$pattern/", $longid)){
				$errors->push(new IllegalValueException('longid', $longid, $pattern));
			}

			try {
				$existing = new $this;
				$existing->pull($longid);
				$found = true;
			} catch(EmptyResultException $e){
				$found = false;
			}

			if($found){
				$errors->push(new IdentifierCollisionException($longid, $existing));
			} else {
				$this->longid = $longid;
			}
		} else {
			if($id != $this->id){
				$errors->push(new IdentifierMismatchException('id', $id, $this));
			}

			if($longid != $this->longid){
				$errors->push(new IdentifierMismatchException('longid', $longid, $this));
			}
		}

		if(!$errors->is_empty()){
			throw $errors;
		}
	}

	protected function generate_id() {
#	@action:
#	  - generate a new id
#	  - assign the newly generated id to this object

		$this->id = bin2hex(random_bytes(4));
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
