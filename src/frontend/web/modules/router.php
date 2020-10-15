<?php
namespace Blog\Frontend\Web\Modules;
use Blog\Frontend\Web\Router\Exceptions\InvalidRoutingTableException;
use Blog\Frontend\Web\Router\Exceptions\RouteNotFoundException;

class Router {
	public $path;
	public $template;
	public $auth;
	public $controller_requests;

	private $routes;


	function __construct(string $routes_json) {
		# load the requested path (https://example.org/test/path)
		#                               this section: ~~~~~~~~~~
		$this->path = trim($_SERVER['REQUEST_URI'], '/'); # remove leading and ending slashes

		# parse the routing json to an array, throw error if it fails
		$this->routes = json_decode($routes_json, true, \JSON_THROW_ON_ERROR);

		# check if routing table is an array
		if(!is_array($this->routes){
			throw new InvalidRoutingTableException($this->routes);
		}

		# try to find the route corresponding to the requested path
		# @param $path: route path as plain text, wildcard, pathpattern or regex;
		# @param $settings: settings specified for the route in the routing file;
		foreach($this->routes as $path => $settings){
			# rewrite plain text, wildcard or pathpattern to regex
			$regex = $this->rewrite_to_regex($path);

			# check if path regex corresponds to requested path
			if(preg_match($regex, $this->path)){
				# it does; set this route and break the loop
				$route = $settings;
				break;
			}
		}

		# check if a route was found at all
		if(!$route){
			throw new NoRouteFoundException();
		}

		$this->set_template($route['template']);
		$this->set_auth($route['auth']);

		foreach($route['controllers'] as $name => $parameters){
			
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

	private function set_template($raw_template) {
		# check if raw template is a string
		if(!is_string($raw_template)){
			throw new InvalidRouteAttributeException();
		} else {
			$template = $this->resolve_substitutions($this->raw_template);

			# check if template contains any forbidden directory segments, such as /../
			# template files may only sit in template dir or a child of that
			if(preg_match('/(\.\.\/|\/\.\.)/'), $template){
				throw new InvalidRouteAttributeException();
			} else {
				# there was no problem, set template as router template attribute
				$this->template = $template;
			}
		}
	}

	private function set_auth($raw_auth = null) {
		# check if auth attribute is set
		if(is_null($raw_auth)){
			$this->auth = false; # set default auth value as router auth attribute
		} else if(is_bool($raw_auth)){
			$this->auth = $raw_auth; # auth attribute is set and a boolean, set it
		} else {
			throw new InvalidRouteAttributeException();
		}
	}

	private function resolve_substitutions($string) {
		$segments = explode('/', $this->path); # create a segment list from path

		# match the string, ?n => $matches[1] = n
		return preg_replace_callback('/\?([0-9]+)/', function($matches) use ($segments){
			# -1 because arrays count from 0 but path segments from 1
			return $segments[$matches[1] - 1];
		}, $string);
	}

	private function rewrite_to_regex($notation) {
		# pathpattern definition
		$pathpattern = '/^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$/';
		# regex definition
		$regex = '/^\/\^.*\$\/$/';

		if($notation == '/'){ # test for 'empty' wildcard
			return '/^$/'; # empty matching regex
		} else if($notation == '*'){ # test for 'any' wildcard
			return '/^.*$/'; # any character matching regex
		} else if(preg_match($regex, $notation)){ # test for regex
			return $notation; # regex stays the same
		} else if(preg_match($pathpattern, $notation)){ # test for pathpattern
			return '/^' . str_replace( # replace characters as described below and add slashes
				['*{',	  '#{',     '/*?',       '/#?',        '*',     '#',      '/'  ],
				['[^/]{', '[0-9]{', '(/[^/]+)?', '(/[0-9]+)?', '[^/]+', '[0-9]+', '\/' ],
				$notation
			) . '$/';
		} else {
			throw new InvalidNotationException();
		}
	}
}

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
/^([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?)(\/([A-Za-z0-9-]+|[\*#]({[0-9]+,?[0-9]*})?))*([\*#]*\??)?$/



PATHPATTERN REWRITING:
*{		=>	[^/]{				recursion 1
#{		=>	[0-9]{
/*?		=>	(/[^/]+)?			recursion 2
/#?		=>	(/[0-9]+)?
*		=>	[^/]+				recursion 3
#		=>	[0-9]+
/		=>	\/					recursion 4

-- add /^ and $/


PATH WILDCARDS:
"/"		=>	/^$/		(empty wildcard)
"*"		=>	/^.*$/		(any wildcard)


PLACEHOLDER SYNTAX:
/^\?([0-9])

   ================================ */
?>
