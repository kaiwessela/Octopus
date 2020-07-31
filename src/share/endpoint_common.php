<?php
# --- basic initialisation algorithm common to all endpoints ---
# do not change these values unless you know what you are doing

require_once ROOT . 'config/config.php';
require_once ROOT . 'config/constants.php';

# set locale to the language defined in the config file
setlocale(LC_ALL, SERVER_LANG . '.utf-8');

if(DEBUG_MODE){
	ini_set('display_errors', '1');
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', '0');
	error_reporting(0);
}

# establish database connection
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);

require_once SHARE_PATH . 'exceptions.php';
require_once SHARE_PATH . 'backend_functions.php';
require_once BACKEND_PATH . 'contentobject.php';
?>
