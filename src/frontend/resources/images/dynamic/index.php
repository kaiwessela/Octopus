<?php
// DEPRECATED

session_start();

error_reporting(0);

# define constants
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BACKEND_PATH', ROOT . 'backend/');
define('TEMPLATE_PATH', ROOT . 'templates/');
define('COMPONENT_PATH', ROOT . 'components/');
define('CONFIG_PATH', ROOT . 'config/');

require_once CONFIG_PATH . 'config.php';
require_once BACKEND_PATH . 'functions.php';
require_once BACKEND_PATH . 'exceptions.php';
require_once BACKEND_PATH . 'contentobject.php';
require_once BACKEND_PATH . 'image.php';
require_once BACKEND_PATH . 'imagefile.php';

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

# read query string
$qs_image = $_GET['image'] ?? null;
$qs_size = $_GET['size'] ?? null;

# check if an image is requested in the query string and its name does not start with a dot
# image longids cannot contain dots. A dot indicates the beginning of the filename extension.
# a string only containing the filename extension would cause problems later in the script
if(!isset($qs_image) || strpos($qs_image, '.') === 0){
	http_response_code(400);
	echo 'ERROR (400 Bad Request): No Image Specified';
	exit;
}

# split the request string into image longid and filename extension
$req_image = explode('.', $qs_image);
$longid = $req_image[0];
$extension = $req_image[1] ?? null;

# try to pull the image by its longid
try {
	$image = Image::pull_by_longid($longid);
} catch(ObjectNotFoundException $e){
	http_response_code(404);
	echo 'ERROR (404 Not Found): No Image Found';
	exit;
}

# check if the requested filename extension is the correct one for this image
# this is basically only done for cosmetic reasons. I do not want this script to deliver an image
# if the request string is not completely correct, even if the longid is. Doing this could cause
# confusion, caching issues etc. It would not be a big problem, but it is nicer this way
if($image->extension != $extension){
	http_response_code(308);
	header('Location: ./' . $image->longid . '.' . $image->extension);
	exit;
}

# get a list of all available image sizes
$available_sizes = Imagefile::pull_all_sizes($image->id, true);

# set the original size as default / fallback
$use_file = $available_sizes[Imagefile::SIZE_ORIGINAL];

# check if a special size is requested and try to fulfill that request
if($qs_size == 'small' || $qs_size == Imagefile::SIZE_SMALL){
	if(isset($available_sizes[Imagefile::SIZE_SMALL])){
		$use_file = $available_sizes[Imagefile::SIZE_SMALL];
	}
} else if($qs_size == 'middle' || $qs_size == Imagefile::SIZE_MIDDLE){
	if(isset($available_sizes[Imagefile::SIZE_MIDDLE])){
		$use_file = $available_sizes[Imagefile::SIZE_MIDDLE];
	}
} else if($qs_size == 'large' || $qs_size == Imagefile::SIZE_LARGE){
	if(isset($available_sizes[Imagefile::SIZE_LARGE])){
		$use_file = $available_sizes[Imagefile::SIZE_LARGE];
	}
}

# get the image file with the requested and available size
try {
	$file = Imagefile::pull_by_id($use_file->id);
} catch(ObjectNotFoundException $e){
	http_response_code(500);
	echo 'ERROR (500 Internal Server Error): Database Error';
	exit;
}

# send the image
if($image->extension == Image::EXTENSION_PNG){
	header('Content-Type: image/png');
} else if($image->extension == Image::EXTENSION_JPG){
	header('Content-Type: image/jpeg');
} else if($image->extension == Image::EXTENSION_GIF){
	header('Content-Type: image/gif');
}

echo $file->data;
?>
