<?php

class Mjoelnir_Controller_Abstract
{
    /**
     * A view instance.
     * @var Smarty
     */
    public $_oView  = null;

    /**
     * An acl instance.
     * @var Mjoelnir_Acl
     */
    protected $_oAcl    = null;
    
    public function __construct() {
        $this->_oView    = new Mjoelnir_View();
        $this->_oAcl    = Mjoelnir_Acl::getInstance();

        $this->_oView->assign('oAcl', $this->_oAcl);
    }
    
    protected function loadBox($sBoxname, $mData, $sLocation) {
        $sMethodName    = 'load' . $sBoxname . 'Box';
        if (method_exists($this, $sMethodName)) {
            return $this->$sMethodName($mData, $sLocation);
        }
        else {
            Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME)->log('Missing method for requested box "' . $sBoxname . '"', Mjoelnir_Logger_Abstract::WARN);
            return '';
        }
    }
    
    /**
     * Returns ready filled boxes with all nessessary customer data.
     * @return  string  Well formed html.
     */
    protected function loadCustomerInfoBox($oModel, $sLocation) {
        $oView  = new Mjoelnir_View();

        $aMainContactAddress    = CustomerAddressModel::getAll(null, null, array(\Db_Adzlocal_Config::TABLE_CUSTOMER_ADDRESSES.'.customer_id' => array('eq' => $oModel->getId()), \Db_Adzlocal_Config::TABLE_CUSTOMER_ADDRESSES.'.address_type' => array('eq' => 'Haupt-Entscheider')));
        $aInvoiceAddress        = CustomerAddressModel::getAll(null, null, array(\Db_Adzlocal_Config::TABLE_CUSTOMER_ADDRESSES.'.customer_id' => array('eq' => $oModel->getId()), \Db_Adzlocal_Config::TABLE_CUSTOMER_ADDRESSES.'.address_type' => array('eq' => 'Haupt-Rechnungsempfaenger')));
        
        $oView->assign('oCurrentUser', $oModel);
        $oView->assign('oMainContact', false);
        if ($aMainContactAddress['count'] === 1) {
            $oView->assign('oMainContact', current($aMainContactAddress['rows']));
        }
        $oView->assign('oInvoiceContact', false);
        if ($aInvoiceAddress['count'] === 1) {
            $oView->assign('oInvoiceContact', current($aInvoiceAddress['rows']));
        }
        
        return $oView->fetch('customer/customerInfo.tpl.html');
    }
    
    /**
     * Returns ready filled boxes with all nessessary campaign data.
     * @return  string  Well formed html.
     */
    protected function loadCampaignInfoBox($iCampaignId, $sLocation) {
        global $zz_campaign_stati_array;
        
        $oView  = new Mjoelnir_View();
        
        $oCampaign  = CampaignModel::getInstance($iCampaignId);
        
        $oView->assign('zz_campaign_stati_array', $zz_campaign_stati_array);
        $oView->assign('oCampaign', $oCampaign);
        
        return $oView->fetch('campaign/campaignInfo.tpl.html');
    }
    
    /**
     * Returns a ready filled sub nav for campaign context.
     * @param   integer $iCampaignId    The campaign id to prepare the subnav for.
     * @return  string  Well formed html.
     */
    protected function loadSubnavCampaignBox($iCampaignId, $sLocation) {
        $oView  = new Mjoelnir_View();

        $oView->assign('iCampaignId', $iCampaignId);
        $oView->assign('oAcl', Mjoelnir_Acl::getInstance());
        
        return $oView->fetch('campaign/subnavCampaign.tpl.html');
    }
    
    protected function loadSubnavCustomerBox($oCustomer, $sLocation) {
        $oView  = new Mjoelnir_View();
        
        $oView->assign('oCustomer', $oCustomer);
        $oView->assign('oAcl', Mjoelnir_Acl::getInstance());
        
        return $oView->fetch('customer/subnav.tpl.html');
    }
}
