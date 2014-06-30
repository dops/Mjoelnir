<?php

/**
 * Provides several functions used for user authentification.
 *
 * @package Topdeals
 * @subpackage Auth
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class Mjoelnir_Auth implements Mjoelnir_Auth_Adapter_Interface
{
    /**
     * Singleton instance.
     * @var Mjoelnir_Auth
     */
    protected static $oInstance = null;

    /**
     * The configured adapter
     * @var str
     */
    protected $sAadapterName = null;

    /**
     * Defindes a default anthentification type
     * @var str
     */
    protected $sDefaultAdapterName  = 'Cookie';

    /**
     * An adapter to write and read auth information.
     * @var mixed
     */
    protected $oAdapter = null;


    protected function __construct() {
        $this->getAdapter();
    }

    /**
     * Returns a singleton instance.
     * @return Mjoelnir_Auth
     */
    public static function getInstance() {
        if (is_null(self::$oInstance)) {
            self::$oInstance    = new Mjoelnir_Auth();
        }

        return self::$oInstance;
    }


    protected function getAdapter() {
        $this->sAadapterName = (defined('AUTH_ADAPTER_NAME')) ? AUTH_ADAPTER_NAME : $this->sDefaultAdapterName;
        $sAdapterClassName   = 'Mjoelnir_Auth_Adapter_' . $this->sAadapterName;
        $this->oAdapter      = new $sAdapterClassName();
    }


    public function authenticate($sValue) {
        return $this->oAdapter->authenticate($sValue);
    }


    public function isAuthed() {
        return $this->oAdapter->isAuthed();
    }


    public function getAuthValue() {
        return $this->oAdapter->getAuthValue();
    }


    public function cancel() {
        return $this->oAdapter->cancel();
    }


    public function createPassword(UserModel $oUser, $iLength = 12, $bSendToUser = false) {
        $mPassword = false;

        if (!$oUser instanceof UserModel) {
            return $mPassword;
        }

        if ($iLength < 8) {
            $iLength = 8;
        } elseif ($iLength > 24) {
            $iLength = 24;
        }

        $a      = range('a', 'z');
        $aC     = range('A', 'Z');
        $n      = range(2, 9);
        $ignore = array('l', 'o', 'O', '0');

        $list   = array_merge($a, $aC, $n);
        $list   = array_diff($list, $ignore);
        sort($list);

        $list_cnt = count($list) - 1;
        for ($l = 0; $l < $iLength; $l++) {
            $mPassword .= $list[rand(0, $list_cnt)];
        }

        if (true === $bSendToUser) {
            mail(
                \UserModel::getCurrentUser()->getEmail(),
                'Neues Passwort fuer ' . $oUser->getFirstName() .' '. $oUser->getLastName(),
                'E: ' . $oUser->getEmail() . "\n" .
                'P: ' . $mPassword . "\n",
                'From: accounting@' . $_SERVER['HTTP_HOST']
            );
        }

        return $mPassword;
    }
}

?>
