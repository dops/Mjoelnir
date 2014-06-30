<?php

/**
 * Topdeals_Acl
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class Mjoelnir_Acl
{
    /**
     * An array containing the controllers and actions that are granted or denied for the current user.
     * Example:
     * array(
     *   'User' => array(
     *     'edit' => true,
     *     'delete' => false
     *   )
     * )
     * @var array
     */
    protected $_aPermissions = array();

    /**
     * To ignore permission requests isAdmin can be set to true. All request if use is allowed to do anything or if he has a right will be returned with true.
     * @var bool
     */
    protected $_bIsAdmin = false;

    /**
     * Singleton instance.
     * @var Mjoelnir_Acl
     */
    protected static $_instance = null;
    
    /**
     * Returns all given Permission formated as a valid array in a string.
     * @return  string
     */
    public function __toString() {
        return var_export($this->_aPermissions, true);
    }

    /**
     * Creates and returns a singlton instance of Topdeals_Acl.
     * @return Mjoelnir_Acl
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance    = new Mjoelnir_Acl();
        }

        return self::$_instance;
    }
    
    /**
     * Adds a permission.
     * @param   UserRolePermissionModel $oPermission A permission object.
     * @return  bool
     */
    public function addPermission(UserRolePermissionModel $oPermission) {
        if (!isset($this->_aPermissions[$oPermission->getApplication()])) {
            $this->_aPermissions[$oPermission->getApplication()]    = array();
        }

        if (!isset($this->_aPermissions[$oPermission->getApplication()][$oPermission->getController()])) {
            $this->_aPermissions[$oPermission->getApplication()][$oPermission->getController()]    = array();
        }

        if (!isset($this->_aPermissions[$oPermission->getApplication()][$oPermission->getController()][$oPermission->getAction()])) {
            $this->_aPermissions[$oPermission->getApplication()][$oPermission->getController()][$oPermission->getAction()]   = (bool) $oPermission->getAllow();
        }

        return true;
    }

    /**
     * Checks if a user could be able to call the given controller and action.
     * @param   str $sApplication   The name of the application to access.
     * @param   str $sController    The name of the controller to access.
     * @param   str $sAction        The name of the action to access.
     * @return  bool    Returns true if the controler and the action are allowed for the user. In all other cases (e.g. nothing ist set) false is returned.
     */
    public function hasAccess($sApplication, $sController, $sAction) {
        if ($this->_bIsAdmin) {
            return true;
        }

        // If the user has necessary permissions.
        if (isset($this->_aPermissions[$sApplication][$sController][$sAction])) {
            return $this->_aPermissions[$sApplication][$sController][$sAction];
        }

        return null;
    }

    /**
     * Checks if the user is allowed to access the requested page right now.
     * @param   str $sApplication   The name of the application to access.
     * @param   str $sController    The name of the controller to access.
     * @param   str $actrionName    The name of the action to access.
     * @return  bool    Returns true or false if something is set for the given controller and action.
     * @throws  Mjoelnir_Acl_Exception
     */
    public function isAllowed($sApplication, $sController = null, $sAction = null) {
        // The Admin has god mode. :)
        if ($this->_bIsAdmin) {
            return true;
        }
        
        // The user is allways allowed to enter the default page/action.
        if ($sController === DEFAULT_CONTROLLER && $sAction === DEFAULT_ACTION) {
            return true;
        }

        // If the user has necessary permissions.
        if (isset($this->_aPermissions[$sApplication][$sController][$sAction])) {
            return $this->_aPermissions[$sApplication][$sController][$sAction];
        }

        return false;
    }

    /**
     * Sets the admin flag to true.
     * @return bool
     */
    public function setAsAdmin() {
        $this->_bIsAdmin = true;
        return true;
    }

    /**
     * Sets the admin flag to false.
     * @return bool
     */
    public function setAsNoneAdmin() {
        $this->_bIsAdmin = false;
        return true;
    }
}

?>
