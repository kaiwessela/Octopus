<?php
namespace Blog\Config;

# --- server config file ---
# you can change and probably have to change these values according to your server and page
# configuration. a wrong value mostly won't cause any damage, but nevertheless please read the
# instructions next to every setting carefully.

class Config {
	# SERVER VARIABLES
	# url of your website (the one you type in to get to the home page)
	# use https:// if you use ssl encryption or http:// if not
	const SERVER_URL = 'http://blog.local'; # with http(s)://, without ending slash /
	# language setting used for things like date formatting (Monday / Lundi / Montag / …)
	const SERVER_LANG = 'de_DE'; # de_DE, de_AT, fr_FR, en_EN, en_US, … (without .UTF.8)

	# DEBUG MODE
	# enabling this mode will turn on php error reporting. use it for debugging, if you only see a
	# blank page or if you see any other bugs
	const DEBUG_MODE = true; # true = debug mode, false = normal/production mode

	# MYSQL DATABASE CONFIGURATION
	# authentication credentials for your mysql database which stores your posts and other site data
	const DB_HOST = 'localhost'; # the hostname / url of your database server
	const DB_NAME = 'blog_new'; # name of your database
	const DB_USER = 'user'; # username of your database user
	const DB_PASSWORD = 'password'; # password of your database user

	# BLOG VERSION
	# just informational purpose - do not edit
	const VERSION = 'v0.12.1-beta';
}
?>
