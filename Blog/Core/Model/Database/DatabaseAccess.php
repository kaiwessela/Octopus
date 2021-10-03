<?php
namespace Blog\Model\Abstracts\Traits;


# DatabaseAccess – A PDO wrapper for DataObjects
# This class provides methods to both establish and deny a database connection.
# Every database request in Octopus should be executed using this class. It works the same as using
# PDO the usual way, with being different only that this class takes over the creation of the PDO
# or denies it if the class is frozen.
# What does it mean if a class is frozen?
# Background: Database requests are mostly used by the DataObject classes. Requests by this classes
# should only be performed BEFORE outputting results over templates began. Or more precise,
# db requests should only be performed in consequence of the Endpoints's execute() function.
# Allowing templates to trigger requests (and thus potentially alter the database) contradicts the
# idea of a consistent, well-ordered program flow and would be an uncontrollable risk.
# Therefore, DataObjects and similar classes are "frozen" before being passed on to the templates.
# This is accomplished by calling the freeze() function which disables the database access.
# Any request to open a PDO is then rejected by this classes prepare() function.
# Important: This is not a hard security feature. It is still possible for templates to create
# their own PDO. This functionality only makes such code looking a little more ugly in order to try
# to prevent smart developers getting stupid ideas.

# Another (optional) feature of this class is to keep track of the sync state of DataObjects. There
# are three different states that must be updated by the DataObjects:
#	- local – object is not yet stored in the database, most oftenly because it was just created
#	- synced – the state of the object is the same as in the database, i.e. because it was just pulled
#	- altered – the object was altered locally and must be pushed to the database in order to save the changes

class DatabaseAccess {
	private ?PDO $pdo;
	private bool $disabled;
	private ?int $sync_state;


	function __construct() {
		$this->pdo = null;
		$this->disabled = false;
		$this->sync_state = null;
	}

	public function enable() {
		if($this->disabled === true){
			throw new Exception('DatabaseAccess » Access prohibited because object is already disabled.');
		}

		if(!is_null($this->pdo)){
			return;
		}

		$this->pdo = new PDO(
			'mysql:host=' . Config::DB_HOST . ';dbname=' . Config::DB_NAME,
			Config::DB_USER,
			Config::DB_PASSWORD,
			[PDO::ATTR_PERSISTENT => true]
		);
	}

	public function disable() {
		$this->disabled = true;
		$this->pdo = null;
	}

	public function prepare(string $query, array $options = []) : PDOStatement|false {
		$this->enable();
		return $this->pdo->prepare($query, $options);
	}

	public function is_local() : bool {
		return ($this->sync_state === 1);
	}

	public function is_synced() : bool {
		return ($this->sync_state === 2);
	}

	public function is_altered() : bool {
		return ($this->sync_state === 3);
	}

	public function set_local() : void {
		$this->sync_state = 1;
	}

	public function set_synced() : void {
		$this->sync_state = 2;
	}

	public function set_altered(bool $force = false) : void {
		// force: normally, if the object gets altered directly after being local, it stays local.
		// with force, it is changed nevertheless to altered

		if($this->is_local() && !$force){
			return null;
		}

		$this->sync_state = 3;
	}
}
