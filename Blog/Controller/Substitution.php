<?php
namespace Blog\Controller;
use \Blog\Controller\Request;

class Substitution {
	private string $placeholder;
	private Request $request;
	private string $result;
	private string $default;


	function __construct(string $placeholder, Request $request, string $default = '') {
		$this->placeholder = $placeholder;
		$this->request = $request;
		$this->default = $default;
	}


	function __toString() {
		return $this->resolve();
	}


	public function resolve() : string {
		if(empty($this->result)){
			$regex = '/(^|{)(([\/])([0-9]+)|([\?])([A-Za-z0-9-_.]+))(\|([A-Za-z0-9-_.]+))?(}|$)/';
			$request = $this->request;
			$default = $this->default;

			$this->result = preg_replace_callback($regex, function($matches) use ($request, $default){
				if(!empty($matches[3]) && $matches[3] == '/'){
					# path mode
					$segments = explode('/', trim($request->path, '/'));
					$value = $segments[$matches[4] - 1] ?? null;
				} else if($matches[5] == '?'){
					# query mode
					$value = $request->GET($matches[6]);
				}
				// IDEA fragment mode

				if(is_null($value)){
					$value = $default;
				}

				return $value;
			}, $this->placeholder);
		}

		return $this->result;
	}
}
?>
