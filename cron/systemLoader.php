<?php

/**
 * Set parameters needed for cron execution
 */
$_SERVER['APPLICATION_ENV'] = $argv[1];
$pathInfo                   = pathinfo(__FILE__);
$_SERVER['DOCUMENT_ROOT']   = $pathInfo['dirname'];

//$_FilterUserId				= $argc > 2 ? $argv[2] : "";


/**
 * Load config
 */
include dirname (__FILE__).'/../config/config.php';

/**
 * Load autoloader
 */
set_include_path(get_include_path() . ':' . PATH_LIBRARY);
include PATH_LIBRARY . '/Autoloader.php';