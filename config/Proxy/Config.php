<?php

class Proxy_Config
{
    /**
     * A list of proxy servers with ip and port.
     * @var array
     */
    static protected $_aList   = array(
//        array('ip' => '64.120.10.29', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
        array('ip' => '147.255.162.98', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
//        array('ip' => '23.19.132.57', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
//        array('ip' => '64.120.10.26', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
        array('ip' => '147.255.162.234', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
        array('ip' => '147.255.162.225', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
//        array('ip' => '64.120.10.30', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
//        array('ip' => '64.120.10.28', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
//        array('ip' => '23.19.132.56', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
        array('ip' => '147.255.162.47', 'port' => 80, 'username' => 'proxymania', 'password' => 'letmeproxy'),
    );
    
    /**
     * Suffles the server list and returns the first entry.
     * @return  array
     */
    static public function getRandomServer() {
        shuffle(self::$_aList);
        return current(self::$_aList);
    }
    
    /**
     * Returns all configured proxies.
     * @return array
     */
    static public function getAll() {
        return self::$_aList;
    }
}