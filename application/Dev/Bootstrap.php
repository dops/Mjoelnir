<?php

/**
 * Description of Bootstrap
 *
 * @author td-office
 */
class Bootstrap
{

    /**
     * The instance of site.
     * @var Mjoelnir_Site
     */
    protected $oSite = null;

    /**
     * The instance of the current user.
     * @var UserModel
     */
    protected $oUser = null;

    /**
     * Construstor.
     */
    public function __construct() {
        $this->_setIncludePaths();

        $this->oSite = Mjoelnir_Site::getInstance();
        $this->oUser = UserModel::getCurrentUser();

        $this->oSite->setDefaultController('index');
        $this->oSite->setDefaultAction('index');
        $this->oSite->setPageTitle('Mjoelnir MVC');

        $this->prepareAcl();
    }

    /**
     * Loads the page. If the user has missing permissions, he will be redirected to the default page/action. Keep in mind the every user needed minimum permissions to
     * view the default page/action.
     */
    public function load() {
        $oAcl = Mjoelnir_Acl::getInstance();
        $oLog = Mjoelnir_Logger_Abstract::getLogger(APPLICATION_NAME);

        $oLog->log('Aufruf: Controller: ' . $this->oSite->getController() . ', Action: ' . $this->oSite->getAction(), Mjoelnir_Log::DEBUG);

        try {
            if (!$oAcl->isAllowed(APPLICATION_NAME, strtolower($this->oSite->getController()), strtolower($this->oSite->getAction()))) {
//                if (RETURN_METHOD == 'json') {
//                    echo json_encode(array('error'   => true, 'status'  => 403, 'message' => Mjoelnir_Message::getMessage(2010)));
//                    exit();
//                }
//
//                Mjoelnir_Redirect::redirect(WEB_ROOT . 'error/forbidden', 403);
            }

//            if ($this->oUser !== false) {
//                // Authorized user
//                if (!$oAcl->isAllowed(APPLICATION_NAME, strtolower($this->oSite->getPage()), strtolower($this->oSite->getAction()))) {
//                    $oLog->log('The user tried to access a not defined permission.', Mjoelnir_Logger_Abstract::INFO);
//
//                    if (RETURN_METHOD == 'json') {
//                        echo json_encode(array('error'   => true, 'status'  => 403, 'message' => Mjoelnir_Message::getMessage(2010)));
//                        exit();
//                    }
//
//                    Mjoelnir_Redirect::redirect(WEB_ROOT . 'error/forbidden', 403);
//                }
//            } else {
//                // Not authorized user
//                $oLog->log('The user is not logged in.', Mjoelnir_Logger_Abstract::INFO);
//
//                if ($this->oSite->getPage() != 'user' || $this->oSite->getAction() != 'login') {
//                    if (RETURN_METHOD == 'json') {
//                        echo json_encode(array('error'   => true, 'status'  => 401, 'message' => Mjoelnir_Message::getMessage(2009)));
//                        exit();
//                    }
//
//                    Mjoelnir_Redirect::redirect(WEB_ROOT . 'user/login/', 200);
//                }
//            }
        }
        catch (Mjoelnir_Acl_Exception $e) {
            $oLog->log('Something went wrong while loading the page.', Mjoelnir_Logger_Abstract::EMERG);
            header('Location: ' . WEB_ROOT . 'error/forbidden');
            exit();
        }

//        $this->oSite->addCssFile('common.less');
//        $this->oSite->addCssFile('famfamfamSilkIcons.less');
//        $this->oSite->addCssFile('paging.less');
//        $this->oSite->addJsFile('less.js', 'footer');
//        if (APPLICATION_ENV == 'development') {
//            $this->oSite->addJsFile('lessDev.js', 'footer');
//        }
//        $this->oSite->addJsFile('jquery/jquery-1.9.1.js', 'footer');
//        $this->oSite->addJsString('<script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js"></script>', 'footer');
//        $this->oSite->addJsFile('jquery/timepicker/jquery-ui-timepicker-addon.js', 'footer');
//        $this->oSite->addJsFile('jquery/jquery-ui-sliderAccess.js', 'footer');
//        $this->oSite->addJsFile('jquery/jquery.easing.1.3.js', 'footer');
//        $this->oSite->addJsFile('form.js', 'footer');
//        $this->oSite->addDebugContent('<div id="memoryPeakUsage">Memory peak usage: ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . '</div>');
//        $this->oSite->setBaseTemplate('layout.tpl.html');
//
//        $oMessages = Mjoelnir_Message::getInstance(Mjoelnir_Request::getInstance());
//
//        $view = $this->oSite->run();
//
//        $view->setTemplateDir(PATH_TEMPLATE);
//
//        $view->assign('baseUrl', (preg_match('/HTTP\//', $_SERVER['SERVER_PROTOCOL'])) ? 'http://' . $_SERVER['HTTP_HOST'] : 'https://' . $_SERVER['HTTP_HOST']);
//        $view->assign('applicationEnv', APPLICATION_ENV);
//        $view->assign('sApplicationName', APPLICATION_NAME);
//        $view->assign('sWebRoot', WEB_ROOT);
//        $view->assign('oAcl', $oAcl);
//        $view->assign('oCurrentUser', UserModel::getCurrentUser());
//        $view->assign('oSite', $this->oSite);
//        $aTemplateMessages = (array) $view->getTemplateVars('aMessages');
//        $aUrlMessages      = $oMessages->getAllMessages();
//        $aMessages         = array_merge_recursive($aTemplateMessages, $aUrlMessages);
//        $view->assign('aMessages', $aMessages);
//        $view->assign('sFormOrderFilter', $this->_getOrderAndFilterForm());
//
//        if (\Mjoelnir_Request::getParameter('HTTP_MJOELNIR_REMOTE', false)) {
//            $this->oSite->setBaseTemplate('blank.tpl.html');
//        }

//        $this->oSite->display($view);
    }

    /**
     * Returns a form used to order or filter the page items.
     * @return  str
     */
    protected function _getOrderAndFilterForm() {
        if (Mjoelnir_Request::getParameter('HTTP_MJOELNIR_REMOTE', false)) {
            $sAction = Mjoelnir_Request::getParameter('HTTP_MJOELNIR_REMOTE_REFERRER', '');
        }
        else {
            $sAction = '';
        }
        $oForm   = new Mjoelnir_Form('frmList', $sAction);
        $oForm->addElement('hidden', 'p', Mjoelnir_Request::getParameter('p', 1));

        $bUseCookieData = true;
        $aParams        = Mjoelnir_Request::getAllParameters();
        foreach ($aParams as $mKey => $mValue) {
//$bUseCookieData = false means that form has been submitted, therefore don't use cookie data, but rely on request data
            if (strpos($mKey, 'order') !== false) {
                $bUseCookieData = false;
                $oForm->addElement('hidden', $mKey, Mjoelnir_Request::getParameter($mKey, ''));
            }
            if (strpos($mKey, 'filter_') !== false) {
                $bUseCookieData = false;
                $oForm->addElement('hidden', $mKey, Mjoelnir_Request::getParameter($mKey, ''));
            }
        }
        if ($bUseCookieData === true) {
            $sCookieName = 'filter_' . $this->oSite->getController() . '_' . $this->oSite->getAction();
            if (isset($_COOKIE[$sCookieName])) {
//get cookie (might not have been set, then just empty fields are created)
                $aCookie = json_decode($_COOKIE[$sCookieName], true);
                foreach ($aCookie as $mKey => $mValue) {
                    $oForm->addElement('hidden', $mKey, $mValue);
                }
            }
        }
        return $oForm->__toString();
    }

    /**
     * Sets the include paths.
     * @return  true
     */
    protected function _setIncludePaths() {
//        set_include_path(get_include_path() . ':' . PATH_LIBRARY); -> Moved to config

        return true;
    }

    /**
     * Set the user permissions in the acl.
     * @return  bool
     */
    protected function prepareAcl() {
        $oAcl = Mjoelnir_Acl::getInstance();

        if ($this->oUser instanceof UserModel) {
// Logged in user
            $aUserUserroles = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $this->_user->getId())));
            foreach ($aUserUserroles['rows'] as $iUserUserrole => $oUserUserrole) {
                $aPermissions = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => $oUserUserrole->getUserroleId())));

                if ($oUserUserrole->getUserroleId() == ADMIN_USER_ROLE_ID) {
                    $oAcl->setAsAdmin();
                    break;
                }
                else {
                    foreach ($aPermissions['rows'] as $oPermission) {
                        $oAcl->addPermission($oPermission);
                    }
                }
            }
        }
        else {
// Logged out user
            $aPermissions = \UserRolePermissionModel::getAll(null, null, array('user_role_id' => array('eq' => UNREGISTERED_USER_ROLE)));
            foreach ($aPermissions['rows'] as $oPermission) {
                $oAcl->addPermission($oPermission);
            }
        }

        return true;
    }

}

