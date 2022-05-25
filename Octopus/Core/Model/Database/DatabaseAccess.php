<?php
namespace Octopus\Core\Model\Database;
use \Octopus\Core\Config;
use PDO;
use PDOStatement;
use Exception;

# DatabaseAccess does:
#	- establish and provide a connection to the database.
#	- disable and deny the connection for entitys (et al) that are frozen (--> Entity::freeze()).
#	- (optionally) keep track of whether entitys and relations are in sync with the database or have been altered.
#
# This class provides methods to both establish and deny a database connection.
# Every database request in Octopus should be executed using this class. It works the same as using PDO the usual way,
# with being different only that this class takes over the creation of the PDO or denies it if the entity is frozen.
# What does it mean if an entity is frozen?
# Background: Database requests are mostly used by the Entity and Relationship classes. Requests by these classes should
# only be performed BEFORE outputting results in templates began. Or formally, db requests should only be performed in
# consequence of the Endpoints's execute() function. Allowing templates to trigger requests themselves (and thus
# potentially alter the database) contradicts the idea of a consistent, well-ordered program flow and would be an
# uncontrollable risk.
# Therefore, entitys and relationships are "frozen" before being passed on to the templates. This is accomplished by
# calling the freeze() function which disables the database access. Any request to open a PDO is then rejected by this
# classes prepare() function.
# Important: This is not a hard security feature. It is still possible for templates to create their own connection.
# This functionality only makes such code looking a little more ugly in order to try to prevent smart developers having
# stupid ideas.

class DatabaseAccess {
	private ?PDO $pdo; # the database connection object (--> documentation: php.net / PDO)
	private bool $disabled; # whether the connection is disabled. false by default


	function __construct() {
		$this->pdo = null;
		$this->disabled = false;
	}


	function __clone() {
		$this->pdo = null;
		$this->disabled = true;
	}


	# Establish a connection to the database (if it has not been disabled already) by creating a new PDO using
	# access details read from the config
	public function enable() : void {
		if($this->disabled === true){ # if the access has already been disabled, throw an error
			throw new Exception('DB access prohibited because object is disabled.');
		}

		if(!is_null($this->pdo)){ # if the connection already exists, do nothing
			return;
		}

		# create a new PDO object
		$this->pdo = new PDO(
			'mysql:host='.Config::get('Database.host').';dbname='.Config::get('Database.name'),
			Config::get('Database.user'),
			Config::get('Database.pass'),
			[PDO::ATTR_PERSISTENT => true] # use a persistent PDO (performance reasons; --> see php.net / PDO)
		);
	}


	# Disable the database connection
	public function disable() : void {
		$this->disabled = true;
		unset($this->pdo); # close the connection
	}


	# Create a new prepared statement and return it - basically a wrapper for PDO::prepare()
	public function prepare(string $query, array $options = []) : PDOStatement|false {
		$this->enable(); # enable the database first
		return $this->pdo->prepare($query, $options); # execute the PDO::prepare() function
	}
}
