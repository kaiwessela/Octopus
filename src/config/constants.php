<?php
# --- file paths ---
# DO NOT CHANGE THESE VALUES IF YOU DO NOT KNOW WHAT YOU ARE DOING
# changing these values is not necessary or helpful for normal users

define('API_PATH', ROOT . 'api/');
define('BACKEND_PATH', ROOT . 'backend/');
define('CONFIG_PATH', ROOT . 'config/');

# consider that source file structure changes on build (frontend folders move into parent)
define('ADMIN_PATH', ROOT . 'admin/');
define('COMPONENT_PATH', ROOT . 'components/');
define('RESOURCE_PATH', ROOT . 'resources/');
define('TEMPLATE_PATH', ROOT . 'templates/');

define('FRONTEND_INCLUDES', RESOURCE_PATH . 'php/');

define('LIBS_PATH', ROOT . 'libs/');

# relative to config.php
define('DYN_IMG_PATH', SERVER_URL . '/resources/images/dynamic/');
define('STA_IMG_PATH', SERVER_URL . '/resources/images/static/');
?>
