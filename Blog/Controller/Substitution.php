<?php
namespace Blog\Controller;
use \Blog\Controller\Request;

class Substitution {
	private ?string $placeholder;
	private bool $numeric;
	private Request $request;
	private string|int|float|null $result;


	function __construct(?string $placeholder, Request $request, bool $numeric = false) {
		$this->placeholder = $placeholder;
		$this->request = $request;
		$this->numeric = $numeric;

	}


	public static function new(?string $placeholder, Request $request, bool $numeric = false) : Substitution{
		return new Substitution($placeholder, $request, $numeric);
	}


	public function resolve() : string|int|float|null {
		if(!isset($this->result)){
			$regex = '/(^|{)(([\/])([0-9]+)|([\?])([A-Za-z0-9-_.]+))(\|([A-Za-z0-9-_.]+))?(}|$)/';
			$request = $this->request;
			$numeric = $this->numeric;

			$result = preg_replace_callback($regex, function($matches) use ($request, $numeric){
				if(!empty($matches[3]) && $matches[3] == '/'){
					# path mode
					$segments = explode('/', trim($request->path, '/'));
					$replacement = $segments[$matches[4] - 1] ?? null;
				} else if($matches[5] == '?'){
					# query mode
					$replacement = $request->GET($matches[6]);
				}

				if($replacement == '' || $replacement == null || ($numeric && !is_numeric($replacement))){
					$replacement = $matches[8] ?? '';
				}

				return $replacement;
			}, $this->placeholder);

			if($result == ''){
				$this->result = null;
			} else if($this->numeric && is_numeric($result)){
				if(floor($result) === ceil($result)){
					$this->result = (int) $result;
				} else {
					$this->result = (float) $result;
				}
			} else {
				$this->result = $result;
			}
		}

		return $this->result;
	}
}
?>
