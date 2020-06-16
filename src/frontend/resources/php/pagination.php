<?php
class Pagination {
	public $objects_per_page;
	public $object_count;
	public $current_page;

	public $pages = [];
	public $page_count;


	function __construct($object_count, $objects_per_page, $current_page = 1) {
		if(!is_int($object_count) || $object_count < 0){
			throw new InvalidArgumentException('Pagination: object_count must be a positive integer or 0.');
		}

		if(!is_int($objects_per_page) || $objects_per_page <= 0){
			throw new InvalidArgumentException('Pagination: objects_per_page must be a positive integer.');
		}

		if(!is_int($current_page) || $current_page <= 0){
			throw new InvalidArgumentException('Pagination: current_page must be a positive integer.');
		}

		$this->objects_per_page = $objects_per_page;
		$this->object_count = $object_count;
		$this->current_page = $current_page;

		$this->page_count = ceil($this->object_count / $this->objects_per_page);

		for($i = 1; $i <= $this->page_count; $i++){
			$this->pages[] = $i;
		}
	}

	public function page_exists($page) {
		return in_array($page, $this->pages);
	}

	public function current_page_exists() {
		return $this->page_exists($this->current_page);
	}

	public function get_object_offset() {
		return $this->objects_per_page * ($this->current_page - 1);
	}

	public function get_object_limit() {
		return $this->objects_per_page;
	}

	public function get_first_object_number() {
		return $this->get_object_offset() + 1;
	}

	public function get_last_object_number() {
		return $this->get_object_offset() + $this->get_object_limit();
	}
}
?>
