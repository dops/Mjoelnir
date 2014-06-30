<?php

/**
 * UserRolePermissionModel
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserRolePermissionModel extends AbstractModel
{
    /**
     * Holds all loaded user models.
     * @var array
     */
    protected static $_instances    = array();

    /**
     * The table that holds the data for the model.
     * @var string
     */
    protected static $_sTable   = 'user_role_permission';

    /**
     * The data array contains the model data.
     * @var type
     */
    protected $_aData    = array(
        'user_role_permission_id'   => null,
        'user_role_id'              => null,
        'application'               => null,
        'controller'                => null,
        'action'                    => null,
        'allow'                     => null,
        'time_insert'               => null,
        'time_update'               => null,
        'insert_user_id'            => null,
        'update_user_id'            => null
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField  = 'user_role_permission_id';

    /**
     * Contains regular expression to validate data. If a value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
        'user_role_id'  => '/[0-9]+/',
        'controller'    => '/[a-z]+/i',
        'action'        => '/[a-z]+/i',
    );

    protected function __construct($mData) {
        parent::__construct($mData);
    }

    /**
     * Checks wether teh current instance can be deleted or not.
     * @param   int     $iId    The id of the model to delete.
     * @return  bool
     */
    public static function isDeleteAllowed($iId) {
        return true;
    }

    /**
     * Deletes all permissions having the given application, controller and action.
     * @param   str     $application    The name of the application.
     * @param   str     $controller     The name of the controller.
     * @param   str     $action         The nam of the action.
     * @return  bool
     */
    public static function deleteByApplicationControllerAction($application, $controller, $action) {
        $oDb = Mjoelnir_Db::getInstance();
        $oDb->delete(Db_Adzlocal_Config::TABLE_USER_ROLE_PERMISSION, 'application = "' . $application . '" AND controller = "' . $controller . '" AND action = "' . $action . '"');
        return true;
    }

    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the user role id.
     * @return int
     */
    public function getUserRoleId() {
        return (int) $this->aData['user_role_id'];
    }

    /**
     * Returns the user role application.
     * @return str
     */
    public function getApplication() {
        return $this->aData['application'];
    }

    /**
     * Returns the user role controller.
     * @return str
     */
    public function getController() {
        return $this->aData['controller'];
    }

    /**
     * Returns the user role action.
     * @return str
     */
    public function getAction() {
        return $this->aData['action'];
    }

    /**
     * Returns the user role allow.
     * @return bool
     */
    public function getAllow() {
        // Admin role (1) has access always.
        if ($this->getUserRoleId() === 1) {
            return true;
        }
        return (bool) $this->aData['allow'];
    }
    
    public function getInsertUserId() {
    	return $this->aData['insert_user_id'];
    }
    
    public function getUpdateUserId() {
    	return $this->aData['update_user_id'];
    }

    #################
    ## SET METHODS ##
    #################


    /**
     * Sets the permissions user role id.
     * @param   int     $value  The user role id.
     * @return  bool
     */
    public function setUserRoleId($value) {
        if ($this->valueIsValid('user_role_id', $value)) {
            $this->aData['user_role_id'] = $value;
            return true;
        }

        $this->sError   = 'Die angegebene Benutzerrollen-ID entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->aDataValidation['user_role_id']);

        return false;
    }

    /**
     * Sets the permissions application.
     * @param   str     $value  The user role permission application.
     * @return  bool
     */
    public function setApplication($value) {
        if ($this->valueIsValid('application', $value)) {
            $this->aData['application'] = $value;
            return true;
        }

        $this->sError   = 'Der angegebene Anwendungsname entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->aDataValidation['application']);

        return false;
    }

    /**
     * Sets the permissions controller.
     * @param   str     $value  The user role permission controller.
     * @return  bool
     */
    public function setController($value) {
        if ($this->valueIsValid('controller', $value)) {
            $this->aData['controller'] = $value;
            return true;
        }

        $this->sError   = 'Der angegebene Controller-Name entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->aDataValidation['controller']);

        return false;
    }

    /**
     * Sets the permissions action.
     * @param   str     $value  The user role permission action.
     * @return  bool
     */
    public function setAction($value) {
        if ($this->valueIsValid('action', $value)) {
            $this->aData['action'] = $value;
            return true;
        }

        $this->sError   = 'Der angegebene Aktions-Name entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->aDataValidation['action']);

        return false;
    }

    /**
     * Sets the permissions allow.
     * @param   str     $value  The user role permission allow.
     * @return  bool
     */
    public function setAllow($value) {
        if ($this->valueIsValid('allow', $value)) {
            $this->aData['allow'] = (bool) $value;
            return true;
        }

        $this->sError   = 'Die angegebene Freigabe entspricht nicht den Vorgaben. Vorgabe: ' . str_replace('/', '', $this->aDataValidation['allow']);

        return false;
    }
    
    public function setInsertUserId($mValue) {
    	if ($this->valueIsValid('insert_user_id', $mValue)) {
    		$this->aData['insert_user_id'] = $mValue;
    		return true;
    	}
    	return false;
    }
    
    public function setUpdateUserId($mValue) {
    	if ($this->valueIsValid('update_user_id', $mValue)) {
    		$this->aData['update_user_id'] = $mValue;
    		return true;
    	}
    	return false;
    }
}