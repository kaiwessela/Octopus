<?php
namespace Octopus\Core\Model\Database;
use PDO;
use Exception;
use PDOStatement;
use Octopus\Core\Config;

# DatabaseAccess does:
#	- establish a connection to the database using credentials set in the config.
#	- reference this (and only this single one) connection to any entities that need to perform database operations.
#	- provide a simple interface to perform database operations (essentially a PDO wrapper).
#	- let the controller disable the database connection for all entities by a single function call.
#
# Every database request in Octopus should be executed using only this class. It works the same as using PDO the usual
# way, with being different only that this class takes over the creation of the PDOStatement or denies it if the
# DatabaseAccess has been disabled.
#
# Why does the database access need to be disabled at some point at all?
# Database requests are mostly used by the Entity and Relationship classes. Requests by these classes should
# only be performed BEFORE outputting results in templates began. Or formally, db requests should only be performed
# while the Endpoints's execute() function is running. Allowing templates to trigger requests themselves (and thus
# potentially alter the database) contradicts the idea of a consistent, well-ordered program flow and would be an
# uncontrollable risk.
# Therefore, the database access should be disabled centrally before any objects are being passed on to the templates.
# This is accomplished by calling the disable() function of this class. As there exists only one instance that is
# referenced to all objects needing it, calling disable() effects all these objects. Any request to open a PDO is then
# rejected by this classes’ prepare() function.
# Important: This is not an invincible safety feature. It is still possible for templates to create their own
# connections. This functionality only makes such code looking a little more ugly in order to try to prevent smart
# developers having stupid ideas.

class DatabaseAccess {
	private ?PDO $pdo; # the database connection object (--> documentation: php.net / PDO)
	private bool $disabled; # whether the connection is disabled. false by default


	function __construct() {
		$this->pdo = null;
		$this->disabled = false;
	}


	# Disable the connection when an instance of this class is being cloned.
	# Instances of DatabaseAccess must never be cloned because this would lever out the central disable mechanism.
	function __clone() {
		$this->pdo = null;
		$this->disabled = true;
		throw new Exception('DatabaseAccess must never be cloned. Use a reference instead.');
	}


	# Establish a connection to the database by creating a new PDO using access details read from the config.
	# If the connection has already been established, do nothing.
	# If the connection has been disabled, throw an exception.
	public function enable() : void {
		if($this->disabled === true){ # if the access has already been disabled, throw an error
			throw new Exception('Access to the database cannot be established because it has already been disabled.');
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


	# Create a new prepared statement (PDOStatement) and return it - basically a wrapper for PDO::prepare().
	# Call $this->enable() first. This also checks whether the connection has already been disabled.
	public function prepare(string $query, array $options = []) : PDOStatement|false {
		$this->enable(); # enable the database first
		return $this->pdo->prepare($query, $options); # execute the PDO::prepare() function
	}
}
