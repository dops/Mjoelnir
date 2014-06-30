<?php

namespace Dev;

class PtiForumReaderController extends \Mjoelnir_Controller_Abstract
{

    const BASE_URL = 'http://www.razyboard.com/system/';
    const LOGIN_URL = 'http://www.razyboard.com/system/index.php?id=slang&profil=true';
    const INDEX_URI = 'user_slang.html';
    const USERNAME = 'dops';
    const PASSWORD = 'gonzoo';

    protected $cookieFile = null;
    protected $cookieJar = null;

    protected $curlHandle = null;

    public function __construct() {
        parent::__construct();

        $this->cookieFile = PATH_USERFILES . 'PtiForumReader.cookie.txt';
        $this->cookieJar = PATH_USERFILES . 'PtiForumReader.cookie.jar';
    }

    public function indexAction() {
        $this->curlHandle = curl_init();

        $this->login();
        $this->crawlStartPage();
    }


    protected function login() {
        $aParams = array(
            'username_local' => self::USERNAME,
            'passwort_local' => self::PASSWORD,
        );

        $sResult = $this->doRequest('index.php?id=slang&profil=true', $aParams);
    }


    protected function crawlStartPage() {
        $sResult = $this->doRequest(self::INDEX_URI);

        preg_match_all('/(forum-slang-[a-z0-9-]+\.html)/i', $sResult, $aMatches);

        \Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('Found ' . count($aMatches[0]) . ' topic pages.', \Mjoelnir_Logger_Abstract::INFO);

//        var_dump($aMatches);

        foreach ($aMatches[1] as $sUri) {
            $this->renderTopicPage($sUri);
            break;
        }
    }

    protected function renderTopicPage($sUri) {
        $sResult = $this->doRequest($sUri);

        preg_match_all('/(morethread-[a-z0-9-]+\.html)">[a-z]/i', $sResult, $aMatches);

        \Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('Found ' . count($aMatches[0]) . ' thread pages.', \Mjoelnir_Logger_Abstract::INFO);

//        var_dump($aMatches);
        foreach ($aMatches[1] as $sUri) {
            $this->renderThreadPage($sUri);
            break;
        }

        // search for older pages
        preg_match_all('/(forum2--slang-[0-9-]+\.html)/i', $sResult, $aMatches);

//        var_dump($aMatches);
//
//        foreach ($aMatches[1] as $sUri) {
//            $this->renderTopicPage($sUri);
//        }
    }

    protected function renderThreadPage($sUri) {
        $sResult = $this->doRequest($sUri);

        // Fetch posts
        $oPosts = simplexml_load_string($sResult);
        var_dump($oPosts->__toString());
        // post date


        // search for further pages
        preg_match_all('/(morethread-[a-z0-9-]+\.html)">\[/i', $sResult, $aMatches);

        \Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('Found ' . count($aMatches[0]) . ' thread pages.', \Mjoelnir_Logger_Abstract::INFO);
    }

    protected function doRequest($sUri, $aParams = array()) {
        $aCurlOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE   => $this->cookieFile,
            CURLOPT_COOKIEJAR    => $this->cookieJar,
            CURLOPT_URL          => self::BASE_URL . $sUri,
            CURLOPT_POST         => true,
            CURLOPT_POSTFIELDS   => $aParams,
        );

        curl_setopt_array($this->curlHandle, $aCurlOptions);

        return curl_exec($this->curlHandle);
    }
}