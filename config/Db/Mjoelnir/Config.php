<?php

class Db_Mjoelnir_Config
{

    /**
     * <p>The data array saves the connection data for multiple environments. It hast to be given in the following
     * way:
     * 
     * $data = array(
     *      APPLICATION_ENV_DEVELOPMENT => array(
     *          'host' => 'localhost',
     *          'user' => 'root',
     *          'pass' => 'secret',
     *          'db' => 'database',
     *      },
     *      APPLICATION_ENV_STAGE => array(...)
     *      .
     *      .
     *      .</p>
     * @var array
     */
    protected $data = array(
        APPLICATION_ENV_DEVELOPMENT => array(
            'adapter'             => 'mysqli',
            'host'                => 'localhost',
            'user'                => 'root',
            'pass'                => 'root',
            'db'                  => 'mjoelnir',
            'collation'           => 'utf8'
        ),
        APPLICATION_ENV_STAGE => array(
            'adapter'            => 'mysqli',
            'host'               => 'localhost',
            'user'               => '',
            'pass'               => '',
            'db'                 => '',
            'collation'          => ''
        ),
        APPLICATION_ENV_LIVE => array(
            'adapter'   => 'mysqli',
            'host'      => 'localhost',
            'user'      => '',
            'pass'      => '',
            'db'        => '',
            'collation' => ''
        ),
    );

    /**
     * <p>The name of the environment where the database runs in.</p>
     * @var string
     */
    protected $environment = null;

    /**
     * <p>Tabel name constants.
     * ATTENTION: Not all tables are named till now.
     * ATTENTION: The placeholder under the given constants is needed by the model builder, to add further tables automatically.</p>
     */

    const TABLE_USER                 = 'user';
    const TABLE_USER_ROLE            = 'user_role';
    const TABLE_USER_USER_ROLE       = 'user_user_role';
    const TABLE_USER_ROLE_PERMISSION = 'user_role_permission';

    // MODEL_BUILDER_TABLE_NAME_CONST //


    public function __construct() {
        if (array_key_exists(APPLICATION_ENV, $this->data)) {
            $this->environment = APPLICATION_ENV;
        }
        else {
            throw new Mjoelnir_Db_Exception('No database configuration found for the given environment "' . APPLICATION_ENV . '".');
        }
    }

    #################
    ## GET METHODS ##
    #################

    /**
     * <p>Returns the host for the given type.</p>
     * @return  string
     */
    public function getHost() {
        return $this->data[$this->environment]['host'];
    }

    /**
     * <p>Returns the user for the given type.</p>
     * @return  string
     */
    public function getUser() {
        return $this->data[$this->environment]['user'];
    }

    /**
     * <p>Returns the pass for the given type.</p>
     * @return  string
     */
    public function getPass() {
        return $this->data[$this->environment]['pass'];
    }

    /**
     * <p>Returns the dbname for the given type.</p>
     * @return  string
     */
    public function getDb() {
        return $this->data[$this->environment]['db'];
    }

    /**
     * <p>Returns the collation to use for db connections.</p>
     * @return  string
     */
    public function getCollation() {
        return $this->data[$this->environment]['collation'];
    }

}
