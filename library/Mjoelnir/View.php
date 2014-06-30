<?php

class Mjoelnir_View extends Smarty
{
    /**
     * The template to use for output.
     * @var str
     */
    protected $_sTemplate   = null;

    public function __construct() {
        parent::__construct ();

        $this->setTemplateDir(PATH_TEMPLATE);
        $this->setCompileDir(PATH_TEMPLATE_COMPILE);
        $this->setCacheDir(PATH_TEMPLATE_CACHE);

        /**
         * set some variables
         */
        $this->caching          = SMARTY_CACHING;
        $this->compile_check    = SMARTY_COMPILE_CHECK;
        $this->force_compile    = SMARTY_COMPILE_FORCE;
        $this->cache_lifetime   = SMARTY_CACHING_LIFETIME;
        
        $this->assign('WEB_ROOT', WEB_ROOT);
        $this->assign('iPageCurrent', Mjoelnir_Request::getParameter('p', 1));
        $this->assign('iPageSize', ITEMS_PER_PAGE);
        $this->assign('oCurrentUser', UserModel::getCurrentUser());
    }


    public function setTemplate($sTemplate) {
        $aTemplateDir  = $this->getTemplateDir();
        foreach($aTemplateDir as $sTemplateDir) {
            if (file_exists($sTemplateDir . $sTemplate)) {
                $this->_sTemplate   = $sTemplate;
            }
        }
    }
    
    public function getTemplate() {
        return $this->_sTemplate;
    }
}