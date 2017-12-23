<?php

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Africa/Nairobi');

$config['SITE_PATH'] = realpath('.') . DIRECTORY_SEPARATOR;
$config['BASE_PATH'] = realpath('.') . '/dooframework/';
$config['APP_MODE'] = 'dev';
$config['SUBFOLDER'] = DIRECTORY_SEPARATOR."qb".DIRECTORY_SEPARATOR;
$config['APP_URL'] = 'http://' . $_SERVER['HTTP_HOST'] . $config['SUBFOLDER'];
$config['DEBUG_ENABLED'] = FALSE;
$config['ERROR_404_ROUTE'] = '/error';

//  print_r($config);die();
