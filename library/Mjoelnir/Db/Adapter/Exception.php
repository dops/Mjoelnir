<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Mjoelnir_Db_Adapter_Exception extends Mjoelnir_Db_Exception
{
    public function __construct($sMessage = null, $iCode = null, $oPrevious = null) {
        parent::__construct($sMessage, $iCode, $oPrevious);
    }
}