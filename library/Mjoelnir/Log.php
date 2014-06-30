<?php

class Mjoelnir_Log
{
    /**
     * A singelton instance of Mjoelnir_Log.
     * @var Mjoelnir_Log
     */
    protected static $_oInstance = null;

    /**
     * A log file hanlde.
     * @var Ressource
     */
    protected $_hFile = null;
    
    /**
     * Log-Levels
     */
    const EMERG     = 0;
    const ALERT     = 1;
    const CRIT      = 2;
    const ERR       = 3;
    const WARN      = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;
    
    protected $_aLogLevelTranslation    = array(
        0   => 'EMERGENCY',
        1   => 'ALERT',
        2   => 'CRITICAL',
        3   => 'ERROR',
        4   => 'WARN',
        5   => 'NOTICE',
        6   => 'INFO',
        7   => 'DEBUG',
    );

    public function __construct() {
        try {
            $this->_hFile = fopen(PATH_LOG . 'application.log', 'a');
        } catch (Exception $oEx) {
            exit ('Log Dircectory not writable - exiting');
        }
    }

    public function __destruct () {
        if (!is_null($this->_hFile) AND $this->_hFile !== false) {
            fclose($this->_hFile);
        }
    }
    

    public static function getInstance ()
    {
        if (is_null(self::$_oInstance)) {
            self::$_oInstance = new Mjoelnir_Log ();
        }

        return self::$_oInstance;
    }

    /**
     * Writes the log message to the file
     * @param string $sMessage
     */
    public function log($sMessage, $iLogLevel = 3) {
        // to avoid errors, the log level constant will be set to default if it is not defined
        if (!defined('LOG_LEVEL'))  { define('LOG_LEVLE', $iLogLevel); }
        
    	$oCaller = $this->getCaller();
        
    	if (!is_null($this->_hFile) && $iLogLevel <= LOG_LEVEL) {
            fputs ($this->_hFile, date('[Y-m-d H:i:s] ') . '[' . $this->_aLogLevelTranslation[$iLogLevel] . '] ' . sprintf('%s::%s() - ', $oCaller->class, $oCaller->function). utf8_encode($sMessage) . "\n");
        }
        
        $this->_sendMail($sMessage, $iLogLevel);
    }

    /**
     * make a debug backtrace and returns a StdClass
     * width:
     * 	-> class
     *	-> function
     *
     * @return object $oCaller
     */
    private function getCaller() {
        $oCaller            = new StdClass;
        $oCaller->class     = null;
        $oCaller->function  = null;
        $aTrace             = debug_backtrace();
        
        if (isset($aTrace[2])) {
            $aCaller            = $aTrace[2];
            $oCaller->function  = $aCaller['function'];

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
        if ($iLogLevel <= 3) {
            $sSubject   = substr('SYSTEM STATE ' . $this->_aLogLevelTranslation[$iLogLevel] . ': ' . $sMessage, 0, 50);
            $sText      = 'An error occured.
                
Level: ' . $this->_aLogLevelTranslation[$iLogLevel] . '
    
Message: ' . "\n\n" . $sMessage;
            
            $oMail  = new Zend_Mail('UTF-8');
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