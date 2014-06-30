<?php

/**
 * An abstract logger class defining basic functionality to be used by other logger types / sub classes (like fileLogger, ...).
 */
abstract class Mjoelnir_Logger_Abstract {
    /**
     * Log-Levels
     */

    const EMERG = 0;
    const ALERT = 1;
    const CRIT = 2;
    const ERR = 3;
    const WARN = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    protected $_aLogLevelTranslation = array(
        0 => 'EMERGENCY',
        1 => 'ALERT',
        2 => 'CRITICAL',
        3 => 'ERROR',
        4 => 'WARN',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG',
    );

    /**
     * map of currently accessible logger instances
     */
    private static $_aLoggerInstances = array();

    /**
     * method returns a logger appropriate to given logger name (currently just FileLogger instances)
     * @param unknown $sLoggername
     * @return unknown|FileLogger
     */
    public static function getLogger($sLoggername) {
        //check of logger already exists
        if (!isset(self::$_aLoggerInstances[$sLoggername])) { //not existing, create
            //check if given loggername exists as const
            if (defined($sLoggername)) {
                self::$_aLoggerInstances[$sLoggername] = new Mjoelnir_Logger_FileLogger(constant($sLoggername));
            } else {
                throw new Mjoelnir_Logger_Exception('Given loggername ' . $sLoggername . ' not defined in constants - logging won\'t work as expected');
            }
        }
        return self::$_aLoggerInstances[$sLoggername];
    }

    /**
     * L�scht einen Logger
     * @param String $sLoggername
     */
    public static function deleteLogger($sLoggername) {
        if (isset(self::$_aLoggerInstances[$sLoggername])) { //logger exists, delete
            unset(self::$_aLoggerInstances[$sLoggername]);
            echo "logger deleted" . $sLoggername;
        }
    }

    /**
     * Write out log message 
     * method must be implemented by sub classes
     * @param string $sMessage
     */
    public abstract function log($sMessage, $iLogLevel = 3);

    /**
     * make a debug backtrace and returns a StdClass
     * width:
     * 	-> class
     * 	-> function
     *
     * @return object $oCaller
     */
    protected function getCaller() {
        $oCaller = new StdClass;
        $oCaller->class = null;
        $oCaller->function = null;
        $aTrace = debug_backtrace();

        if (isset($aTrace[2])) {
            $aCaller = $aTrace[2];
            $oCaller->function = $aCaller['function'];

            if (isset($aCaller['class'])) {
                $oCaller->class = $aCaller['class'];
            }
        }

        return $oCaller;
    }

    /**
     * Sends an email if the log level is error or worse.
     * @param   string  $sMessage   The log message.
     * @param   integer $iLogLevel  The log level.
     * @return  boolean Returns always true.
     */
    protected function _sendMail($sMessage, $iLogLevel) {
        if ($iLogLevel <= 3 && APPLICATION_ENV != 'stage') {
            $sSubject = substr('SYSTEM STATE ' . $this->_aLogLevelTranslation[$iLogLevel] . ': ' . $sMessage, 0, 50);
            $sText = 'An error occured.

Level: ' . $this->_aLogLevelTranslation[$iLogLevel] . '

Message: ' . "\n\n" . $sMessage;

            $oMail = new Zend_Mail('UTF-8');
            $oMail->setFrom(APPLICATION_LOG_MAIL, 'adzLocal System');
            $oMail->setReplyTo(APPLICATION_LOG_MAIL);
            $oMail->addTo(APPLICATION_LOG_MAIL);
            $oMail->setSubject($sSubject);
            $oMail->setBodyText(utf8_encode($sText));
            $oMail->send();
        }

        return true;
    }

}