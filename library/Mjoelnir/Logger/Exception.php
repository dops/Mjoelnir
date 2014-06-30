<?php

/**
 * Exception to be used for logging purposes
 */
class Mjoelnir_Logger_Exception extends Exception
{

    public function __construct($sMessage, $sCode = null, $sPrevious = null)
    {
        parent::__construct($sMessage, $sCode, $sPrevious);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}

