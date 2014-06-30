<?php

/**
 * Description of UserModel
 *
 * @author Michael Streb <mstreb@adzlocal.de>
 */
class UserModel extends AbstractModel
{

    /**
     * Holds all loaded user models.
     * @var array
     */
    public static $_instances = array();

    /**
     * The table that holds teh data for the UserModel.
     * @var string
     */
    protected static $_sTable = Db_Mjoelnir_Config::TABLE_USER;

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData = array(
        'id'                => null,
        'first_name'        => null,
        'last_name'         => null,
        'status'            => null,
        'role'              => null,
        'sales'             => null,
        'sem'               => null,
        'title'             => null,
        'login'             => null,
        'password'          => null,
        'email_account'     => null,
        'email_pw'          => null,
        'tel'               => null,
        'caller_phone_nr'   => null,
        'email'             => null,
        'snom_ip'           => null,
        'date_first_monday' => null,
        'login_hash'        => null,
        'time_insert'       => null,
        'time_update'       => null,
        'insert_user_id'    => null,
        'update_user_id'    => null
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField = 'user_id';

    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation = array(
    );

    /**
     * Some data values are saved in a non-human-readable way. To make them human-readable, this array names the fields and the
     * translations for each possible value.
     * @var array
     */
    protected $_aValueTranslation = array(
        'type' => array('Employee' => 'Mitarbeiter', 'Customer' => 'Kunde'),
    );

    /**
     * The constructor first loads the data for the model. If it is done, it loads a specific data model, depending on the user type.
     * @param   mixae   $mData  The given data can be a unique id, or an array with multiple values fitting on teh model.
     */
    protected function __construct($mData)
    {
        parent::__construct($mData);
    }

    /**
     * Checks wether the current instance can be deleted or not.
     * @param   int     $iId    The id of the model to delete.
     * @return  bool
     */
    public static function isDeleteAllowed($iId)
    {
        return false;
    }

    /**
     * Crypts a string.
     * @param   str $string The string to crypt.
     * @return type
     */
    public static function _crypt($string)
    {
        return md5(ENCRYPT_PREFIX . trim($string));
    }

    /**
     * Sicheres Passwort erzeugen
     *
     * @return string
     */
    public static function generatePassword()
    {
        $arr = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
        shuffle($arr);
        $str = str_shuffle(substr(implode('', $arr), 0, 6));

        return $str;
    }

    /**
     * Returns an instance of the currently logged in user, if one is logged in.
     * @return UserModel|boolean
     */
    public static function getCurrentUser() {
        $oDb   = Mjoelnir_Db::factory('mysqli', 'mjoelnir');
        $oAuth = Mjoelnir_Auth::getInstance();

        if ($oAuth->getAuthValue()) {
            if (!isset(self::$_instances['currentUserInstance']) || self::$_instances['currentUserInstance']->getLoginHash() != $oAuth->getAuthValue()) {
                $oSql  = $oDb->select()
                        ->from(self::$_sTable)
                        ->where('login_hash = ?', $oAuth->getAuthValue());
                $oRes  = $oSql->query();
                $aData = $oRes->fetch();

                if (isset($aData[self::$_sUniqueIdField])) {
                    $oUser = UserModel::getInstance($aData);
                    if (!is_null($oUser->getId())) {
                        self::$_instances['currentUserInstance'] = $oUser;
                    } else {
                        self::$_instances['currentUserInstance'] = NULL;
                    }
                } else {
                    $oAuth->cancel();
                    self::$_instances['currentUserInstance'] = NULL;
                }
            }
            return self::$_instances['currentUserInstance'];
        }

        return false;
    }

    /**
     * Fetches a user id using login data.
     * @param   str $login      The users login name.
     * @param   str $Spassword  The users uncrypted password.
     * @return  int
     */
    public static function getUserIdByLogin($login, $password)
    {
        $oDb = Mjoelnir_Db::getInstance();

        $oSql  = $oDb->select()
                ->from(Db_Mjoelnir_Config::TABLE_USER, self::$_sUniqueIdField)
                ->where('email = ?', $login)
                ->where('password = ?', $password);
        $oRes  = $oSql->query();
        $aData = $oRes->fetch();

        Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log(sprintf('$login: %s', $login), Mjoelnir_Logger_Abstract::INFO);

        if (isset($aData[self::$_sUniqueIdField])) {
            return (int) $aData[self::$_sUniqueIdField];
        }

        return false;
    }

    /**
     * Fetches a user id using login data.
     * @param   str $sLoginHash The users login hash.
     * @return  int
     */
    public static function getUserIdByLoginHash($sLoginHash)
    {
        $oDb = Mjoelnir_Db::getInstance();

        $oSql  = $oDb->select()
                ->from(Db_Mjoelnir_Config::TABLE_USER, self::$_sUniqueIdField)
                ->where('login_hash = ?', $sLoginHash);
        $oRes  = $oSql->query();
        $aData = $oRes->fetch();

        Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log(sprintf('$login: %s', $login), Mjoelnir_Logger_Abstract::INFO);

        if (isset($aData[self::$_sUniqueIdField])) {
            return (int) $aData[self::$_sUniqueIdField];
        }

        return false;
    }

    /**
     * Returns 1 or 0 if the user is active or not.
     * @return  int
     */
    public function getActiveFlag()
    {
        return ($this->aData['status'] == 'active') ? 1 : 0;
    }

    /**
     * Sets a new login hash to improve security.
     */
    public function renewLoginHash()
    {
        $this->aData['login_hash'] = $this->_crypt($this->aData['first_name'] . $this->aData['last_name'] . $this->aData['email'] . mt_rand(0, time()));
        $this->oDb->query('UPDATE ' . self::$_sTable . ' SET login_hash = "' . $this->aData['login_hash'] . '" WHERE ' . self::$_sUniqueIdField . ' = ' . $this->aData[self::$_sUniqueIdField]);
    }

    #################
    ## GET METHODS ##
    #################

    public function getFirstName()
    {
        return $this->aData['first_name'];
    }

    public function getLastName()
    {
        return $this->aData['last_name'];
    }

    public function getPassword()
    {
        return $this->aData['password'];
    }

    public function getEmail()
    {
        return $this->aData['email'];
    }

    public function getLoginHash()
    {
        return $this->aData['login_hash'];
    }

    #################
    ## SET METHODS ##
    #################

    public function setFirstName($sValue)
    {
        $this->aData['first_name'] = $sValue;
        return true;
    }

    public function setLastName($sValue)
    {
        $this->aData['last_name'] = $sValue;
        return true;
    }

    public function setPassword($sValue)
    {
        $this->aData['password'] = $sValue;
        return true;
    }

    public function setEmail($sValue)
    {
        $this->aData['email'] = $sValue;
        return true;
    }

}

