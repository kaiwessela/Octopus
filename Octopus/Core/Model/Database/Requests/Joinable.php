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
	final public function add_join(JoinRequest $request) : void {
		$this->joins[] = $request;
	}


	# Add an attribute/a column to sort the result rows by.
	# $direction can be ASC (ascending) or DESC (descending).
	# $significance determines the order of the columns the result is sorted by. lower significance means that the
	# resulting rows are sorted by this column earlier. in practice, the significance is the index of the attribute in
	# the original order chain (set on the initial pull() function).
	final public function add_order(Attribute $by, string $direction, int $significance) : void {
		if(!array_key_exists($by->get_prefixed_db_column(), $this->attributes)){
			throw new Exception('The column to order the result by is not included in the column list.');
		}

		if(!($direction === 'ASC' || $direction === 'DESC')){
			throw new Exception('Invalid order direction.');
		}

		if($significance < 0){
			throw new Exception('Invalid order significance (must be greater than 0).');
		}

		$this->order[$significance] = [$by, $direction];
	}


	# Return an array of the attributes of this request and of all its joins.
	final public function collect_attributes() : array {
		$attributes = $this->attributes;

		foreach($this->joins as $join){
			$attributes = [...$attributes, ...$join->collect_attributes()];
		}

		return $attributes;
	}


	# Return an array (chain) of the order directives of this request and all of its forward joins (Step 1).
	final public function collect_order_directives_forward() : array {
		$order_clauses = [];

		foreach($this->attributes as $attribute){
			if($attribute->has_order_clause()){
				$order_clause = $attribute->get_order_clause();
				$order_clauses[$order_clause->get_original_index()] = $order_clause;
			}
		}

		foreach($this->joins as $join){
			if($join->is_forward_join()){
				$order_clauses += $join->collect_order_directives_forward();
			}
		}

		ksort($order_clauses); // really necessary?

		return array_values($order_clauses);
	}


	final public function collect_order_directives_reverse() : array {
		if(!$this instanceof SelectRequest && !$this->is_reverse_join()){
			throw new Exception();
		}

		$order_clauses = $this->collect_order_directives_forward();

		// add fallback
		$order_clauses[] = new OrderClause($this->object->get_primary_identifier(), 'ASC', PHP_INT_MAX);

		foreach($this->joins as $join){
			if($join->is_reverse_join()){
				$order_clauses = array_merge($order_clauses, $join->collect_order_directives_reverse());
			}
		}

		return $order_clauses;
	}


	# Resolve all joins and return their adjoined queries.
	final protected function resolve_joins() : string {
		$query = '';

		foreach($this->joins as $join){
			$query .= $join->get_query();
		}

		return $query;
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
