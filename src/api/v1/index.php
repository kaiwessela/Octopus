<?php
session_start();

//error_reporting(0);

/* ################################

ROUTING:
/
/posts
/posts/new
/posts/[id|longid]
/posts/[id|longid]/edit
/posts/[id|longid]/delete
/images
/images/new
/images/[id|longid]
/images/[id|longid]/edit
/images/[id|longid]/delete

*/ ################################

define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');
define('LIBS_PATH', ROOT . 'libs/');

require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'functions.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'contentobject.php';
require_once BACKEND_PATH . 'post.php';
require_once BACKEND_PATH . 'image.php';

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

$return = [
	'api_status' => 'kaiwessela/blog API v1 – OK.',
	'response_code' => null,
	'result' => null
];

$class = $_GET['class'] ?? null;
$identifier = $_GET['identifier'] ?? null;
$action = $_GET['action'] ?? null;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	if($_SERVER['CONTENT_TYPE'] == 'application/json'){
		$data = json_decode(file_get_contents('php://input'), true);
	} else {
		$data = $_POST;
	}
}


# NO CLASS
if(!isset($class)){
	returnResponseCode(200);
	finish();

# CLASS POST
} else if($class == 'posts'){

	if(!isset($identifier)){
		try {
			$posts = Post::pull_all();
		} catch(EmptyResultException $e){
			returnResponseCode(404);
			finish();
		} catch(DatabaseException $e){
			returnResponseCode(500);
			returnError($e->getMessage());
			finish();
		}

		returnResponseCode(200);
		returnResult($posts);
		finish();
	} else if($identifier == 'new'){
		if($_SERVER['REQUEST_METHOD'] != 'POST'){
			returnResponseCode(405);
			returnError('invalid request method.');
			finish();
		} else {
			$post = Post::new();

			try {
				$post->insert($data);
			} catch(InvalidInputException $e){
				returnResponseCode(400);
				returnError($e->getMessage());
				finish();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}

			returnResponseCode(200);
			returnResult($post);
			finish();
		}
	} else {
		try {
			$post = Post::pull($identifier);
		} catch(EmptyResultException $e){
			returnResponseCode(404);
			finish();
		} catch(DatabaseException $e){
			returnResponseCode(500);
			returnError($e->getMessage());
			finish();
		}

		if(!isset($action)){
			# continue;
		} else if($action == 'edit'){
			try {
				$post->update($data);
			} catch(InvalidInputException $e){
				returnResponseCode(400);
				returnError($e->getMessage());
				finish();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}
		} else if($action == 'delete'){
			try {
				$post->delete();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}
		} else {
			returnResponseCode(400);
			returnError('invalid action.');
			finish();
		}

		returnResponseCode(200);
		returnResult($post);
		finish();
	}

# CLASS IMAGE
} else if($class == 'images'){

	if(!isset($identifier)){
		try {
			$images = Image::pull_all();
		} catch(EmptyResultException $e){
			returnResponseCode(404);
			finish();
		} catch(DatabaseException $e){
			returnResponseCode(500);
			returnError($e->getMessage());
			finish();
		}

		returnResponseCode(200);
		returnResult($images);
		finish();
	} else if($identifier == 'new'){
		if($_SERVER['REQUEST_METHOD'] != 'POST'){
			returnResponseCode(405);
			returnError('invalid request method.');
			finish();
		} else {
			$image = Image::new();

			try {
				$image->insert($data);
			} catch(ImageManagerException $e){
				returnResponseCode(400);
				returnError($e->getMessage());
				finish();
			} catch(InvalidInputException $e){
				returnResponseCode(400);
				returnError($e->getMessage());
				finish();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}

			returnResponseCode(200);
			returnResult($image);
			finish();
		}
	} else {
		try {
			$image = Image::pull($identifier);
		} catch(EmptyResultException $e){
			returnResponseCode(404);
			finish();
		} catch(DatabaseException $e){
			returnResponseCode(500);
			returnError($e->getMessage());
			finish();
		}

		if(!isset($action)){
			# continue;
		} else if($action == 'edit'){
			try {
				$image->update($data);
			} catch(InvalidInputException $e){
				returnResponseCode(400);
				returnError($e->getMessage());
				finish();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}
		} else if($action == 'delete'){
			try {
				$image->delete();
			} catch(DatabaseException $e){
				returnResponseCode(500);
				returnError($e->getMessage());
				finish();
			}
		} else {
			returnResponseCode(400);
			returnError('invalid action.');
			finish();
		}

		returnResponseCode(200);
		returnResult($image);
		finish();
	}

# INVALID CLASS
} else {
	returnResponseCode(400);
	returnError('invalid class.');
	finish();
}

# FUNCTIONS
function returnResponseCode($code) {
	global $return;

	if($code == 400){
		$return['response_code'] = '400 Bad Request';
		http_response_code(400);
	} else if($code == 404){
		$return['response_code'] = '404 Not Found';
		http_response_code(404);
	} else if($code == 405){
		$return['response_code'] = '405 Method Not Allowed';
		http_response_code(405);
		header('Allow: POST');
	} else if($code == 500){
		$return['response_code'] = '500 Internal Server Error';
		http_response_code(500);
	} else {
		$return['response_code'] = '200 OK';
		http_response_code(200);
	}
}

function returnError($err) {
	global $return;

	$return['error'] = $err;
}

function returnResult($result) {
	global $return;

	$return['result'] = $result;
}

function finish() {
	global $return;

	header('Content-Type: application/json');
	echo json_encode($return);
	exit;
}
?>
