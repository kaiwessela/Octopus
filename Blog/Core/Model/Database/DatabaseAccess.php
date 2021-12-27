<?php
namespace Octopus\Core\Model\Database;
use \Octopus\Config\Config; // TODO check this
use PDO;
use PDOStatement;

# DatabaseAccess is a PDO wrapper for DataObjects.
# This class provides methods to both establish and deny a database connection.
# Every database request in Octopus should be executed using this class. It works the same as using
# PDO the usual way, with being different only that this class takes over the creation of the PDO
# or denies it if the class is frozen.
# What does it mean if a class is frozen?
# Background: Database requests are mostly used by the DataObject classes. Requests by this classes
# should only be performed BEFORE outputting results in templates began. Or more precise,
# db requests should only be performed in consequence of the Endpoints's execute() function.
# Allowing templates to trigger requests (and thus potentially alter the database) contradicts the
# idea of a consistent, well-ordered program flow and would be an uncontrollable risk.
# Therefore, DataObjects and similar classes are "frozen" before being passed on to the templates.
# This is accomplished by calling the freeze() function which disables the database access.
# Any request to open a PDO is then rejected by this classes prepare() function.
# Important: This is not a hard security feature. It is still possible for templates to create
# their own connection. This functionality only makes such code looking a little more ugly in order
# to try to prevent smart developers having stupid ideas.

# Another (optional) feature of this class is to keep track of the sync state of DataObjects. There
# are three different states that must be updated by the DataObjects:
#	- local – object is not yet stored in the database, most oftenly because it was just created
#	- synced – the state of the object is the same as in the database, i.e. because it was just pulled
#	- altered – the object was altered locally and must be pushed to the database in order to save the changes

class DatabaseAccess {
	private ?PDO $pdo; # the database connection object
	private bool $disabled; # whether the connection is disabled. false by default
	private ?int $sync_state; # the sync state: 1 - local; 2 - synced; 3 - altered; undefined (null) by default


	function __construct() { # initialize the base values
		$this->pdo = null;
		$this->disabled = false;
		$this->sync_state = null;
	}

	# Open a connection to the database (if it has not been disabled already) by creating a new PDO using
	# access details configured in the config
	public function enable() : void {
		if($this->disabled === true){ # if the access is already disabled, throw an error
			throw new Exception('DB access prohibited because object is already disabled.');
		}

		if(!is_null($this->pdo)){ # if the connection is already enabled, just return
			return;
		}

		# create a new PDO object
		$this->pdo = new PDO(
			'mysql:host='.Config::get('Database.host').';dbname='.Config::get('Database.name'),
			Config::get('Database.user'),
			Config::get('Database.pass'),
			[PDO::ATTR_PERSISTENT => true] # use a persistent PDO (performance reasons; -> see PHP documentation/PDO)
		);
	}

	# Disable the database connection
	public function disable() : void {
		$this->disabled = true;
		unset($this->pdo); # close the PDO
	}

	# create a new prepared statement and return it - basically a wrapper for PDO::prepare()
	public function prepare(string $query, array $options = []) : PDOStatement|false {
		$this->enable(); # enable the database first
		return $this->pdo->prepare($query, $options); # execute the PDO::prepare() function
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
		# force: normally, if a local object is altered, it stays local.
		# with force, it is changed to altered nevertheless.

		if($this->is_local() && !$force){
			return;
		}

		$this->sync_state = 3;
	}
}
