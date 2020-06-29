<?php
# --- server config file ---
# you can change and probably have to change these values according to your server and page
# configuration. a wrong value mostly won't cause any damage, but nevertheless please read the
# instructions next to every setting carefully.

# SERVER VARIABLES
# url of your website (the one you type in to get to the home page)
# use https:// if you use ssl encryption or http:// if not
define('SERVER_URL', 'http://blog.local'); # with http(s)://, without ending slash /
# language setting used for things like date formatting (Monday / Lundi / Montag / …)
define('SERVER_LANG', 'de_DE'); # de_DE, de_AT, fr_FR, en_EN, en_US, … (without .UTF.8)

# DEBUG MODE
# enabling this mode will turn on php error reporting. use it for debugging, if you only see a
# blank page or if you see any other bugs
define('DEBUG_MODE', true); # true = debug mode, false = normal/production mode

# MYSQL DATABASE CONFIGURATION
# authentication credentials for your mysql database which stores your posts and other site data
define('DB_HOST', 'localhost'); # the hostname / url of your database server
define('DB_NAME', 'blog'); # name of your database
define('DB_USER', 'user'); # username of your database user
define('DB_PASSWORD', 'password'); # password of your database user

# WEBSITE CONFIGURATION
# the amount of preview posts displayed per post list page
define('PREVIEW_POSTS_PER_PAGE', 3);


# - end of server configuration -
?>
