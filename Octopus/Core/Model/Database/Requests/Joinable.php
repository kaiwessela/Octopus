<?php
namespace Octopus\Core\Model\Database\Requests;
use Exception;
use Octopus\Core\Model\Attribute;
use Octopus\Core\Model\Database\OrderClause;
use Octopus\Core\Model\Database\Requests\JoinRequest;
use Octopus\Core\Model\Database\Requests\SelectRequest;

# The Joinable trait defines functions that are used by all Requests that can join other requests to it, which are
# SelectRequest and JoinRequest.

# Convoluted Requests:
# A request is convoluted exactly then when it is possible that results
# for an object can be spread across multiple rows. (the rule "one row per object" no longer applies).
# If one of the request's joins is convoluted, the request itself is too.
#
# This happens when the request contains reverse joins. In practice, this can happen when the object to be selected or
# joined contains a RelationshipAttribute.
#
# Convolution becomes problematic for counting and limiting, because simply counting the rows does no longer work.
# therefore, a two-step process with a subquery has to be used:
# first, the complete query is performed (including joins, conditions, order, limits), but it is performed as a
# distinct select with only the attributes of the main object (which is the object of the SelectRequest).
# what happens is that the request is performed normally, but before counting/limiting, all join columnsare stripped and
# duplicate rows are stripped too (due to the DISTINCT), so counting/limiting works again. to get back the joins, the
# result of the subquery is then used as the table for the main request and all joins, conditions and orders are applied
# again.

# Order:
# Order directives are stored in the Request of the object that contains the attribute to sort the results by,
# using the following format for $order: $significance => [Attribute $by, string $direction], ...
#
# When resolving the main SelectRequest, the order directives are collected and chained together in a quite complicated
# way, following these steps:
# 1. For the SelectRequest and each reverse join request, merge the own order chain and the chains of all forward joins
#	below it.
# 2. sort these chains by the significance of their directives and reindex them to create an uninterrupted list.
# 3. append the default order directive of the SelectRequest resp. the reverse join requests to each respective chain.
#	the default order directive is the object's main identifier in ascending order.
# 4. glue all chains together, beginning with the SelectRequest and descending by breadth-first search.
#
# This complicated rigmarole is necessary to ensure that lower-order order directives do not disarrange the rows of the
# higher-order object, which would render the result unparseable.


trait Joinable {

	# Add a JoinRequest to join another object to this request's object.
	final public function add_join(JoinRequest $request) : void { // IDEA rename join
		$this->joins[$request->get_identifier()] = $request;
	}


	# Add an attribute/a column to sort the result rows by.
	# $identifier can be either the name of an attribute included in this request, or an array, with the first item
	# being the identifier of a join included in this request (which is it's foreign attribute name).
	# $sequence can be either ASC or DESC, for ascending or descending order. See OrderClause also.
	# $significance determines the order of the order clauses, with 0 being the first (and thus most significant).
	final public function order_by(string|array $identifier, string $sequence, int $significance) : void {
		if(is_array($identifier)){ # the identifier points to a join, so recursively pass on the call to it
			$attribute = array_shift($identifier); # both removes the first element of the array and returns it

			if(!isset($this->joins[$attribute])){ # check that the specified join exists
				throw new Exception("Unknown join «{$attribute}» in order clause #{$significance}.");
			}

			# recursively pass on the data to the the JoinRequest's order_by method
			# mind that $identifier had the first element removed by using array_shift
			$this->joins[$attribute]->order_by($identifier, $sequence, $significance);
		} else { # the identifier points to an attribute included in this request
			if(!isset($this->attributes[$identifier])){ # check that the specified attribute exists
				throw new Exception("Unknown attribute «{$attribute}» in order clause #{$significance}.");
			}

			# create a new OrderClause from the data and store it in $this->order, with the significance being the key
			# note that this may override an existing order clause with the same significance
			$this->order[$significance] = new OrderClause($this->attributes[$identifier], $sequence, $significance);
		}
	}


	# Return an array of the attributes of this request and of all its joins.
	final public function collect_attributes() : array {
		$attributes = $this->attributes;

		foreach($this->joins as $join){
			$attributes = array_merge($attributes, $join->collect_attributes());
		}

		return $attributes;
	}


	# Return an array of the order clauses of this request and all its joins, ordered the following way:
	# 1. each expanding request sets a fallback order clause with the lowest possible significance.
	# 2. each expanding request collects its own order clauses and those of all its attaching (forward) joins
	# 3. for each expanding request, all these collected order clauses are being sorted by their given significance
	# 4. these arrays are then just linked together, with the main request's clauses appearing before the join's clauses 
	final public function collect_order_clauses() : array {
		if($this->is_expanding()){
			# (1) set a fallback order clause for this request. PHP_INT_MAX is the least possible significance
			$this->order_by($this->object->get_primary_identifier_name(), 'ASC', PHP_INT_MAX);
			# (2, 3) collect the order clauses of this request and all attaching joins below it, sorted by significance
			$order_clauses = $this->collect_own_and_attaching_order_clauses();
		} else {
			$order_clauses = []; # return an empty array, as attaching joins' clauses have already been collected
		}

		# (4) link together all expanding joins' order clause arrays
		foreach($this->joins as $join){
			# note: calling $collect_order_clauses also on attaching joins is necessary because they themselves might
			# contain expanding joins which need to be collected. if not, this is not a problem as then they will only
			# return an empty array
			$order_clauses = array_merge($order_clauses, $join->collect_order_clauses());
		}

		return $order_clauses;
	}


	# Helper for collect_order_clauses(). Fulfills steps 2 and 3 of the description above.
	final public function collect_own_and_attaching_order_clauses() : array {
		$order_clauses = $this->order; # collect this request's order clauses

		foreach($this->joins as $join){ # collect the order clauses of all its forward joins
			if($join->is_forward_join()){
				# the union operator ensures that the resulting order clauses are ordered by their index, which equals
				# their significance
				$order_clauses += $join->collect_own_and_attaching_order_clauses();
			}
		}
		
		// ksort($order_clauses); // probably not necessary

		return array_values($order_clauses);
	}


	# Resolve all joins and return their adjoined queries.
	final protected function resolve_joins() : string {
		$query = '';

		foreach($this->joins as $join){
			$query .= $join->get_query();
		}

		return $query;
	}


	final public function is_attaching() : bool {
		return !($this instanceof SelectRequest) && $this->is_forward_join(); // TEMP
	}


	final public function is_expanding() : bool {
		return ($this instanceof SelectRequest) || $this->is_reverse_join(); // TEMP
	}

	
	# Return whether the request is convoluted. See explaination above for what exactly that means.
	final public function is_convoluted() : bool {
		$result = false;

		foreach($this->joins as $join){
			$result |= ($join->is_convoluted() || $join->is_reverse_join());
		}

		return $result;
	}
}
?>
