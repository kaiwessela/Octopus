<?php
namespace Blog\Frontend\Web\Modules;
use \Blog\Config\Routes;
use \Blog\Config\Controllers;

class Router {
	public $path;
	public $route;
	public $params;

/* ================================
ROUTE SYNTAX:
{
	'regex | pathpattern' [<-path]: {
		'template': 'string | string with placeholders',
		'controllers': {
			'string | placeholder' [<- controller name]: {
				'action': 'string',

				#for action=show|edit|delete
				'identifier': 'string | placeholder'

				#for action=list
				'amount': int(>0) [default: 5]
				'page': int(>0) | 'placeholder' [default: 1]

				[… controller-specific values]
			},
			[… more controllers]
		},
		'auth': bool [default: false]
	}
}


REGEX SYNTAX:
/^\/\^.*\$\/$/

PATHPATTERN SYNTAX:
/^([A-Za-z0-9-]+|[\*#](\+|\?|\*|{[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#](\+|\?|\*|{[0-9]+,?[0-9]*})?))*$/



PATHPATTERN REWRITING:
-- use leading and ending /
*/	  /*=>	[^/]+/				recursion 1
#/		=>	[0-9]+/
/*		=>	/[^/]				recursion 2
/#		=>	/[0-9]
/		=>	\/					recursion 3
-- remove leading and ending \/
-- add /^ and $/


PATH WILDCARDS:
"/"		=>	/^$/		[END]
"*"		=>	/^.*$/		[END]


PLACEHOLDER SYNTAX:
/^\?([0-9])

   ================================ */


	function __construct() {
		$this->path = trim($_SERVER['REQUEST_URI'], '/');
		//echo 'requested path: ' . $this->path . \PHP_EOL . \PHP_EOL;

		$routes = json_decode(file_get_contents(__DIR__ . '/../../../config/routes.json'), true);

		foreach($routes as $path => $route){
			//echo '=== ROUTE ===' . \PHP_EOL;
			//echo 'route path: ' . $path . \PHP_EOL;

			if($path == '/'){ # path wildcards
				$matchpath = '/^$/';
				//echo 'is "empty" wildcard; rewritten to: ' . $matchpath . \PHP_EOL;
			} else if($path == '*'){
				$matchpath = '/^.*$/';
				//echo 'is "any" wildcard; rewritten to: ' . $matchpath . \PHP_EOL;
			} else if(preg_match('/^\/\^.*\$\/$/', $path)){
				# path is a regex
				$matchpath = $path;
				//echo 'is already a regex: ' . $matchpath . \PHP_EOL;
			} else if(preg_match('/^([A-Za-z0-9-]+|[\*#](\+|\?|\*|{[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#](\+|\?|\*|{[0-9]+,?[0-9]*})?))*$/', $path)){
				# path is a pathpattern
				$path = '/' . $path . '/';
				$path = str_replace(
					['*/',     '#/',      '/*',    '/#',     '/' ],
					['[^/]+/', '[0-9]+/', '/[^/]', '/[0-9]', '\/'],
					$path
				);
				$path = trim($path, '\/');
				$matchpath = '/^' . $path . '$/';
				//echo 'is a pathpattern; rewritten to: ' . $matchpath . \PHP_EOL;
			} else {
				# error (path invalid)
			}

			//echo \PHP_EOL;

			if(preg_match($matchpath, $this->path)){
				$this->route = $route;
				break;
			}
		}

		if(!$this->route){
			throw new NoRouteFoundException();
		}

		if(is_string($this->route['template'])){
			$this->params['template'] = $this->path_resolve($this->route['template']);
		} else {
			// exception
		}

		if(!isset($this->route['auth'])){
			$this->params['auth'] = true;
		} else if(is_bool($this->route['auth'])){
			$this->params['auth'] = $this->route['auth'];
		} else {
			//exception
		}

		foreach($this->route['controllers'] as $name => $settings){
			$controller = [];

			if(!is_string($name)){
				// exception
			} else if(in_array($name, Controllers::REGISTERED)){
				$name = $name;
			} else if(in_array(Controllers::ALIASES[$name], Controllers::REGISTERED)){
				$name = Controllers::ALIASES[$name];
			} else {
				// exception
			}

			if(!is_string($settings['action'])){
				// exception
			} else {
				$controller['action'] = $settings['action'];
			}

			if($settings['action'] == 'list'){
				if(!isset($settings['amount'])){
					$controller['amount'] = 5;
				} else if(is_int($settings['amount'] && $settings['amount'] > 0)){
					$controller['amount'] = $settings['amount'];
				} else {
					// exception
				}

				if(!isset($settings['page'])){
					$controller['page'] = 5;
				} else if(is_int($settings['page'] && $settings['page'] > 0)){
					$controller['page'] = $settings['page'];
				} else if(is_string($settings['page']) && (int) $this->path_resolve($settings['page']) > 0){
					$controller['page'] = (int) $this->path_resolve($settings['page']);
				} else {
					// exception
				}
			} else if($settings['action'] == 'show' || $settings['action'] == 'edit' || $settings['action'] == 'delete'){
				if(is_string($settings['identifier'])){
					$controller['identifier'] = $this->path_resolve($settings['identifier']);
				} else {
					// exception
				}
			}

			$this->params['controllers'][$name] = $controller;
		}
	}

	public function path_resolve($input) {
		$segments = explode('/', $this->path);

		return preg_replace_callback('/\?([0-9]+)/', function($matches) use ($segments){
			return $segments[$matches[1] - 1];
		}, $input);
	}
}
?>
