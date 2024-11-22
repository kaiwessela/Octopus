<?php
namespace Octopus\Modules\Web\Misentries;

class Misentry {
	public string $attribute_name;
	public string $code;
	public string $exception_class;
	public string $message;
	public array $data;


	public function make_label(callable $label_function) : string {
		return $label_function($this);
	}
}