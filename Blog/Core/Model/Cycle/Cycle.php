<?php // CODE ok, COMMENTS --, IMPORTS ok
namespace Blog\Core\Model\Cycle;
use \Blog\Core\Model\Cycle\OutOfCycleException;
use Exception;

class Cycle {
	private array $cycle;
	private ?int|string $stadium;


	function __construct(array $cycle) {
		$root_found = false;

		foreach($cycle as $edge){
			$from = $edge[0];
			$to = $edge[1];

			if(!is_array($edge) || count($edge) != 2 || !is_int($from) || !is_int($to) || !is_string($from) || !is_string($to)){
				throw new Exception("Invalid Cycle Step at: " . var_export($edge));
			}

			if($this->is_root($to)){
				throw new Exception("Invalid Cycle: Illegal Step to root/0 at: " . var_export($edge));
			}

			if($this->is_root($from)){
				$root_found = true;
			}
		}

		if(!$root_found){
			throw new Exception("Invalid Cycle: No Entry Step found.");
		}

		$this->cycle = $cycle;
		$this->stadium = null;
	}


	public function start(?int|string $stadium = null) : void {
		if($this->stadium !== null){
			throw new OutOfCycleException($this->stadium, 'root/0');
		}

		if(empty($stadium)){
			foreach($this->cycle as list($from, $to)){
				if($this->is_root($from)){
					$stadium = $to;
					break;
				}

				# there is no need for a check if no root edge is found, because this was
				# already checked on construction. So there is definitely a valid starting point
			}
		}

		$this->step($stadium);
	}

	public function step(int|string $stadium) : void {
		$this->check_step($stadium);

		$this->stadium = $stadium;
	}

	public function check_step(int|string $stadium, ?bool $exception = true) : void|bool {
		$valid = false;

		foreach($this->cycle as list($from, $to)){
			if($this->stadium === $from && $stadium === $to){
				$valid = true;
				break;
			}
		}

		if($exception){
			if(!$valid){
				throw new OutOfCycleException($this->stadium, $stadium);
			} else {
				return;
			}
		} else {
			return $valid;
		}
	}

	private function is_root(int|string|null $stadium) : bool {
		return ($stadium === 'root' || $stadium === 0 || $stadium === null);
	}

	function __get($value) {
		if($value == 'stadium'){
			return $this->stadium;
		}
	}
}
?>
