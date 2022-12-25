<?php
namespace Octopus\Core\Model\Database;
use Exception;

# A Condition collects and resolves data to create an SQL WHERE clause from, which is used to determine which rows of
# a database table the request is being applied to.
# Conditions can be created from generally everywhere inside Octopus, but must be added to a Request to take effect.
#
# A Request can only have one condition, but multiple conditions can be linked by packing them into an AndOp or OrOp,
# which are themselves conditions that represent the MYSQL AND and OR operators (linking multiple conditions together).
#
# Upon Request->resolve(), the Request will resolve its condition and integrate the resolved clause (called query here)
# into its own query.
#
# Each condition resolves into an SQL clause (cached in $query). As this clause can include placeholders (i.e. because
# the condition compares the value of a column to another given value), these given values are cached in $values.
# Conditions may never ever (!!!) write these values into the query itself, as this might open a gateway for an SQL
# injection attack. Instead, it must use a placeholder and pass on the value separately (this is the principle of
# prepared statements). This placeholder should be "cond_" + an index number, and the value should be stored in $values
# as "cond_{index}" => value.
# The first index to use is passed on to the resolve() function via the $index variable. If the condition sets multiple
# values, it can simply ascend from $index as much as necessary. The return value of resolve() is the last index used.
# So if the passed on index is 2 and resolve() sets one value, resolve() returns 3. Or if it sets zero or two values,
# it returns 2 or 4 respectively. Simply return $index plus the amount of values set.
#
# This admittedly somewhat unaesthetic process can be avoided by developers by defining a simplified_resolve() method
# instead of a custom resolve() method. Then the default resolve() method is used, which is basically a wrapper that
# calls the custom simplified_resolve() method, counts the amount of inserted values and returns it.
# The simplified_resolve() method must only return the query, but all placeholders must be set using subsitute($value).
# This all at once inserts a placeholder into the string, writes the value into $values and counts up the index.

abstract class Condition {
	protected string $query; # Caches the condition statement.
	protected array $values; # Caches the condition values.
	
	private ?int $index_counter; # Index counter for substitute() in connection with simplified_resolve().


	function __construct() {
		$this->values = [];
		$this->index_counter = null;
	}


	# Compute the SQL statement, set the values and return the given $index plus the amount of values set.
	# By default, this wraps around simplified_resolve().
	public function resolve(int $index = 0) : int {
		$this->index_counter = $index;

		$this->query = $this->simplified_resolve();

		return $this->index_counter;
	}


	# Compute the SQL statement without needing to worry about the index. Returns the statement.
	# When simplified_resolve() is used, all values (and their placeholders) must be set using substitute().
	protected function simplified_resolve() : string {
		throw new Exception('Please define resolve() or simplified_resolve() before using this condition.');
	}


	# Set a value, count the index counter one up and return the placeholder for the value.
	# Must only be used in connection with simplified_resolve().
	final protected function substitute($value) : string {
		if(is_null($this->index_counter)){
			throw new Exception('Cannot use substitute() because the simplified resolve method is not being used.');
		}

		$placeholder = 'cond_'.$this->index_counter;

		$this->values[$placeholder] = $value;
		$this->index_counter++;
		
		return ':'.$placeholder;
	}


	# Return the statement.
	final public function get_query() : string {
		if(!isset($this->query)){
			$this->resolve();
		}

		return $this->query;
	}


	# Return the values.
	final public function get_values() : array {
		if(!isset($this->query)){
			$this->resolve();
		}

		return $this->values;
	}
}
?>
