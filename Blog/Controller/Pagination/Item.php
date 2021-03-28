<?php
namespace Blog\Controller\Pagination;
use \Blog\Config\Config;
use \Blog\Controller\Pagination\Pagination;
use Exception;

class Item {
	public int $number;
	private Pagination $pagination;


	function __construct(int $number, Pagination &$pagination) {
		if($number <= 0){
			throw new Exception("PaginationItem » invalid number: '$number'.");
		}

		$this->number = $number;
		$this->pagination = &$pagination;
	}


	public function is_current() : bool {
		return ($this->number == $this->pagination->current_page);
	}

	public function is_first() : bool {
		return ($this->number == 1);
	}

	public function is_last() : bool {
		return ($this->pagination->last_page() == $this->number);
	}

	public function distance_to_current() : int {
		return abs($this->number - $this->pagination->current_page);
	}

	public function first_object_number() : int {
		return ($this->pagination->objects_per_page * ($this->number - 1) + 1);
	}

	public function last_object_number() : int {
		$result = $this->pagination->objects_per_page * $this->number;
		if($result > $this->pagination->total_objects){
			return $this->pagination->total_objects;
		} else {
			return $result;
		}
	}

	public function object_amount() : int {
		if($this->is_last()){
			return $this->pagination->total_objects - ($this->pagination->objects_per_page * $this->number);
		} else {
			return $this->pagination->objects_per_page;
		}
	}

	public function href() : string {
		return Config::SERVER_URL . '/' . str_replace('{page}', $this->number, $this->pagination->url_scheme);
	}
}
?>
