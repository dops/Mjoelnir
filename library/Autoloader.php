<?php

/**
 * <p>Autoloader for pages and library classes.</p>
 * @param   string  $className  <p>The name of the class to load.</p>
 */
function Mjoelnir_Autoloader ($className) {
    $pathToFile = getPathToClass($className);

    if ($pathToFile !== false) {
        require_once $pathToFile;
    }
    else {
        Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('Could not load class "' . $className . '".', Mjoelnir_Logger_Abstract::ERR);
    }
}
spl_autoload_register('Mjoelnir_Autoloader');

/**
 * Autoloader for FPDF
 * @param   string  $sCLassName The name of the class to load.
 */
function Fpdf_Autoloader($sClassName) {
    if ($sClassName === 'FPDF') {
        require_once PATH_LIBRARY . 'Fpdf/fpdf.php';
    }
    if ($sClassName === 'TTFParser') {
        require_once PATH_LIBRARY . 'Fpdf/makefont/ttfparser.php';
    }
}
spl_autoload_register('Fpdf_Autoloader');

/**
 * Autoloader for tcpdf
 * @param   string  $sClassName The name of the class to load.
 */
function Tcpdf_Autoloader($sClassName) {
    if ($sClassName === 'TCPDF') {
        require_once PATH_LIBRARY . 'Tcpdf/tcpdf.php';
    }
}
spl_autoload_register('Tcpdf_Autoloader');


function Smarty_Autoloader($sClassName) {
//    define('SMARTY_DIR', PATH_LIBRARY . 'Smarty-3.1.16/libs/');

    if ($sClassName == 'Smarty' && file_exists(PATH_LIBRARY . 'Smarty-3.1.16/libs/Smarty.class.php')) {
        require_once(PATH_LIBRARY . 'Smarty-3.1.16/libs/Smarty.class.php');
    }
}
spl_autoload_register('Smarty_Autoloader');