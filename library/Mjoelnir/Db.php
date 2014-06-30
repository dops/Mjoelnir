<?php

class Mjoelnir_Db
{
    /**
     * <p>This constant names the template used database adapter names.</p>
     * @var type
     */
    const ADAPTER_NAME_TEMPLATE = 'Mjoelnir_Db_Adapter_:adapterName:';
    
    /**
     * <p>This template is used for database configuration names.</p>
     */
    const CONFIG_NAME_TEMPLATE  = 'Db_:configName:_Config';


    /**
     * <p>The name of the requested database.</p>
     * @var string
     */
    protected $sDatabaseName = null;

    /**
     * <p>The environment where the database is running in.</p>
     * @var string
     */
    protected $sEnvironment = null;

    /**
     * <p>An array with singlton instances for each loaded database.</p>
     * @var array
     */
    public static $_instances = array();

    /**
     * <p>A db config.</p>
     * @var Db_Config
     */
    protected $oConfig  = null;

    /**
     * <p>Link id for database connection.</p>
     * @var integer|null
     */
    protected $oAdapterAbstract = null;

    /**
     * <p>The result id of the last executed statement.</p>
     * @var Result-ID
     */
    protected $aResult = null;

    /**
     * <p>The constructor takes a database name and tries to load its configuration. If it could be loaded, a connection to the database will be established. If the config
     * file could not be found, an exception of type Mjoelnir_Db_Exception.</p>
     * @param   string  $sDatabaseName  <p>The name of the database to connect to.</p>
     * @throws  Mjoelnir_Db_Exception
     */
    private function __construct($sDatabaseName) {
        $this->sDatabaseName = $sDatabaseName;
        
        $sConfigClassName = 'Db_' . ucfirst(strtolower($sDatabaseName)) . '_Config';
        
        if (class_exists($sConfigClassName)) {
            $this->oConfig = new $sConfigClassName();

            $this->connect();
        }
        else {
            throw new Mjoelnir_Db_Exception('Could not load config for database "' . $sDatabaseName . '".');
        }
    }

    /**
     * <p>Returns an instance of Mjoelnir_Db for the requested database. If no instance is available, one will be created. No redundat connections will be created. The 
     * configuration for the requested database will be loaded automatically. If the configuration could not be found an exception of type Mjoelnir_Db_Exception will
     * br thrown.</p>
     * @param   string  $adapter    <p>The name of the adapter to use for database operations.</p>
     * @return object
     */
    public static function factory($adapterName, $configName) {
        // Check if an instance for the given adapter and the given configuration already exists.
        if (!isset(self::$_instances[$adapterName . '-' . $configName])) {
            // Load config
            $configClass = str_replace(':configName:', str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($configName)))), self::CONFIG_NAME_TEMPLATE);
            try {
                $config = new $configClass();
            }
            catch (Config_Db_Exception $ex) {

            }

            // Load adapter
            $adapterClass = str_replace(':adapterName:', str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName)))), self::ADAPTER_NAME_TEMPLATE);
            try {
                self::$_instances[$adapterName . '-' . $configName] = new $adapterClass($config);
            }
            catch (Mjoelnir_Db_Adapter_Exception $ex) {

            }
        }

        return self::$_instances[$adapterName . '-' . $configName];
    }

    /**
     * <p>Tries to establish a connection to the requested database. If a connection already exists, it is used instead of crfeating a new one.</p>
     * @return bool
     */
    public function connect() {

    	if (!$this->connected()) {
//    		$this->oAdapterAbstract = parent::factory('mysqli',
//    			array (
//    				'host'           => $this->oConfig->getHost(),
//    				'username'       => $this->oConfig->getUser(),
//    				'password'       => $this->oConfig->getPass(),
//    				'dbname'         => $this->oConfig->getDb(),
//                    'charset'        => 'utf8',
//    				'driver_options' => array(MYSQLI_INIT_COMMAND => 'SET NAMES UTF8;')
//    			)
//    		);
    	}

        return true;
    }

  	/**
     * Gibt die aktuelle Link-ID zur pruefung zurueck.
     *
     * @return string
     */
	public function connected() {
		return !is_null($this->oAdapterAbstract);
	}

    /**
     * <p></p>
     * @param type $sMethod
     * @param type $mArgList
     * @return type
     * @throws Zend_Exception
     */
	public function __call($sMethod, $mArgList) {

		$sType = $this->_selectConnectionType(strtoupper($sMethod));

		if (method_exists($this->oAdapterAbstract, $sMethod)) {
			return call_user_func_array(array($this->oAdapterAbstract, $sMethod), $mArgList);
		}
        else {
			throw new Zend_Exception(__CLASS__ . '::' . __FUNCTION__ . ': ' . $sMethod . ' not found!');
		}
	}

    /**
     * Sendet einen SQL-String an die Datenbank.
     *
     * @param string $sQuery
     * @return string
     */
    public function query($sQuery, $aBind = array()) {
        $this->connect();
        
        $this->aResult = $this->oAdapterAbstract->query($sQuery, $aBind);

        return  $this->aResult;
    }

    /**
     * Gibt eine Zeile des letzten Ergebnisses als assoziatives Array zurueck.
     *
     * @param string $result_id
     * @return array
     */
    public function fetchAssoc($sQuery = null)
    {
        if (!is_null($sQuery)) {
            $this->aResult = $this->query($sQuery);
        }
        return $this->aResult->fetch();
    }

    /**
     * Returns the number of rows in a result.
     * @param   Mysqli_Result   $aResult A mysqli result object.
     * @return  int
     */
    public function numRows($aResult = null) {
        if (is_null($aResult)) {
            $aResult = $this->aResult;
        }

        return mysqli_num_rows($aResult);
    }
}
