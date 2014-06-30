<?php

namespace Dev;

class IndexController extends \Mjoelnir_Controller_Abstract
{
    public function indexAction() {
        
        $this->oView->assign('oLoginForm', UserController::getLoginForm());
        
        $this->oView->setTemplate('index/index.tpl.html');
        return $this->oView;
    }
}