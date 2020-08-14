<?php
namespace Blog\Frontend\Web\Modules\Pagination;
use \Blog\Config\PaginationConfig;
use \Blog\Backend\Exceptions\InvalidArgumentException;

class Pagination {
	public $object_count;
	public $current_page;
	public $page_count;

	public $items;


	function __construct($object_count, $current_page = 1) {
		if(!is_int($object_count) || $object_count < 0){
			throw new InvalidArgumentException('Pagination: object_count must be a positive integer or 0.');
		}

		if(!is_int($current_page) || $current_page <= 0){
			throw new InvalidArgumentException('Pagination: current_page must be a positive integer.');
		}

		$this->object_count = $object_count;
		$this->current_page = $current_page;

		$this->page_count = ceil($this->object_count / PaginationConfig::OBJECTS_PER_PAGE);

		if($this->page_count == 0){ # set an empty page if there are no objects
			$this->page_count = 1;
		}
	}

	public function load_items() {
		$item_count = ceil($this->object_count / PaginationConfig::OBJECTS_PER_PAGE);

		foreach(PaginationConfig::STRUCTURE as $item){
			$item = (object) $item;
			$item->disabled = false;

			if($item->type == 'absolute'){
				if($item->page == 'first'){
					$item->absolute_number = 1;
				} else if($item->page == 'last'){
					$item->absolute_number = (int) $this->page_count;
				} else if($item->page == 'current'){
					$item->absolute_number = (int) $this->current_page;
				} else if(is_int($item->page)){
					$item->absolute_number = $item->page;
				} else {
					continue;
				}

				$item->relative_number = $this->current_page - $item->absolute_number;
			} else if($item->type == 'relative'){
				if(is_int($item->page)){
					$item->relative_number = $item->page;
					$item->absolute_number = $this->current_page + $item->relative_number;
				} else {
					continue;
				}
			} else {
				continue;
			}

			if($item->absolute_number < 1 || $item->absolute_number > $this->page_count){
				if($item->hide_on_void){
					continue;
				} else {
					$item->disabled = true;
				}
			}

			$this->items[] = $item;
		}

		$numbers_used = [];
		foreach($this->items as $index => $item){
			if(!array_key_exists((int) $item->absolute_number, $numbers_used)){
				# number is not used but will from now on be, write it into the list
				$numbers_used[$item->absolute_number] = $index;
				continue;
			} else {
				# number is already used
				$before_index = $numbers_used[$item->absolute_number];
				if($item->duplicate_priority > $this->items[$before_index]->duplicate_priority){
					if($this->items[$before_index]->hide_on_duplicate){
						unset($this->items[$before_index]);
					}

					$numbers_used[$item->absolute_number] = $index;
				} else if($item->duplicate_priority < $this->items[$before_index]->duplicate_priority){
					if($item->hide_on_duplicate){
						unset($this->items[$index]);
					}
				} else {
					continue;
				}
			}
		}
	}

	public function display_items() {
		$pagination = &$this; // TEMP
		foreach($this->items as $item){
			include __DIR__ . '/templates/' . $item->template . '.item.php';
		}
	}

	public function display() {
		$pagination = &$this;
		include __DIR__ . '/templates/main.php';
	}

	public function page_exists($page) {
		return $page >= 1 && $page <= $this->page_count;
	}

	public function current_page_exists() {
		return $this->page_exists($this->current_page);
	}

	public function get_object_offset() {
		return PaginationConfig::OBJECTS_PER_PAGE * ($this->current_page - 1);
	}

	public function get_object_limit() {
		return PaginationConfig::OBJECTS_PER_PAGE;
	}

	public function get_first_object_number() {
		$first_number = $this->get_object_offset() + 1;

		if($first_number > $this->object_count){
			return $this->object_count;
		} else {
			return $first_number;
		}
	}

	public function get_last_object_number() {
		$last_number = $this->get_object_offset() + $this->get_object_limit();

		if($last_number > $this->object_count){
			return $this->object_count;
		} else {
			return $last_number;
		}
	}
}
?>
