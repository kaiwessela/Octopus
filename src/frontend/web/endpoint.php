<?php
namespace Blog\Frontend\Web;
use \Blog\Config\Config;
use \Blog\Config\Routes;
use PDO;

class Endpoint {
	public $parsedown;
	public $path;
	public $route;


	function __construct() {
		setlocale(\LC_ALL, Config::SERVER_LANG . '.utf-8');

		if(Config::DEBUG_MODE){
			ini_set('display_errors', '1');
			error_reporting(\E_ALL);
		} else {
			ini_set('display_errors', '0');
			error_reporting(0);
		}

		$this->path = implode('/', [$_GET['page'] ?? '', $_GET['post'] ?? '']);
	}

	public function handle() {
		foreach(Routes::ROUTES as $route){
			if(!preg_match($route['path'], $this->path)){
				continue;
			}

			$this->route = $route;
		}

		if(!$this->route){
			$this->route = Routes::DEFAULT_ROUTE;
		}

		$controllerclass = '\Blog\Frontend\Web\Controllers\\' . $this->route['controller'];
		$controller = new $controllerclass($this->route);


		if(!$controller->load()){
			$this->return_404();
		}

		$controller->display();
	}

	function return_404() {
		http_response_code(404);
		include 'templates/_404.tmp.php';
		exit;
	}
}





/* ################################

# HOW THIS SYSTEM WORKS
This lightweight system provides a simple content management system and basic blog functionality.
Some essential pages like the startpage are predefined and cannot be removed.
You can add as much additional pages as you want simply by adding a template file. The name of that
file determines the path of that page (test.tmp.php -> example.org/test; see ROUTING for details).

# TEMPLATE / COMPONENT NOMENCLATURE
## Difference between Template and Component:
A Template file contains a complete page.
A Component file only contains a part of a page.
Static parts of a page (i.e. header, footer ...) can be outsourced into Component files.
Components can then be included back into Templates.
Components cannot act as a single page.
Templates cannot include Templates.
Components can include other Components.

## Nomenclature
_?.tmp.php	–	predefined Template; used for essential functionality and cannot be removed
?.tmp.php	–	custom Template; use this for your own pages
?.comp.php	–	Component; there are no set rules for component structure or predefined components.
				Structure your site and components how it fits best for you

Template files must be located in the frontend/templates/ folder.
Component files must be located in the frontend/components/ folder.

## Predefined Templates
_404.tmp.php
_index.tmp.php
_post.tmp.php
_posts.tmp.php

# ROUTING
/					– startpage -> _index.tmp.php
/posts				– all posts -> _posts.tmp.php
/posts/[longid]		– single post -> _post.tmp.php
/p/[id]				– shortlink to a single post, same content as /posts/[longid]
/[page]				– static page -> [page].tmp.php

*/ ################################
?>
