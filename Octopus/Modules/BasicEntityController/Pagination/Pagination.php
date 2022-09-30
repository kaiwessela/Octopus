<?php
namespace Octopus\Modules\BasicEntityController\Pagination;
use \Octopus\Modules\BasicEntityController\Pagination\Item;

class Pagination {
	public int $current_page;
	public int $objects_per_page;
	public int $total_objects;
	public string $url_scheme;

	public array $warnings;

	public array $items;


	function __construct(?int $page, ?int $amount, int $count, ?string $url_scheme) {
		$this->warnings = [];

		if($count < 0){
			$this->warnings[] = '$count must not be negative! Setting it to 0.';
			$count = 0;
		}

		if($amount === null){
			$amount = $count;
		} else if($amount <= 0){
			$this->warnings[] = '$amount must be positive! Setting it to 1.';
			$amount = 1;
		}

		if($page === null){
			$page = 1;
		} else if($page <= 0){
			$this->warnings[] = '$page must be positive! Setting it to 1.';
			$page = 1;
		}

		$this->objects_per_page = $amount;
		$this->url_scheme = $url_scheme ?? '';

		if($count === 0){
			$this->current_page = 1;
			$this->total_objects = 0;
			$this->items = [1 => new Item(1, $this)];
			return;
		}

		$this->items = [];
		$this->current_page = $page;
		$this->total_objects = $count;

		for($i = 1; $i <= ceil($this->total_objects / $this->objects_per_page); $i++){
			$this->items[$i] = new Item($i, $this);
		}
	}


	public function pages(callable $function) : void {
		foreach($this->items as $item){
			$function($item);
		}
	}


	public function is_empty() : bool {
		return ($this->total_objects === 0);
	}

	public function first_page() : int {
		return 1;
	}

	public function last_page() : int {
		return ($this->is_empty()) ? 1 : ceil($this->total_objects / $this->objects_per_page);
	}

	public function current_item() : ?Item {
		return $this->items[$this->current_page] ?? null;
	}

	public function page_exists(int $page) : bool {
		return ($page >= $this->first_page() && $page <= $this->last_page());
	}
}
?>
