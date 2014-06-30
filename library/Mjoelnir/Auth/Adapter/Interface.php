<?php

/**
 * Defines an set of methods every auth adapter hast ot provide.
 *
 * @package Mjoelnir
 * @subpackage Auth
 * @author Michael Streb <michael.streb@topdeals.de>
 */
interface Mjoelnir_Auth_Adapter_Interface
{
    /**
     * Sets auth information for the user.
     * @param   string  $sValue  The value to set for authentification.
     * @return  bool
     */
    public function authenticate($sValue);

    /**
     * Checks if a user is already authed.
     * @return bool
     */
    public function isAuthed();

    /**
     * Return the value set during the authentification.
     * @return string
     */
    public function getAuthValue();

    /**
     * Cancels teh authentification.
     * @return bool
     */
    public function cancel();
}
?>
