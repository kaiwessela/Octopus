<?php
namespace Octopus\Modules\Web\Misentries;

class MisentryList {
	protected array $misentries;


	public function sublist(string $attribute_name) : MisentryList {

	}


	public function has(string $attribute_name, ?string $code = null) : bool {
		if(!isset($this->misentries[$attribute_name])){
			return false;
		} if(!is_null($code)){
			return isset($this->misentries[$attribute_name][$code]);
		} else {
			return true;
		}
	}


	public function make_labels(callable $label_function) : string {
		$labels = '';

		foreach($this->misentries as $_ => $sublist){
			foreach($sublist as $_ => $misentry){
				$labels .= $misentry->make_label($label_function);
			}
		}

		return $labels;
	}


	public function make_dummy_labels(callable $label_function, string $attribute_name, array $codes) : string {
		$labels = '';
		
		foreach($codes as $code => $data){
			$labels .= $label_function($attribute_name, $code, $data);
		}

		return $labels;
	}
}