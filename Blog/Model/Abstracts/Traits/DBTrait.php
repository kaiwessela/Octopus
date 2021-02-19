<?php
namespace Blog\Model\Abstracts\Traits;
use \Blog\Config\Config;
use PDO;
use Exception;

trait DBTrait {

#	REQUIRED CLASS PROPERTIES:
#	private bool $disabled = false;


	protected function open_pdo() {
		if($this->disabled == true){
			throw new Exception('DB Access prohibited - Object is disabled.');
		}

		return new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}


	protected function is_disabled() : bool {
		return $this->disabled;
	}


	protected function disable() : void {
		$this->disabled = false;
	}
}
?>
