<?php
namespace Blog\Controller\Processors\Pagination;

class Item {
	public $disabled;
	public $target; # former absolute_number
	public $steps;  # former relative_number
	public $title;

	public $duplicate_priority;
	public $hide_on_duplicate;

	private $pagination;


	function __construct($settings, &$pagination) {
		$this->pagination = &$pagination;

		$this->disabled = false;
		$this->title = $settings['title'];
		$this->template = (is_int($settings['number'])) ? 'default' : $settings['number'];
		$this->duplicate_priority = $settings['duplicate_priority'];
		$this->hide_on_duplicate = $settings['hide_on_duplicate'];

		if($settings['type'] == 'absolute'){
			if($settings['number'] == 'first'){
				$this->target = 1;
			} else if($settings['number'] == 'last'){
				$this->target = $pagination->total_pages;
			} else if($settings['number'] == 'current'){
				$this->target = $pagination->current_page;
			} else if(is_int($settings['number']) && $settings['number'] > 0){
				$this->target = $settings['number'];
			} else {
				// error
			}

			$this->steps = $this->target - $pagination->current_page;

		} else if($settings['type'] == 'relative'){
			if(is_int($settings['number'])){
				$this->steps = $settings['number'];
			} else {
				// error
			}

			$this->target = $this->steps + $pagination->current_page;

		} else {
			// error
		}

		if(!$this->exists() && !$settings['hide_on_void']){
			$this->disabled = true;
		}

	}

	function __toString() {
		// TODO check if href is working
		return '<a href="./'.$this->target.'" title="'.$this->title.'">'.$this->target.'</a>';
	}

	public function exists() {
		return ($this->target >= 1 && $this->target <= $this->pagination->total_pages);
	}

	public function yields() {
		if(!$this->hide_on_duplicate){
			return false;
		}

		# $this->hide_on_duplicate = true
		foreach($this->pagination->items as $item){
			if($this == $item){
				continue; # cannot compare to self
			}

			if($item->target != $this->target){
				continue; # no conflict
			}

			if(!$item->hide_on_duplicate){
				# the other item never hides but this does so this must yield
				return true;
			}

			# both hide on duplicate, compare priority
			if($this->duplicate_priority < $item->duplicate_priority){
				return true;
			} else {
				continue; # side effect: if both have the same duplicate priority, none must yield
			}
		}
	}
}
?>
