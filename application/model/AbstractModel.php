<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractModel
 *
 * @author td-office
 */
abstract class AbstractModel
{
    /**
     * An instance of a db layer.
     * @var Mjoelnir_Db
     */
    protected $oDb  = null;

    /**
     * The table that holds the data for the model. Has to be set in child class.
     * @var string
     */
    protected static $sTable   = null;

    /**
     * The data array contains the model data. Has to be set in child class.
     * @var type
     */
    protected $aData    = array();

    /**
     * The unique id field is the field that holds the system-wide unique id for the model instance.
     * @var int
     */
    public static $sUniqueIdField  = null;

    /**
     * Contains regular expression to validate data. If a value needs no validation, just don´t name it. Can be set in child class.
     * @var array
     */
    protected $aDataValidation  = array();

    /**
     * Saves one error message to return it when AbstractModel::getError() is called.
     * @var str
     */
    protected $sError   = '';

    /**
     * An instance of Topdeals_Log
     * @var Mjoelnir_Log
     */
    protected $oLog = null;
    
    /**
     * This array names the allowed join types.
     * @var array
     */
    protected static $aAllowedNormalJoinTypes = array('inner', 'left', 'right', 'full', 'cross', 'natural');
    protected static $aAllowedUsingJoinTypes = array('inner', 'left', 'right', 'full');

    /**
     * <p>Constructor.</p>
     * @param   mixed   $aData   <p>$data contains either a model id or an array with model data.</p>
     */
    protected function __construct($aData) {
        $this->oDb  = Mjoelnir_Db::getInstance(DEFAULT_DB);
        $this->oLog = Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME);

        if (!is_null($aData)) {
            if (is_array($aData)) {
                $aOverflow  = array_diff_key($aData, $this->aData);

                if (count($aOverflow) > 0) {
                    foreach ($aOverflow as $sOverflowKey) {
                        unset($aData[$sOverflowKey]);
                    }
                }

                $aTmp   = array_replace($this->aData, $aData);
                if (!is_null($aTmp)) {
                    $this->aData    = $aTmp;
                }
            }
            else {
                $this->_readData($aData);
            }
        }
    }

    /**
     * Returns an instance of the model. If the model has already been loaded, a reference to this object will be returned. Otherwise a new instance will be created and returned.
     * @param   mixed                   $mData   $data could be either an id or an array with clearing data.
     * @return  AccountCategoryModel
     */
    public static function getInstance($mData = null) {
        $sCalledClass   = get_called_class();

        if (is_null($mData)) {
            return new $sCalledClass($mData);
        }
        else {
            if (is_array($mData)) {
                if (isset($mData[$sCalledClass::$_sUniqueIdField])) { $iInstanceId    = $mData[$sCalledClass::$_sUniqueIdField]; }
                else                                                { $iInstanceId    = null; }
            }
            else {
                $iInstanceId    = $mData;
            }

            if (!isset($sCalledClass::$_instances[$iInstanceId])) {
                $oClass                                     = new $sCalledClass($mData);
                $sCalledClass::$_instances[$iInstanceId]    = $oClass;
            }

            return $sCalledClass::$_instances[$iInstanceId];
        }
    }

    /**
     * Reads the data from the database.
     * @param   int $iId The id of the user to fetch the data fro.
     */
    protected function _readData($iId) {
        $sCalledClass   = get_called_class();

        $oSql = $this->oDb->select()
                ->from($sCalledClass::$_sTable, array_keys($this->aData))
                ->where($sCalledClass::$_sUniqueIdField . ' = ?', (int) $iId);
        $oRes = $oSql->query();
        $aData = $oRes->fetch();

        if (is_array($this->aData) && count($this->aData) > 0 && is_array($aData) && count($aData) > 0) {
            $this->aData    = array_merge($this->aData, $aData);

            if (property_exists($sCalledClass, '_aAdditionalData') && method_exists($sCalledClass, '_saveAdditionalData')) {
                $this->_readAdditionalData();
            }

            return true;
        }

        return false;
    }

    /**
     * Returns all records. If a limit and a start value is given, the result will be limited to this range.
     * @param   int     $iStart     The position to start reading from.
     * @param   int     $iLimit     The max number of results to return.
     * @param   array   $aFilter    An array naming multiple fields and values to filter the result. Always using equal matching.
     * @param   array   $aOrder     An array naming multiple fields and values to order the result. The key names the field and the value the direction.
     * @param   array   $aJoin      An array containing table names as key and join conditions as value. The condition could even be a string or an array.
     * @param   array   $sDataqbase If you want to execute the statement on another database, you can name it here.
     * @return  array
     */
    public static function getAll($iStart = null, $iLimit = null, $aFilter = array(), $aOrder = array(), $aJoin = array(), $sDatabase = null) {
        if (is_null($sDatabase)) { $sDatabase = DEFAULT_DB; }
        $oDb            = Mjoelnir_Db::factory('mysqli', 'mjoelnir');
        $oLogger        = Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME);
        $sCalledClass   = get_called_class();
return array('count' => 0, 'rows' => array());
        $oSelect    = $oDb->select()->from($sCalledClass::$_sTable, new Zend_Db_Expr('SQL_CALC_FOUND_ROWS ' . $sCalledClass::$_sTable . '.*'));

        // Add joins
        $aJoinTable = array();
        if (method_exists($sCalledClass, 'getJoinTable')) {
        	$aJoinTable	= $sCalledClass::getJoinTable();
        }
        if ($aJoin > 0){
        	$aJoinTable += $aJoin;
        }
        if (count($aJoinTable) > 0) {
        	self::_getJoins($oSelect, $aJoinTable);
        }

        // Add filters
        if (count($aFilter) > 0) {
            self::_getFilter($oSelect, $aFilter);
        }

        if (count($aOrder) > 0) {
            foreach ($aOrder as $field => $direction) {
                $oSelect->order($field . ' ' . $direction);
            }
        }

        if (is_null($iStart) && !is_null($iLimit))    { $oSelect->limit($iLimit); }
        if (!is_null($iStart) && !is_null($iLimit))   { $oSelect->limit($iLimit, $iStart); }
 
        $oLogger->log($oSelect->__toString(), Mjoelnir_Logger_Abstract::DEBUG);
        
        $oRes = $oSelect->query();
        
        $aReturn            = array('rows' => array(), 'count' => 0);
        $aTempResult        = $oRes->fetchAll();
        $aTempCount         = $oDb->select()->from(null, new Zend_Db_Expr('FOUND_ROWS() AS count'))->query()->fetchAll();
        $aReturn['count']   = $aTempCount[0]['count'];

        foreach ($aTempResult as $aData) {
            $oInstance                              = $sCalledClass::getInstance($aData);
            $aReturn['rows'][$oInstance->getId()]   = $oInstance;
        }

        return $aReturn;
    }
    
    /**
     * The getJoinTable method is used to join foreign tables to the current statement. For this three dimesions are the minimal need. The key of the first dimension names the type of
     * join you would like to add, eg. left or inner. The key of the second dimension names the table which will be joined to the statement. The third dimension has two keys
     * "mCondition" and "aFields". mCondition can either be a string or an array. If its a string, then both tables must have a field whos name equals the string, if its an array,
     * then the first element names the field of the current table, and the second element names the field of the foriegn table. aFields is an array with all fields that should be
     * fetched form the foriegn table. If the kex of a field is a string, it will be used as alias for the filed.
     *
     * Example:
     *
     * The following examples is, to get user addresses, depending on the user. Note that the where condition is not show.
     *
     * If both tables have an equal field:
     * $_aJoinTable = array(
     *     'inner' => array(
     *         'user_address' => array(
     *             'mCondition' => 'user_id',
     *             'aFields' => array(),
     *     ),
     * );
     *
     * If bothe tables have different fields:
     * $_aJoinTable = array(
     *     'inner' => array(
     *         'user_address' => array(
     *             'mCondition' => array(
     *                 'id',
     *                 'user_id',
     *             ),
     *             'aFields' => array(
     *                 'alias' => 'fieldName',
     *         ),
     *     ),
     * );
     *
     * Possible join types are: inner, left, right, full, cross, natural
     * @var array
     */
    public static function getJoinTable() {
    	return array();
    }

    /**
     * Applies different filters to the database request.
     * @param   Zend_Db_Select  $oSelect    A Zend select object.
     * @param   array           $aParams    A array containing the fields and the comparision values.
     */
    protected static function _getFilter($oSelect, $aParams) {
        foreach ($aParams as $sFieldName => $aConditions) {
            if (is_array($aConditions)) {
                foreach ($aConditions as $sComparisonOperator => $mValue) {
                    // Equal
                    if ($sComparisonOperator === 'eq') {
                        $oSelect->where($sFieldName . ' = ?', $mValue);
                    }

                    // Not equal
                    if ($sComparisonOperator === 'neq') {
                        $oSelect->where($sFieldName . ' != ?', $mValue);
                    }

                    // Like
                    if ($sComparisonOperator === 'like') {
                        $oSelect->where($sFieldName . ' LIKE ?', $mValue);
                    }
                    // Not like
                    if ($sComparisonOperator === 'nlike') {
                        $oSelect->where($sFieldName . ' NOT LIKE ?', '%' . $mValue . '%');
                    }

                    // Lighter
                    if ($sComparisonOperator === 'l') {
                        $oSelect->where($sFieldName . ' < ?', $mValue);
                    }

                    // Lighter than
                    if ($sComparisonOperator === 'lt') {
                        $oSelect->where($sFieldName . ' <= ?', $mValue);
                    }

                    // Greater
                    if ($sComparisonOperator === 'g') {
                        $oSelect->where($sFieldName . ' > ?', $mValue);
                    }

                    // Greater than
                    if ($sComparisonOperator === 'gt') {
                        $oSelect->where($sFieldName . ' >= ?', $mValue);
                    }

                    // Between
                    if ($sComparisonOperator === 'bt') {
                        $mStart = (is_numeric($mValue[0])) ? $mValue[0] : '\'' . $mValue[0] . '\'';
                        $mEnd   = (is_numeric($mValue[0])) ? $mValue[1] : '\'' . $mValue[1] . '\'';
                        $oSelect->where($sFieldName . ' ?', new Zend_Db_Expr('BETWEEN ' . $mStart . ' AND ' . $mEnd));
                    }

                    // In
//                    if ($sComparisonOperator === 'in') {
//                        $oSelect->where($sFieldName . ' IN (?)', implode(', ', array_map(function($tmp) { return '"' . $tmp .'"'; }, $mValue)));
//                    }
                    if ($sComparisonOperator === 'in') {
                        if (count($mValue) > 0) {
                            $oSelect->where(new Zend_Db_Expr($sFieldName . ' IN (' . implode(', ', array_map(function($tmp) { if (is_numeric($tmp)) { return $tmp; } else { return '"' . $tmp .'"'; }}, $mValue)) . ')'));
                        }
                        else {
                            $oSelect->where(new Zend_Db_Expr($sFieldName . ' IN ("")'));
                        }
                        
                    }
                    // regexp
                    if ($sComparisonOperator === 'regexp') {
                    	$oSelect->where($sFieldName . ' REGEXP ?', $mValue);
                    }
                }
            }
            else {
                // Is null
                if ($aConditions === 'in') {
                    $oSelect->where(new Zend_Db_Expr($sFieldName . ' IS NULL'));
                }
                
                // Is not null
                elseif ($aConditions === 'inn') {
                    $oSelect->where(new Zend_Db_Expr($sFieldName . ' IS NOT NULL'));
                }

                // If nothing matches, it seems to be straight sql
                else {
                    $oSelect->where(new Zend_Db_Expr($aConditions));
                }
            }
        }
    }
    
    /**
     * Adds join condition given by the child class to the statement.
     * @param   Zend_Db_Select  $oSelect    The Zend db select instance.
     * @throws  Mjoelnir_Db_Exception
     */
    private static function _getJoins($oSelect, $aJoinTable) {
        $sCalledClass   = get_called_class();
        
//        foreach (self::$_aJoinTable as $sTable => $mJoinCondition) {
        foreach ($aJoinTable as $sJoinType => $aJoinTable) {
//            foreach ($aJoinTable as $sTable => $mJoinCondition) {
            foreach ($aJoinTable as $sTable => $aJoinData) {
                if (is_string($aJoinData['mCondition'])) {
                    if (in_array($sJoinType, self::$aAllowedUsingJoinTypes)) {
                        $sJoinMethod    = 'join' . ucfirst(strtolower($sJoinType)) . 'Using';
                        $sJoinCondition = $aJoinData['mCondition'];
                    }
                }
                elseif (is_array($aJoinData['mCondition'])) {
                    if (in_array($sJoinType, self::$aAllowedNormalJoinTypes)) {
                        $sJoinMethod    = 'join' . ucfirst(strtolower($sJoinType));
                        $sJoinCondition = $aJoinData['mCondition'][0] . ' = ' . $aJoinData['mCondition'][1];
                    }
                }
                else {
                    throw new Mjoelnir_Db_Exception('The given join conditions are not valid: ' . print_r($aJoinData['mCondition'], true));
                }

                if (isset($sJoinMethod)) {
                    $oSelect->$sJoinMethod($sTable, $sJoinCondition, $aJoinData['aFields']);
                }
                else {
                    Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('The table "' . $sTable . '" could not be joined, because the given join type "' . $sJoinType . '" is not valid!', Mjoelnir_Log::ERR);
                }
            }
        }
    }

    /**
     * Saves the permission data.
     * @return bool
     */
    public function save() {
        $sCalledClass   = get_called_class();
        $iUserId 		= \UserModel::getCurrentUser()->getId();
        $iNow           = time();
        $aInsertData    = array();
        $aUpdateData    = array();
        $iId            = $this->aData[$sCalledClass::$_sUniqueIdField];
        
        foreach ($this->aData as $sFieldname => $mValue) {
            switch ($sFieldname) {
                case $sCalledClass::$_sUniqueIdField:
                    if (is_null($mValue)) {
                       $aInsertData[]  = $sFieldname . ' = NULL';
                    }
                    else {
                        $aInsertData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($mValue);
                    }
                    break;

                case 'time_insert':
                    if (is_null($this->aData[$sCalledClass::$_sUniqueIdField])) {
                        $this->aData['time_insert']    = $iNow;
                    }
                    $aInsertData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($iNow);
                    break;

                case 'time_update':
                    if (is_null($this->aData[$sCalledClass::$_sUniqueIdField])) {
                        $this->aData['time_update']    = $iNow;
                    }
                    $aUpdateData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($iNow);
                    break;
				
                case 'insert_user_id':
                    if (is_null($this->aData[$sCalledClass::$_sUniqueIdField])) {
                            $this->aData['insert_user_id']    = $iUserId;
                    }
                    $aInsertData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($iUserId);
                    break;

                case 'update_user_id':
                    if (is_null($this->aData[$sCalledClass::$_sUniqueIdField])) {
                            $this->aData['update_user_id']    = $iUserId;
                    }
                    $aUpdateData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($iUserId);
                    break;
                
                default:
                    if (is_null($mValue)) {
                        if (!$this->_noInserWithNull($sFieldname)) {
                            $aInsertData[]  = '`' . $sFieldname . '` = NULL';
                            $aUpdateData[]  = '`' . $sFieldname . '` = NULL';
                        }
                    }
                    else {
                        $aInsertData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($mValue);
                        $aUpdateData[]  = '`' . $sFieldname . '` = ' . $this->oDb->quote($mValue);
                    }
            }
        }

        $sSql   = '
            INSERT INTO
                ' . $sCalledClass::$_sTable . '
            SET
                ' . implode(', ', $aInsertData) . '
            ON DUPLICATE KEY UPDATE
                ' . $sCalledClass::$_sUniqueIdField . ' = LAST_INSERT_ID(' . $sCalledClass::$_sUniqueIdField . '),
                ' . implode(', ', $aUpdateData) . '
        ';

        $this->oLog->log($sSql, Mjoelnir_Logger_Abstract::DEBUG);
        
        try {
            $this->oDb->query($sSql);
        }
        catch (Zend_Db_Statement_Exception $e) {
            $this->oLog->log($e->getMessage());
            $this->oLog->log($sSql, Mjoelnir_Logger_Abstract::CRIT);
            $this->oLog->log($e->getTraceAsString());
        }

        if (is_null($iId)) {
            $this->aData[$sCalledClass::$_sUniqueIdField]  = $this->oDb->lastInsertId();
        }

        // Save additional data.
        if (property_exists($sCalledClass, '_aAdditionalData') && method_exists($sCalledClass, '_saveAdditionalData')) {
            $this->_saveAdditionalData();
        }

        return true;
    }

    /**
     * Returns true or false depending on if a field might be set ot null or not.
     * @param   str $sFieldname The name of the field to check.
     * @return  bool
     */
    protected function _noInserWithNull($sFieldname) {
        if (isset($this->_aNoInsertWithNull) && array_search($sFieldname, $this->_aNoInsertWithNull) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Deletes the category.
     * @return  bool
     */
    public static function delete($iId) {
        $oDb                = Mjoelnir_Db::getInstance();
        $sCalledClass       = get_called_class();
        $bIsDeleteAllowed   = $sCalledClass::isDeleteAllowed($iId);
        
        if (true === $bIsDeleteAllowed) {
            $sSql   = 'DELETE FROM ' . $sCalledClass::$_sTable . ' WHERE ' . $sCalledClass::$_sUniqueIdField . ' = ' . (int) $iId;
            $oStmt  = $oDb->query($sSql);
            
            if ($oStmt->rowCount() === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates the given value, and return true if it is, or flase if it´s not.
     * @param   str     $sName   The name of the value to validate.
     * @param   mixed   $mValue  The value to validate.
     * @return  bool
     */
    public function valueIsValid($sName, $mValue) {
        if (
            (isset($this->aDataValidation[$sName]) && preg_match($this->aDataValidation[$sName], $mValue))
            || !isset($this->aDataValidation[$sName])
        ) {
            $bReturn    = true;
        }
        else {
            $bReturn    = false;
        }
        
        $this->oLog->log($sName . ': "' . $mValue . '" -> "' . (isset($this->aDataValidation[$sName]) ? $this->aDataValidation[$sName] : 'nothing') . '" = ' . (($bReturn === true) ? 'true' : 'false'), Mjoelnir_Logger_Abstract::DEBUG);
        return $bReturn;
    }

    /**
     * Searches for a value translation. If found, the translation will be removed, otherwise just the original value is given back.
     * @param   str     $sParamName The name of the parameter.
     * @param   mixed   The parameter value. It can be an integer or a string.
     * @return  mixed
     */
    protected function _translateValue($sParamName, $mValue) {
        if (!is_array($mValue) && property_exists($this, '_aValueTranslation')) {
            if (isset($this->_aValueTranslation[$sParamName]) && isset($this->_aValueTranslation[$sParamName][$mValue])) {
                return $this->_aValueTranslation[$sParamName][$mValue];
            }
        }

        return $mValue;
    }


    #################
    ## GET METHODS ##
    #################

    /**
     * Returns the user role id.
     * @return int
     */
    public function getId() {
        $sCalledClass   = get_called_class();
        return (int) $this->aData[$sCalledClass::$_sUniqueIdField];
    }

    /**
     * Returns the insert timestamp.
     * @return  int
     */
    public function getTimeInsert() {
        return (int) $this->aData['time_insert'];
    }

    /**
     * Returns the update timestamp.
     * @return  int
     */
    public function getTimeUpdate() {
        return (int) $this->aData['time_update'];
    }
    
    /**
     * Returns the id of the id of the user who inserted the row the first time. If $bReturnObject is set to true, a user model is returned instead.
     * @param   boolean $bReturnObject  If set to true, a user model is returned instead of the user id.
     * @return  integer|UserModel|CustomerModel
     */
    public function getInsertUserId($bReturnObject = false) {
        if ($bReturnObject === true) {
            $sClassName = (APPLICATION_NAME == 'Kundencenter')  ? 'CustomerModel'   : 'UserModel';
            return $sClassName::getInstance($this->aData['insert_user_id']);
        }
        return $this->aData['insert_user_id'];
    }
    
    /**
     * Returns the id of the id of the user who edited the row the last time. If $bReturnObject is set to true, a user model is returned instead.
     * @param   boolean $bReturnObject  If set to true, a user model is returned instead of the user id.
     * @return  integer|UserModel|CustomerModel
     */
    public function getUpdateUserId($bReturnObject = false) {
        if ($bReturnObject === true) {
            $sClassName = (APPLICATION_NAME == 'Kundencenter')  ? 'CustomerModel'   : 'UserModel';
            return $sClassName::getInstance($this->aData['update_user_id']);
        }
        return $this->aData['update_user_id'];
    }

    /**
     * Returns a saved error message.
     * @return type
     */
    public function getError() {
        return $this->sError;
    }
}

?>
