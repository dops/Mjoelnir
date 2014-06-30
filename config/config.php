<?php

// If executing from console, set the useragent to topdealsCron.
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = 'MjoelnirCron';
}

/**
 * Env vars
 */
define('APPLICATION_ENV_DEVELOPMENT', 'development');
define('APPLICATION_ENV_STAGE', 'stage');
define('APPLICATION_ENV_LIVE', 'live');
define('APPLICATION_ENV', (isset($_SERVER['APPLICATION_ENV'])) ? $_SERVER['APPLICATION_ENV'] : 'production');
define('APPLICATION_NAME', (isset($_SERVER['APPLICATION_NAME'])) ? $_SERVER['APPLICATION_NAME'] : 'Www');
define('APPLICATION_LOG_MAIL', (isset($_SERVER['APPLICATION_LOG_MAIL'])) ? $_SERVER['APPLICATION_LOG_MAIL'] : 'dev@michael-streb.de');

/**
 * Path definitions
 */
if (substr($_SERVER['DOCUMENT_ROOT'], strlen($_SERVER['DOCUMENT_ROOT']) - 1, 1) == '/') {
    define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
} else {
    define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}
define('WEB_ROOT', '/');
define('PATH_LIBRARY', DOCUMENT_ROOT . '../library/');
//define('SMARTY_DIR', PATH_LIBRARY . 'Smarty/');
define('PATH_LOG', DOCUMENT_ROOT . '../var/log/');
define('PATH_MODEL', DOCUMENT_ROOT . '../application/model/');
define('PATH_INTERFACE', DOCUMENT_ROOT . '../application/interface/');
define('PATH_APPLICATION', DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/');
define('PATH_TEMPLATE', DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/view/template/');
define('PATH_GLOBAL_TEMPLATE', DOCUMENT_ROOT . '../application/view/template/');
define('PATH_TEMPLATE_CACHE', DOCUMENT_ROOT . '../var/smarty/templates_cache/');
define('PATH_TEMPLATE_COMPILE', DOCUMENT_ROOT . '../var/smarty/templates_compile/');
define('PATH_CONTROLLER', DOCUMENT_ROOT . '../application/' . APPLICATION_NAME . '/controller/');
define('PATH_CONFIG', DOCUMENT_ROOT . '../config/');
define('PATH_CSS', 'css/');
define('PATH_JS', 'js/');
define('PATH_IMAGES', 'images/');
define('PATH_USERFILES', '../var/userFiles/');

// Set the library path as include path
set_include_path(get_include_path() . ':' . PATH_LIBRARY);

/**
 * Default page and action
 */
define('DEFAULT_CONTROLLER', 'index');
define('DEFAULT_ACTION', 'index');

/**
 * Smarty configuration / cache settings
 */
switch ($_SERVER['APPLICATION_ENV']) {
    case APPLICATION_ENV_DEVELOPMENT:
    case APPLICATION_ENV_STAGE:
        define('SMARTY_CACHING', false);
        define('SMARTY_COMPILE_CHECK', true);
        define('SMARTY_COMPILE_FORCE', true);
        define('SMARTY_CACHING_LIFETIME', 0);
        break;
    default:
        # production
        define('SMARTY_CACHING', false);
        define('SMARTY_COMPILE_CHECK', false);
        define('SMARTY_COMPILE_FORCE', false);
        define('SMARTY_CACHING_LIFETIME', -1);
        break;
}

/**
 * The custom auth parameter definition bases on the application name, to enable multi login between different applications.
 */
define('AUTH_CUSTOM_PARAMETER', 'loginHash' . APPLICATION_NAME);

/**
 * The user role id used for the admin user role.
 */
define('ADMIN_USER_ROLE_ID', 1);

/**
 * To allow unregistered users to view more than just the default page, a user role id for the uregesitered user rights is defined here.
 */
define('UNREGISTERED_USER_ROLE', 2);

/**
 * The userrole used for customer users.l
 */
define('CUSTOMER_USER_ROLE_ID', 3);

/**
 * Authentification
 */
define('AUTH_EXPIRE', 60 * 60 * 24);

/**
 * Default database to use
 */
define('DEFAULT_DB', 'Mjoelnir');

/**
 * Error reporting
 */
ini_set('display_errors', true);

/**
 * Encryption prefix
 */
define('ENCRYPT_PREFIX', '$1$bvMJUFBp$plZ9Qccqnbv/bISmqBvJN1');

/**
 * Set locale
 */
//setlocale(LC_ALL, 'de_DE', 'de_DE', 'de', 'ge');
//setlocale(LC_MONETARY, 'de_DE');

/**
 * Set return method depending on user agent
 */
switch ($_SERVER['HTTP_USER_AGENT']) {
    case 'MjoelnirXhr': define('RETURN_METHOD', 'json');
        break;
    default: define('RETURN_METHOD', 'html');
}

/**
 * Page title prefix
 */
define('PAGE_TITLE_PREFIX', 'Mjoelnir Development');
define('PAGE_TITLE_GLUE', '::');

/**
 * Email adresses
 */
/**
 * Log-Level
 */
if (APPLICATION_ENV == APPLICATION_ENV_DEVELOPMENT) {
    define('LOG_LEVEL', 7);
} elseif (APPLICATION_ENV == APPLICATION_ENV_STAGE) {
    define('LOG_LEVEL', 5);
} else {
    define('LOG_LEVEL', 3);
}

/**
 * Logger constants (mapping of logger-names on path/logfile.name)
 */
//add others if necessary
define(APPLICATION_NAME, DOCUMENT_ROOT . '../var/log/' . APPLICATION_NAME . '_' . date('Y-m-d') . '.log');
define('LOGGER_CRAWL', DOCUMENT_ROOT . '../var/log/_crawl_' . date('Y-m-d') . '.log');
define('LOGGER_CRAWL_RESPONSE', DOCUMENT_ROOT . '../var/log/_crawl_response_' . date('Y-m-d') . '.log');
define('LOGGER_CRONJOB', DOCUMENT_ROOT . '../var/log/_cronjob_' . date('Y-m-d') . '.log');

/**
 * Diverent
 */
define('ITEMS_PER_PAGE', 30);