<?php

/**
 * Description of Cookie
 *
 * @author  Michael Streb <michael.streb@michael-streb.de>
 */
class Mjoelnir_Auth_Adapter_Cookie implements Mjoelnir_Auth_Adapter_Interface
{
    /**
     * The parameter to validate for authentification.
     * @var str
     */
    static $sAuthParam   = 'loginHash';
    
    /**
     * Sets auth information for the user.
     * @param   string  $value  The value to set for authentification.
     * @return  bool
     */
    public function authenticate($value) {
        $aMatches    = array();
        preg_match('/(\.[a-z-]+\.[a-z]+)$/i', $_SERVER['HTTP_HOST'], $aMatches);
        $bRes = setcookie($this->getAuthParam(), $value, (time() + AUTH_EXPIRE), '/', $aMatches[1]);
        return $bRes;
    }

    /**
     * Checks if a user is already authed.
     * @return bool
     */
    public function isAuthed() {
        if (isset($_COOKIE[$this->getAuthParam()])) {
            return true;
        }
        return false;
    }

    /**
     * Return the value set during the authentification.
     * @return string
     */
    public function getAuthValue() {
        if ($this->isAuthed()) {
            return $_COOKIE[$this->getAuthParam()];
        }

        return null;
    }

    /**
     * Cancels teh authentification.
     * @return bool
     */
    public function cancel() {
        $aMatches    = array();
        preg_match('/(\.[a-z]+\.[a-z]+)$/i', $_SERVER['HTTP_HOST'], $aMatches);
        return setcookie($this->getAuthParam(), null, time() - 1, '/', $aMatches[1]);
    }
    
    /**
     * Returns the auth parameter name.
     * @return  string
     */
    protected function getAuthParam() {
        return (defined('AUTH_CUSTOM_PARAMETER')) ? AUTH_CUSTOM_PARAMETER : self::$sAuthParam;
    }
}