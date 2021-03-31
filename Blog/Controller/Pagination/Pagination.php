<?php
namespace Blog\Controller\Pagination;
use \Blog\Controller\Pagination\Item;
use Exception;

class Pagination {
	public int $current_page;
	public int $objects_per_page;
	public int $total_objects;
	public string $url_scheme;

	public array $items;


	function __construct(?int $page, ?int $amount, int $count, string $url_scheme) {
		if($count < 0){
			throw new Exception('Pagination » count cannot be negative.');
		}

		if($amount === null){
			$amount = $count;
		} else if($amount <= 0){
			throw new Exception('Pagination » amount must be positive.');
		}

		if($page === null){
			$page = 1;
		} else if($page < 0){
			throw new Exception('Pagination » page cannot be negative.');
		} else if($page == 0 && $count != 0){
			throw new Exception('Pagination » page cannot be 0 when count is positive.');
		}

		$this->objects_per_page = $amount;
		$this->url_scheme = $url_scheme;

		if($count == 0){
			$this->current_page = 0;
			$this->total_objects = 0;
			$this->items = [];
			return;
		}

		$this->current_page = $page;
		$this->total_objects = $count;

		if($this->objects_per_page == 0){
			$this->items = [];
		} else {
			for($i = 1; $i <= ceil($this->total_objects / $this->objects_per_page); $i++){
				$this->items[$i] = new Item($i, $this);
			}
		}
	}


	public function is_empty() : bool {
		return ($this->current_page == 0);
	}

	public function first_page() : ?int {
		return ($this->is_empty()) ? null : 1;
	}

	public function last_page() : ?int {
		return ($this->is_empty()) ? null : ceil($this->total_objects / $this->objects_per_page);
	}

	public function current_item() : ?Item {
		return $this->items[$this->current_page] ?? null;
	}

	public function page_exists(int $page) : bool {
		return (!$this->is_empty() && $page >= $this->first_page() && $page <= $this->last_page());
	}
}
?>
