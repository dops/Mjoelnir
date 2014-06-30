<?php

namespace Dev;

/**
 * UserController
 *
 * @author Michael Streb <michael.streb@topdeals.de>
 */
class UserController extends \Mjoelnir_Controller_Abstract
{

    /**
     * Index action.
     * @return string
     */
    public function indexAction()
    {
        $site = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link'  => WEB_ROOT . 'user'));

        $this->_oView->assign('WEB_ROOT', WEB_ROOT);

        $this->_oView->setTemplate('user/index.tpl.html');
        return $this->_oView;
    }

    /**
     * Lists all users.
     * @return string
     */
    public function listAction()
    {
        $site = \Mjoelnir_Site::getInstance();
        $site->addBreadcrumb(array('title' => 'Userverwaltung', 'link'  => WEB_ROOT . 'user'));
        $site->addBreadcrumb(array('title' => 'User verwalten', 'link'  => WEB_ROOT . 'user/list'));

        // Validate filter
        $aFilter = array();
        if (\Mjoelnir_Request::getParameter('filter_name', false)) {
            $aFilter['name'] = '(first_name LIKE "%' . \Mjoelnir_Request::getParameter('filter_name') . '%" OR last_name LIKE "%' . \Mjoelnir_Request::getParameter('filter_name') . '%")';
        }
        if (\Mjoelnir_Request::getParameter('filter_email', false)) {
            $aFilter['email'] = array('like' => '%' . \Mjoelnir_Request::getParameter('filter_email') . '%');
        }

        // Limit users for better overview
        $iPage = \Mjoelnir_Request::getParameter('p', 1);
        if ($iPage == 1) {
            $iStart = null;
        } else {
            $iStart = ($iPage - 1) * ITEMS_PER_PAGE;
        }

        // Order
        $sOrderField = \Mjoelnir_Request::getParameter('order', 'user_id');
        $sOrderDir   = \Mjoelnir_Request::getParameter('orderDir', 'ASC');

        $aUsers = \UserModel::getAll($iStart, ITEMS_PER_PAGE, $aFilter, array($sOrderField => $sOrderDir));

        $oFilterName   = new \Mjoelnir_Form_Element_Text('name', \Mjoelnir_Request::getParameter('filter_name', ''), array('onkeyup' => 'return setFormValue(\'frmList\', \'filter_name\', this.value, false);'));
        $oFilterEmail  = new \Mjoelnir_Form_Element_Text('email', \Mjoelnir_Request::getParameter('filter_email', ''), array('onkeyup' => 'return setFormValue(\'frmList\', \'filter_email\', this.value, false);'));
        $oFilterSubmit = new \Mjoelnir_Form_Element_Button('submitFilters', 'Finden', array('onclick' => 'return setFormValue(\'frmList\', \'_submitFilters\', 1, true);'));
        $sFilter       = '<th></th><th>' . $oFilterName . '</th><th>' . $oFilterEmail . '</th><th></th><th>' . $oFilterSubmit . '</th>';

        $this->_oView->assign('sFilter', $sFilter);
        $this->_oView->assign('aUser', $aUsers);

        $this->_oView->setTemplate('user/list.tpl.html');
        return $this->_oView;
    }

    /**
     * E$dits a user.
     * @return  str
     */
    public function editAction()
    {
        $oSite = \Mjoelnir_Site::getInstance();
        $oSite->addBreadcrumb(array('title' => 'Userverwaltung', 'link'  => WEB_ROOT . 'user'));
        $oSite->addBreadcrumb(array('title' => 'User verwalten', 'link'  => WEB_ROOT . 'user/list'));
        if (\Mjoelnir_Request::getParameter('id', false)) {
            $oSite->addBreadcrumb(array('title' => 'User bearbeiten', 'link'  => WEB_ROOT . 'user/edit/id/' . \Mjoelnir_Request::getParameter('id')));
        } else {
            $oSite->addBreadcrumb(array('title' => 'User anlegen', 'link'  => WEB_ROOT . 'user/edit'));
        }

        $oUser          = \UserModel::getInstance(\Mjoelnir_Request::getParameter('id', null));
        $aUserUserroles = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $oUser->getId())));
        $aUserroleIds   = array();
        foreach ($aUserUserroles['rows'] as $iUserUserroleId => $oUserUserrole) {
            $aUserroleIds[] = $oUserUserrole->getUserroleId();
        }

        /**
         * Fetch all existing userroles for later validating and form building.
         */
        $aUserroles    = \UserroleModel::getAll();
        $aUserroleList = array();
        foreach ($aUserroles['rows'] as $role) {
            $aUserroleList[$role->getId()] = $role->getName();
        }

        $aMessages = array('error' => array());

        if (\Mjoelnir_Request::getParameter('save', false) || \Mjoelnir_Request::getParameter('save_return', false)) {
            // Validate login information
            if (!$oUser->setEmail(\Mjoelnir_Request::getParameter('email', false))) {
                $aMessages['error']['email'] = 'Bitte geben Sie eine korrekte E-Mail Adresse an.';
            }
            if (strlen(\Mjoelnir_Request::getParameter('password')) > 0) {
                if (\Mjoelnir_Request::getParameter('password') == \Mjoelnir_Request::getParameter('passwordConfirm')) {
                    $oUser->setPassword(\Mjoelnir_Request::getParameter('password'));
                } else {
                    $aMessages['error']['password'] = 'Das angegebene Passwort stimmt nicht mit der Passwortbestätigung überein.';
                }
            }

            if (\Mjoelnir_Request::getParameter('passwordCreate', false)) {
                $bSendToUser = true;
                $mPassword   = \Mjoelnir_Auth::createPassword($oUser, 16, $bSendToUser);
                if (false !== $mPassword) {
                    $oUser->setPassword($mPassword);
                }
            }

            if ($oUser->getId() === 0 && strlen(\Mjoelnir_Request::getParameter('password')) == 0 && \Mjoelnir_Request::getParameter('passwordCreate', false) === false) {
                $aMessages['error']['password'] = 'Bitte geben Sie entweder ein Passwort und die Passwortbestätigung ein, oder setzen Sie den
                    Haken für "Passwort generieren & zusenden".';
            }

            if (\Mjoelnir_Request::getParameter('active', false)) {
                $oUser->setActiveFlag(1);
            } else {
                $oUser->setActiveFlag(0);
            }

            if (!$oUser->setFirstName(\Mjoelnir_Request::getParameter('firstName', false))) {
                $aMessages['error']['firstName'] = 'Bitte geben Sie einen korrekten Vornamen an.';
            }
            if (!$oUser->setLastName(\Mjoelnir_Request::getParameter('lastName', ''))) {
                $aMessages['error']['lastName'] = 'Bitte geben Sie einen korrekten Nachnamen an.';
            }
            if ($oUser->getId() === 0) {
                if (!$oUser->setType(\Mjoelnir_Request::getParameter('userType', ''))) {
                    $aMessages['error']['type'] = 'Bitte geben Sie einen korrekten Usertyp an.';
                }
            }

            // Validate employee information
            if (\Mjoelnir_Request::getParameter('userType', false) === 'Employee') {
                if (!$oUser->setPhoneMobile(\Mjoelnir_Request::getParameter('phoneMobile', ''))) {
                    $aMessages['error']['phoneMobile'] = 'Bitte geben Sie eine korrekte private Handynummer an.';
                }
                if (!$oUser->setPhoneExtension(\Mjoelnir_Request::getParameter('phoneExtension', ''))) {
                    $aMessages['error']['phoneExtension'] = 'Bitte geben Sie eine korrekte Durchwahl an.';
                }
                if (!$oUser->setPosition(\Mjoelnir_Request::getParameter('position', ''))) {
                    $aMessages['error']['position'] = 'Bitte geben Sie eine korrekte Position an.';
                }
            }

            // Validate customer information
            if (\Mjoelnir_Request::getParameter('userType', false) === 'Customer') {
                if (!$oUser->setCompany(\Mjoelnir_Request::getParameter('company', false))) {
                    $aMessages['error']['company'] = 'Bitte geben Sie einen korrekten Firmennamen an.';
                }
                if (!$oUser->setPhone(\Mjoelnir_Request::getParameter('phone', false))) {
                    $aMessages['error']['phone'] = 'Bitte geben Sie eine korrekte Telefonnummer an.';
                }
                if (!$oUser->setphoneMobile(\Mjoelnir_Request::getParameter('phoneMobile', ''))) {
                    $aMessages['error']['phoneMobile'] = 'Bitte geben Sie eine korrekte Handynummer an.';
                }
                if (!$oUser->setAddress(\Mjoelnir_Request::getParameter('address', false))) {
                    $aMessages['error']['address'] = 'Bitte geben Sie eine korrekte Adresse an.';
                }
                if (!$oUser->setZip(\Mjoelnir_Request::getParameter('zip', false))) {
                    $aMessages['error']['zip'] = 'Bitte geben Sie eine korrekte Postleitzahl an.';
                }
                if (!$oUser->setCity(\Mjoelnir_Request::getParameter('city', false))) {
                    $aMessages['error']['city'] = 'Bitte geben Sie eine korrekte Stadt an.';
                }
                if (!$oUser->setCountry(\Mjoelnir_Request::getParameter('country', false))) {
                    $aMessages['error']['country'] = 'Bitte geben Sie ein korrektes Land an.';
                }
            }

            if (!$oUser->setuserroleIds(\Mjoelnir_Request::getParameter('userroleIds', false))) {
                $aMessages['error']['userroleIds'] = 'Bitte geben Sie mindestens eine Benutzer-Rolle an.';
            }

            if (count($aMessages['error']) == 0) {
                $oUser->save();

                if (\Mjoelnir_Request::getParameter('save', false)) {
                    header('Location: ' . WEB_ROOT . 'user/edit/id/' . $oUser->getId());
                    exit();
                }

                if (\Mjoelnir_Request::getParameter('save_return', false)) {
                    header('Location: ' . WEB_ROOT . 'user/list');
                    exit();
                }
            }
        }

        $this->_oView->assign('WEB_ROOT', WEB_ROOT);
        $this->_oView->assign('error', $aMessages['error']);
        $this->_oView->assign('userForm', $this->_getEditForm($aMessages));

        $this->_oView->setTemplate('user/edit.tpl.html');
        return $this->_oView;
    }

    /**
     * Deletes a user.
     */
    public function deleteAction()
    {
        $userId = \Mjoelnir_Request::getParameter('id', false);
        if ($userId) {
            \UserModel::delete($userId);
        }

        header('Location: ' . WEB_ROOT . 'user/list');
        exit();
    }

    /**
     * User login.
     * @param   str $loginHash  The users login hash
     * @return  str
     */
    public function loginAction($loginHash = null)
    {
        $aMessages = array('error' => array());
        $sName     = '';
        // Has the user send login informations
        if (\Mjoelnir_Request::getParameter('login', false)) {
            if (strlen(\Mjoelnir_Request::getParameter('name', false)) > 0 && strlen(\Mjoelnir_Request::getParameter('password', false)) > 0) {
                $userId = \UserModel::getUserIdByLogin(\Mjoelnir_Request::getParameter('name'), \Mjoelnir_Request::getParameter('password'));

                if ($userId !== false) {
                    $oUser = \UserModel::getInstance($userId);
                    if ($oUser->getActiveFlag() == 1) {
                        $oUser->renewLoginHash();

                        $oAuth = \Mjoelnir_Auth::getInstance();
                        $oAuth->authenticate($oUser->getLoginHash());

                        $oSite = \Mjoelnir_Site::getInstance();
                        \Mjoelnir_Redirect::redirect(WEB_ROOT . $oSite->getDefaultPage() . '/' . $oSite->getDefaultAction() . '/', 200);
                    } else {
                        $aMessages['error']['msg'] = \Mjoelnir_Message::getMessage(2003);
                    }
                } else {
                    $aMessages['error']['msg'] = \Mjoelnir_Message::getMessage(2002);
                }
            } else {
                if (strlen(\Mjoelnir_Request::getParameter('name', false)) == 0) {
                    $aMessages['error']['name'] = \Mjoelnir_Message::getMessage(2000);
                } else {
                    $sName = \Mjoelnir_Request::getParameter('name');
                }

                if (strlen(\Mjoelnir_Request::getParameter('password', false)) == 0) {
                    $aMessages['error']['password'] = \Mjoelnir_Message::getMessage(2001);
                }
            }
        }

        if (count($aMessages['error']) > 0) {
            $this->_oView->assign('aMessages', $aMessages);
        }

        $this->_oView->assign('oFormLogin', $this->getLoginForm());

        $this->_oView->setTemplate('user/login.tpl.html');
        return $this->_oView;
    }

    public static function getLoginForm($aMessages = array())
    {
        $oForm = new \Mjoelnir_Form('frmLogin', '', 'post', array(), PATH_TEMPLATE . 'form/');
        $oForm->addElement('text', 'name', \Mjoelnir_Request::getParameter('name'), array('placeholder' => 'Name', 'error' => (isset($aMessages['error']['name'])) ? true : false));
        $oForm->addElement('password', 'password', '', array('placeholder' => 'Passwort', 'error' => (isset($aMessages['error']['password'])) ? true : false));
        $oForm->addElement('submit', 'login', 'Einloggen');

        return $oForm;
    }

    /**
     * Logs out the user.
     */
    public function logoutAction()
    {
        $auth = \Mjoelnir_Auth::getInstance();
        $auth->cancel();

        header('Location: ' . WEB_ROOT . 'user/login');
        exit();
    }

    /**
     * Encrypts a given string.
     * @param   str $password   The password to encrypt.
     * @return  str
     */
    protected function cryptPassword($password)
    {
        return md5($password);
    }

    protected function _getEditForm($aMessages)
    {
        $oUser          = \UserModel::getInstance(\Mjoelnir_Request::getParameter('id', null));
        $aUserUserroles = \UserUserroleModel::getAll(null, null, array('user_id' => array('eq' => $oUser->getId())));
        $aUserroleIds   = array();
        foreach ($aUserUserroles['rows'] as $iUserUserroleId => $oUserUserrole) {
            $aUserroleIds[] = $oUserUserrole->getUserroleId();
        }

        /**
         * Fetch all existing userroles for later validating and form building.
         */
        $aUserroles    = \UserroleModel::getAll();
        $aUserroleList = array();
        foreach ($aUserroles['rows'] as $role) {
            $aUserroleList[$role->getId()] = $role->getName();
        }

        $aSelectedUserRoleIds = \Mjoelnir_Request::getParameter('userroleIds', array());

        $oForm = new \Mjoelnir_Form('userEdit', '', 'post', array(), PATH_TEMPLATE . 'form/');

        if ($oUser->getId() !== 0) {
            $oForm->addElement('hidden', 'userId', $oUser->getId());
        }

        // Login information
        $oForm->addElement('text', 'email', \Mjoelnir_Request::getParameter('email', $oUser->getEmail()), array('label' => 'E-Mail', 'error' => (isset($aMessages['error']['email'])) ? true : false));
        $oForm->addElement('password', 'password', '', array('label'        => 'Passwort', 'autocomplete' => 'off', 'error'        => (isset($aMessages['error']['password'])) ? true : false));
        $oForm->addElement('password', 'passwordConfirm', '', array('label'        => 'Passwort wiederholen', 'autocomplete' => 'off', 'error'        => (isset($aMessages['error']['passwordConfirm'])) ? true : false));
        $oForm->addElement('checkbox', 'passwordCreate', '', array('label' => 'Passwort generieren & zusenden', 'error' => false));
        $oForm->addElement('text', 'firstName', \Mjoelnir_Request::getParameter('firstName', $oUser->getFirstName()), array('label' => 'Vorname', 'error' => (isset($aMessages['error']['firstName'])) ? true : false));
        $oForm->addElement('text', 'lastName', \Mjoelnir_Request::getParameter('lastName', $oUser->getLastName()), array('label' => 'Nachname', 'error' => (isset($aMessages['error']['lastName'])) ? true : false));
        $oForm->addElement('checkbox', 'active', \Mjoelnir_Request::getParameter('active', $oUser->getActiveFlag()), array('label' => 'aktiv?', 'error' => (isset($aMessages['error']['active'])) ? true : false));

        // Only when creating a new user the type could be changed.
        if ($oUser->getId() === 0) {
            $aUserTypeSelect = array('list'     => array('Customer' => array('value' => 'Kunde'), 'Employee' => array('value' => 'Mitarbeiter')), 'selected' => $oUser->getType());
            $oForm->addElement('select', 'userType', $aUserTypeSelect, array('label' => 'Konto-Typ', 'error' => (isset($aMessages['error']['userType'])) ? true : false));
        } else {
            $oForm->addElement('html', 'userType', $oUser->getType(true), array('label' => 'Konto-Typ'));
        }
        $oForm->addElementsToFieldset(array('email', 'password', 'passwordConfirm', 'passwordCreate', 'firstName', 'lastName', 'userType', 'active'), 'allgemeine Informationen');

        // general information
        if ($oUser->getType() == 'Employee' || $oUser->getId() === 0) {
            $oForm->addElement('text', 'phoneMobile', \Mjoelnir_Request::getParameter('phoneMobile', $oUser->getPhoneMobile()), array('label' => 'Handy', 'error' => (isset($aMessages['error']['phoneMobile'])) ? true : false));
            $oForm->addElement('text', 'phoneExtension', \Mjoelnir_Request::getParameter('phoneExtension', $oUser->getPhoneExtension()), array('label' => 'Durchwahl', 'error' => (isset($aMessages['error']['phoneExtension'])) ? true : false));
            $oForm->addElement('text', 'position', \Mjoelnir_Request::getParameter('position', $oUser->getPosition()), array('label' => 'Position', 'error' => (isset($aMessages['error']['position'])) ? true : false));
            $aOptions = ($oUser->getId() === 0) ? array('id'    => 'employeeInfo', 'class' => 'specialUserInfo') : array();
            $oForm->addElementsToFieldset(array('phoneMobile', 'phoneExtension', 'position'), 'spezielle Mitarbeiter-Informationen', $aOptions);
        }

        if ($oUser->getType() == 'Customer' || $oUser->getId() === 0) {
            $oForm->addElement('text', 'company', \Mjoelnir_Request::getParameter('company', $oUser->getCompany()), array('label' => 'Firma', 'error' => (isset($aMessages['error']['company'])) ? true : false));
            $oForm->addElement('text', 'phone', \Mjoelnir_Request::getParameter('phone', $oUser->getPhone()), array('label' => 'Telefon', 'error' => (isset($aMessages['error']['phone'])) ? true : false));
            $oForm->addElement('text', 'phoneMobile', \Mjoelnir_Request::getParameter('phoneMobile', $oUser->getPhoneMobile()), array('label' => 'Handy', 'error' => (isset($aMessages['error']['phoneMobile'])) ? true : false));
            $oForm->addElement('text', 'address', \Mjoelnir_Request::getParameter('address', $oUser->getAddress()), array('label' => 'Straße und Hausnr.', 'error' => (isset($aMessages['error']['address'])) ? true : false));
            $oForm->addElement('text', 'zip', \Mjoelnir_Request::getParameter('zip', $oUser->getZip()), array('label' => 'PLZ', 'error' => (isset($aMessages['error']['zip'])) ? true : false));
            $oForm->addElement('text', 'city', \Mjoelnir_Request::getParameter('city', $oUser->getCity()), array('label' => 'Stadt', 'error' => (isset($aMessages['error']['city'])) ? true : false));
            $oForm->addElement('text', 'country', \Mjoelnir_Request::getParameter('country', $oUser->getCountry()), array('label' => 'Land', 'error' => (isset($aMessages['error']['country'])) ? true : false));
            $aOptions = ($oUser->getId() === 0) ? array('id'    => 'customerInfo', 'class' => 'specialUserInfo') : array();
            $oForm->addElementsToFieldset(array('firstName', 'lastName', 'company', 'phone', 'phoneMobile', 'address', 'zip', 'city', 'country'), 'spezielle Kunden-Informationen', $aOptions);
        }

        // Account type and userroles

        foreach ($aUserroles['rows'] as $iUserroleId => $oUserrole) {
            if (\Mjoelnir_Request::getParameter('save', false)) {
                $iValue = (isset($aSelectedUserRoleIds[$iUserroleId])) ? 1 : 0;
            } else {
                $iValue = (in_array($iUserroleId, $aUserroleIds)) ? 1 : 0;
            }
            $oForm->addElement('checkbox', 'userroleIds[' . $oUserrole->getId() . ']', $iValue, array('label' => $oUserrole->getName(), 'error' => (isset($aMessages['error']['userroleIds'])) ? true : false));
            $aElementNames[] = 'userroleIds[' . $oUserrole->getId() . ']';
        }
        $oForm->addElementsToFieldset($aElementNames, 'Benutzerrollen');

        $oForm->addElement('submit', 'save', 'Speichern');
        $oForm->addElement('submit', 'save_return', 'Speichern und zurück');
        $oForm->addElement('html', 'requiredNote', 'Alle mit einem * gekennzeichneten Felder sind Pflichtfelder.');

        return $oForm->__toString();
    }

}

