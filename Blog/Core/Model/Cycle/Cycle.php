<?php
namespace Octopus\Core\Model\Cycle;
use \Octopus\Core\Model\Cycle\OutOfCycleException;
use Exception;

class Cycle {
	# for performance reasons, the internal format for a cycle is different than the 'construction' format as defined
	# in __construct(); in the internal array, there is only one key-value pair for each node, with the (starting) node
	# being the key and the value being an array of all allowed ending nodes:
	# [from => [to1, to2, to3, …], …]
	private array $cycle;
	private string|null $stadium;


	# @param $cycle: Array of valid steps; A valid step is itself defined as an array of two strings,
	# the first one being the starting point, the second one being the ending point of the step: [[from, to], …].
	# The cycle must contain at least one edge starting from the root node.
	function __construct(array $cycle) {
		$this->cycle = [];
		$this->stadium = 'root';
		$root_found = false; # true if an edge starting from the root node ('root') was found

		# check all edges of the proposed cycle
		foreach($cycle as $edge){
			list($from, $to) = $edge; # split edge in from and to

			# check formal validity
			if(!is_array($edge) || count($edge) != 2 ||!is_string($from) || !is_string($to)){
				throw new Exception("Invalid Cycle Step at: " . var_export($edge));
			}

			# check for the root node being the ending point of the edge - that is not allowed
			if($this->is_root($to)){
				throw new Exception("Invalid Cycle: Illegal Step to «root» at: " . var_export($edge));
			}

			# check whether the root node is the starting point of the edge, in which case a root edge was found
			if($this->is_root($from)){
				$root_found = true;
			}

			# store the edge in the format explained above (at the property definitions)
			if(isset($this->cycle[$from])){
				$this->cycle[$from][] = $to;
			} else {
				$this->cycle[$from] = [$to];
			}
		}

		# if no root edge was found, throw an error
		if(!$root_found){
			throw new Exception("Invalid Cycle: No Starting Point found.");
		}
	}


	# Start the cycle by stepping from the root node to the first ordinary node. If there are multiple root edges,
	# $stadium can define which one to take ($stadium then must equal the ending node of that edge). On null, the
	# first root edge will be chosen.
	public function start(?string $stadium = null) : void {
		if($this->stadium !== null){ # if the cycle has already been started, throw an error
			throw new OutOfCycleException($this->stadium, 'root/0');
		}

		if(empty($stadium)){
			$stadium = $this->cycle['root'][0];
		}

		$this->step($stadium);
	}


	# Change the current stadium using a valid edge. $stadium is the ending node of that edge.
	public function step(string $stadium) : void {
		# first check whether the proposed step is currently allowed
		$this->check_step($stadium); # @throws OutOfCycleException

		$this->stadium = $stadium;
	}


	# Return whether stepping to a specific node (defined by $stadium) is allowed from the current stadium.
	# If $exception is true, an OutOfCycleException is thrown if the step is forbidden. Otherwise, the result is just
	# returned as a boolean.
	public function check_step(string $stadium, ?bool $exception = true) : bool {
		$valid = false;

		# check whether the proposed step is defined in $this->cycle
		if(isset($this->cycle[$this->stadium][$stadium])){
			return true;
		} else if($exception){ # the step is not allowed. if an exception is expected, throw it, otherwise return false
			throw new OutOfCycleException($this->stadium, $stadium);
		} else {
			return false;
		}
	}


	# Return whether the current stadium equals $stadium
	public function is_at(string $stadium) : bool {
		return $this->stadium === $stadium;
	}


	private function is_root(string $stadium) : bool {
		return $stadium === 'root';
	}
}
?>
