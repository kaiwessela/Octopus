<?php
namespace Blog\Controller\Processors\Pagination;
use \Blog\Config\Pagination as PaginationConfig;
use \Blog\Controller\Processors\Pagination\Item;
use InvalidArgumentException;

class Pagination {
	private $current_page;
	private $objects_per_page;
	private $total_objects;
	#total_pages;
	#first_object;
	#last_object;

	public $structure;
	public $base_path;

	public $items;


	function __construct($current_page, $objects_per_page, $total_objects, $base_path, $structure = null) {
		if((is_int($current_page) && $current_page > 0) || is_null($current_page)){
			$this->current_page = (int) $current_page ?? 1;
		} else {
			throw new InvalidArgumentException('Pagination: current_page must be a positive integer or 0.');
		}

		if(is_int($objects_per_page) && $objects_per_page > 0){
			$this->objects_per_page = $objects_per_page;
		} else {
			throw new InvalidArgumentException('Pagination: objects_per_page must be a positive integer.');
		}

		if(is_int($total_objects) && $total_objects >= 0){
			$this->total_objects = $total_objects;
		} else {
			throw new InvalidArgumentException('Pagination: total_objects must be a positive integer or 0.');
		}

		if(is_string($base_path)){
			$this->base_path = $base_path;
		} else {
			throw new InvalidArgumentException('Pagination: base_path must be a valid string.');
		}

		$this->structure = PaginationConfig::STRUCTURES[$structure] ?? Config::STRUCTURES['default'];

		foreach($this->structure as $item_settings){
			$item = new Item($item_settings, $this);

			if(!$item->exists() && !$item->disabled){
				continue;
			} else {
				$this->items[] = $item;
			}
		}

		$this->items = array_filter($this->items, function($item){
			return !$item->yields();
		});
	}

	function __get($name) {
		if($name == 'current_page'){
			return $this->current_page;

		} else if($name == 'objects_per_page'){
			return $this->objects_per_page;

		} else if($name == 'total_objects'){
			return $this->total_objects;

		} else if($name == 'total_pages'){
			return ceil($this->total_objects / $this->objects_per_page);

		} else if($name == 'first_object'){
			return ($this->current_page - 1) * $this->objects_per_page + 1;

		} else if($name == 'last_object'){
			return (($this->current_page * $this->objects_per_page) > $this->total_objects)
				? $this->total_objects : ($this->current_page * $this->objects_per_page);

		}
	}
}