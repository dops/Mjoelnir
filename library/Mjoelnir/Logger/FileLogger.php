<?php
/**
 * A logger capable of writing log message to a log file
 */

class Mjoelnir_Logger_FileLogger extends Mjoelnir_Logger_Abstract
{
    /**
     * A log file handle.
     * @var Ressource
     */
    private $_hFile = null;
    
    public function __construct($sLogFile) {
        try {
            $this->_hFile = fopen($sLogFile, 'a');
        } catch (Exception $oEx) {
            exit ('Log Dircectory not writable - exiting');
        }
    }

    public function __destruct () {
        if (!is_null($this->_hFile) AND $this->_hFile !== false) {
            fclose($this->_hFile);
        }
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
}