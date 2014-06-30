<?php

$iStartTime = microtime(true);

try {
    /**
     * Load config
     */
    include '../config/config.php';

    /*
     * Load autoloader
     */
    include PATH_LIBRARY . 'Autoloader.php';

    /**
     * Load function library.
     */
    include PATH_LIBRARY . 'Functions.php';

    $oBootstrap = new Bootstrap();
    $oBootstrap->load();
} catch (Exception $oEx) {
    if (APPLICATION_ENV === APPLICATION_ENV_DEVELOPMENT || APPLICATION_ENV === APPLICATION_ENV_STAGE) {
        echo '<h3>An exception of type "' . get_class($oEx) . '" has occured!</h3>';
        echo '<p>Message: ' . $oEx->getMessage() . '<br>File: ' . $oEx->getFile() . '<br>Line: ' . $oEx->getLine() . '</p>';
        echo '<p>' . nl2br($oEx->getTraceAsString()) . '</p>';
    }
}

$iEndTime = microtime(true);
$iRuntime = number_format($iEndTime - $iStartTime, 4);
if (APPLICATION_ENV == 'development') {
    echo 'Script Laufzeit: ' . $iRuntime . '<br />';
}