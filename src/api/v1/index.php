<?php
/* ################################

VALID ROUTES:
/
/posts (?limit=[int](&offset=[int]))
/posts/new
/posts/count
/posts/[id|longid]
/posts/[id|longid]/edit
/posts/[id|longid]/delete
/images (?limit=[int](&offset=[int]))
/images/new
/images/count
/images/[id|longid]
/images/[id|longid]/edit
/images/[id|longid]/delete

*/ ################################

session_start();

# turn off error reporting because it would interfere with the encoding
#error_reporting(0);

# define paths
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');
define('LIBS_PATH', ROOT . 'libs/');

# include config and backend classes
require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'functions.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'contentobject.php';
require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';
require_once BACKEND_PATH . 'imagemanager.php';
require_once 'apirequest.php';
require_once 'apiresponse.php';

# initialize request and response objects
$req = new APIRequest();
$res = new APIResponse();

# set default response values
$res->set_api_status('kaiwessela/blog API v1 – running.');
$res->set_header('Content-Type: application/json');

# establish database connection
try {
	$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
} catch(PDOException $e){
	# database connection failed, answer with error
	$res->set_api_status('kaiwessela/blog API v1 – degraded.');
	$res->set_response_code(500);
	$res->set_error_message('PDO: Connection failed – ' . $e->getMessage());
	$res->send();
}

# initialize image manager
try {
	$imagemanager = new ImageManager(ROOT . 'resources/images/dynamic');
} catch(InvalidArgumentException $e){
	# image manager does not work, change api status
	$res->set_api_status('kaiwessela/blog API v1 – degraded.');
	$res->set_error_message('ImageManager error: ' . $e->getMessage());
}


# CLASS HANDLING MODULE
if(!isset($req->class)){
	# no class requested, answer only with api status
	$res->set_response_code(200);
	$res->send();
} else if($req->class == 'posts'){
	# class Post requested
	$backend_class = 'Post';
} else if($req->class == 'images'){
	# class Image requested
	$backend_class = 'Image';
} else {
	# invalid class requested, answer with error
	$res->set_response_code(400);
	$res->set_error_message('API: invalid class.');
	$res->send();
}


# IDENTIFIER HANDLING MODULE
if(!isset($req->identifier)){
	# no identifier specified -> return all instances of class

	$limit = null;
	$offset = null;
	if(isset($req->query_string['limit'])){
		$limit = (int) $req->query_string['limit'];

		if(isset($req->query_string['offset'])){
			$offset = (int) $req->query_string['offset'];
		}
	}

	try {
		# try to pull all instances of class
		$objs = $backend_class::pull_all($limit, $offset);
	} catch(EmptyResultException $e){
		# no instances found, answer with error
		$res->set_response_code(404);
		$res->set_error_message('API: no objects found.');
		$res->send();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message('API: internal database error.');
		$res->send();
	} catch(InvalidArgumentException $e){
		# invalid argument supplied, answer with error
		$res->set_response_code(400);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# everything worked, return objects
	$res->set_response_code(200);
	$res->set_result($objs);
	$res->send();

} else if($req->identifier == 'new') {
	# generic identifier 'new' specified -> insert a new instance of class
	# check if Request-Method is POST
	if($req->method != 'POST'){
		# Request-Method must be POST but isn't, answer with error
		$res->set_response_code(405);
		$res->set_error_message('API: invalid request method.');
		$res->send();
	}

	# Request-Method is valid
	# create new instance of class
	$obj = $backend_class::new();

	try {
		# try to insert post data into the instance
		$obj->insert($req->post);
	} catch(InvalidInputException $e){
		# post data is invalid, answer with error
		$res->set_response_code(400);
		$res->set_error_message($e->getMessage());
		$res->send();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# everything worked, return object
	$res->set_response_code(200);
	$res->set_result($obj);
	$res->send();

} else if($req->identifier == 'count'){
	# generic identifier 'count' specified -> return the amount of instances of class available
	try {
		# try to count all instances of class
		$count = $backend_class::count();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# everything worked, return count
	$res->set_response_code(200);
	$res->set_result($count);
	$res->send();

} else {
	# object-specific identifier specified -> pull requested instance of class, handle depending on specified action
	try {
		# try to pull the specified instance of class
		$obj = $backend_class::pull($req->identifier);
	} catch(EmptyResultException $e){
		# instance not found, answer with error
		$res->set_response_code(404);
		$res->set_error_message('API: object not found.');
		$res->send();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# proceed in the action handling module

}


# ACTION HANDLING MODULE
if(!isset($req->action)){
	# no action specified -> return instance of class
	$res->set_response_code(200);
	$res->set_result($obj);
	$res->send();

} else if($req->action == 'edit'){
	# action 'edit' specified -> edit instance of class
	# check if Request-Method is POST
	if($req->method != 'POST'){
		# Request-Method must be POST but isn't, answer with error
		$res->set_response_code(405);
		$res->set_error_message('API: invalid request method.');
		$res->send();
	}

	try {
		# try to update the object
		$obj->update();
	} catch(InvalidInputException $e){
		# post data is invalid, answer with error
		$res->set_response_code(400);
		$res->set_error_message($e->getMessage());
		$res->send();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# everything worked, return object
	$res->set_response_code(200);
	$res->set_result($obj);
	$res->send();

} else if($req->action == 'delete'){
	# action 'delete' specified -> delete instance of class
	try {
		# try to delete the object
		$obj->delete();
	} catch(DatabaseException $e){
		# internal database exception, answer with error
		$res->set_response_code(500);
		$res->set_error_message($e->getMessage());
		$res->send();
	}

	# everything worked, return object
	$res->set_response_code(200);
	$res->set_result($obj);
	$res->send();

} else {
	# invalid action specified, answer with error
	$res->set_response_code(400);
	$res->set_error_message('API: invalid action.');
	$res->send();

}
?>
