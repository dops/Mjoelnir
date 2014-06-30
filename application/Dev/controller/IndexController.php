<?php

namespace Dev;

class IndexController extends \Mjoelnir_Controller_Abstract
{
    public function indexAction() {
        
        $this->_oView->assign('oLoginForm', UserController::getLoginForm());
        
        $this->_oView->setTemplate('index/index.tpl.html');
        return $this->_oView;
    }
}