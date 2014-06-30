<?php

/**
 * Description of {$sModelName}
 *
 * @author Michael Streb <mstreb@adzlocal.de>
 */
class {$sModelName} extends AbstractModel
{
    /**
     * Holds all loaded models.
     * @var array
     */
    public static $_instances    = array();

    /**
     * The table that holds teh data for the {$sModelName}.
     * @var string
     */
    protected static $_sTable   = Db_Adzlocal_Config::{$sTableNameConst};

    /**
     * Contains all data reffered to a singel user.
     * @var array
     */
    protected $_aData   = array({$sDataArray}
    );

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $_sUniqueIdField    = '{$sUniqueIdField}';

    /**
     * Contains regular expression to validate user data. If a user value needs no validation, just don´t name it.
     * @var array
     */
    protected $_aDataValidation  = array(
    );
    
    /**
     * Some data values are saved in a non-human-readable way. To make them human-readable, this array names the fields and the 
     * translations for each possible value.
     * @var array
     */
    protected $_aValueTranslation    = array(
    );

    /**
     * The constructor first loads the data for the model. If it is done, it loads a specific data model, depending on the user type.
     * @param   mixae   $mData  The given data can be a unique id, or an array with multiple values fitting on teh model.
     */
    protected function __construct($mData) {
        parent::__construct($mData);
    }

    /**
     * Checks wether the current instance can be deleted or not.
     * @param   int     $iId    The id of the model to delete.
     * @return  bool
     */
    public static function isDeleteAllowed($iId) {
        return false;
    }
    
    #################
    ## GET METHODS ##
    #################{$sGetMethods}
    
    #################
    ## SET METHODS ##
    #################{$sSetMethods}
}