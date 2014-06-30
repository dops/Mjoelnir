<?php

/**
 * Description of Exception
 *
 * @author Michael Streb <mstreb@adzlocal.de>
 */
class Mjoelnir_Db_Exception extends Exception
{
    public function __construct($sMessage = null, $iCode = null, $oPrevious = null) {
        parent::__construct($sMessage, $iCode, $oPrevious);
    }
}

?>
