<?php

class Mjoelnir_Remote_Viewer
{
    
    protected $_cHandle = null;

    
    protected $_sRemoteUrl  = '';

    public function __construct($sRemoteUrl, $sCookieFileName) {
        $this->_sRemoteUrl  = $sRemoteUrl;
        $this->_cHandle     = curl_init();
        curl_setopt($this->_cHandle, CURLOPT_HTTPHEADER, array(
            'ADZLOCAL_REMOTE: 1', 
            'ADZLOCAL_REMOTE_REFERRER: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        curl_setopt($this->_cHandle, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($this->_cHandle, CURLOPT_COOKIEJAR, $sCookieFileName);
        curl_setopt($this->_cHandle, CURLOPT_COOKIEFILE, $sCookieFileName);
    }
    
    /**
     * Requests an url with the given parameters with the given method.
     * @param   array   $aParams    The parameters to add to the request.
     * @param   string  $sMethod    The method to do the request. Could be POST or GET.
     * @return  string
     */
    public function get($aParams, $sMethod = 'POST') {
        if ($sMethod != 'POST' && $sMethod != 'GET') {
            return false;
        }

        $aTmpParams = array();
        foreach ($aParams as $sKey => $sValue) {
            $aTmpParams[]   = urlencode($sKey) . '=' . urlencode($sValue);
        }

        $sParams    = implode('&', $aTmpParams);
            
        if ($sMethod == 'POST') {
            $sUrl   = $this->_sRemoteUrl;
            curl_setopt($this->_cHandle, CURLOPT_POSTFIELDS, $sParams);
        }
        
        if ($sMethod == 'GET') {
            $sUrl   = $this->_sRemoteUrl . '?' . $sParams;
        }

        curl_setopt($this->_cHandle, CURLOPT_URL, $sUrl);

        return curl_exec($this->_cHandle);
    }
}