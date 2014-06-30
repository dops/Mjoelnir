<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Form
 *
 * @author Michael Streb <michael.streb@michael-streb.de>
 */
class Mjoelnir_Form
{

    /**
     * The forms name.
     * @var str
     */
    protected $_sName = '';

    /**
     * The URL to send the from to.
     * @var str
     */
    protected $_sAction = '/';

    /**
     * Defines if the form will be sent via https or not. False is default.
     * @var bool
     */
    protected $_bUseSsl = false;

    /**
     * The method of form submition.
     * @var str
     */
    protected $_sMethod = 'post';

    /**
     * Additional form options.
     * @var array
     */
    protected $_aOptions = array();

    /**
     * Contanis all elements belonging to the form.
     * @var type
     */
    protected $_aElements = array();

    /**
     * Contains a fiedlsets an the corresponding elements.
     * @var array
     */
    protected $_aFieldsets = array();

    /**
     * A template dir given by the application.
     * @var str
     */
    protected $_sTemplateDir = null;

    /**
     * The default template dir.
     * @var str
     */
    public static $_sDefaultTemplateDir = '../library/Mjoelnir/Form/Templates/';

    /**
     * Multipart flag. If an element is added that needs multipart form handling, this flag is set to true.
     * @var bool
     */
    protected $_bIsMultipart = false;

    /**
     * The form view.
     * @var Smarty
     */
    protected $_oView = null;

    public function __construct($sName, $sAction = null, $sMethod = 'post', $aOptions = array(), $sTemplateDir = null) {
        $sRequestUri         = preg_replace('/\/messages\/([\d]{4}(:[\d]+)?)(,[\d]{4}(:[\d]+)?)*/', '', \Mjoelnir_Request::getParameter('REQUEST_URI'));
        $this->_sName        = $sName;
        $this->_sAction      = (is_null($sAction) || empty($sAction)) ? $_SERVER['SERVER_NAME'] . $sRequestUri : $sAction;
        $this->_sMethod      = $sMethod;
        $this->_aOptions     = $aOptions;
        $this->_sTemplateDir = (is_null($sTemplateDir)) ? DOCUMENT_ROOT . self::$_sDefaultTemplateDir : $sTemplateDir;
    }

    /**
     * Adds an element to the form.
     * @param   str     $sType      The element type. Must be equal to a html input type.
     * @param   str     $sName      The name of the element. Because form element values are delivered by their name, teh element will be registered under his name. This means that further elements will override a first one with the same name.
     * @param   mixed   $mValue     The value of the element. Can be a string or an array, depending on the requested element type.
     * @param   array   $aOptions   The options array can name additional attributes to set in the element output.
     * @param   str     $sPrefix    The prefix will be place dirct before the element output.
     * @param   str     $sSuffix    The suffix will be place dirct behind the element output.
     * @return Topdeals_Form_Element_Abstract
     */
    public function addElement($sType, $sName, $mValue, $aOptions = array(), $sPrefix = '', $sSuffix = '') {
        if (in_array($sType, array('select', 'radiomulti'))) {
            $sTypeClassName = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($sType));
            $oElement       = new $sTypeClassName($sName, (isset($mValue['selected'])) ? $mValue['selected'] : '', $mValue['list'], $aOptions, $this->_sTemplateDir, $sPrefix, $sSuffix);
        }
        elseif ($sType == 'number') {
            $sTypeClassName = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($sType));
            $iMin           = (isset($mValue['min'])) ? $mValue['min'] : false;
            $iMax           = (isset($mValue['max'])) ? $mValue['max'] : false;
            $iStep          = (isset($mValue['step'])) ? $mValue['step'] : false;

            $oElement = new $sTypeClassName($sName, (isset($mValue['selected'])) ? $mValue['selected'] : '', $iMin, $iMax, $iStep, $aOptions, $this->_sTemplateDir, $sPrefix, $sSuffix);
        }
        else {
            if ($sType === 'file') {
                $this->_bIsMultipart = true;
            }

            $sTypeClassName = 'Mjoelnir_Form_Element_' . ucfirst(strtolower($sType));
            $oElement       = new $sTypeClassName($sName, $mValue, $aOptions, $this->_sTemplateDir, $sPrefix, $sSuffix);
        }

        if ($oElement->getType() == 'radio') {
            $this->_aElements[$sName . $oElement->getValue()] = $oElement;
        }
        else {
            $this->_aElements[$sName] = $oElement;
        }

        return $oElement;
    }

    /**
     * Returns a single element from the form. 
     * @param   string  $sElementName <p>
     * The name of the element to return from the form.
     * </p>
     * @param   string  $bRendered <p>
     * If the optional parameter $bRendered is set to true, the element will be returned as ready rendered html.
     * </p>
     * @param   string  $bDeleteFromList <p>
     * If teh optional parameter $bDeleteFromList is set to true, the element will be deleted from the form, so if you render the whole form after getting an element with 
     * $bDeleteFromList set to true, the form will not contain the previously fetched element anymore.
     * </p>
     * @return boolean|string|Mjoelnir_Form_Element_Abstract <p>
     * If an element with the given name is found in the form it will be returned as a form element object. If the parameter $bRendered is set to true, the ready rendered 
     * html will be returned instead of the object. If no element is found false is returned.
     * </p>
     */
    public function getElement($sElementName, $bRendered = false, $bDeleteFromList = false) {
        $mElement = false;

        // Search in element list
        if (isset($this->_aElements[$sElementName])) {
            $mElement = $this->_aElements[$sElementName];

            if ($bDeleteFromList === true) {
                unset($this->_aElements[$sElementName]);
            }
        }

        // Search for fieldset
        if (isset($this->_aFieldsets[$sElementName])) {
            $mElement = $this->_aFieldsets[$sElementName];
        }

        // Search in fieldstes
        foreach ($this->_aFieldsets as $aFieldset) {
            if (isset($aFieldset[$sElementName])) {
                $mElement = $aFieldset[$sElementName];

                if ($bDeleteFromList === true) {
                    unset($aFieldset[$sElementName]);
                }
            }
        }

        // Return element
        if ($mElement !== false) {
            if ($bRendered === true) {
                if (is_array($mElement)) {
                    $sElements         = '';
                    $sFieldsetElements = '';
                    foreach ($mElement['aFields'] as $sFieldsetElement) {
                        $sFieldsetElements .= $sFieldsetElement;
                    }
                    $oFieldset = new Mjoelnir_Form_Element_Fieldset($sElementName, $sFieldsetElements, $this->_aFieldsets[$sElementName]['aOptions'], $this->_sTemplateDir);
                    $sElements .= $oFieldset;

                    return $sElements;
                }
                else {
                    return $mElement->__toString();
                }
            }
            else {
                return $mElement;
            }
        }

        return false;
    }

    public function getStartTag() {
        return $this->renderStartTag();
    }

    public function getEndTag() {
        return $this->renderEndTag();
    }

    /**
     * Removes an element from the form.
     * @param   str     $sName  The nam of the element.
     * @return  bool
     */
    public function removeElement($sName) {
        if (isset($this->_aElements[$sName])) {
            unset($this->_aElements[$sName]);
        }

        return true;
    }

    /**
     * Adds a fieldset to with the given elements to the list of elements. The elements itself will be moved from the element list into the fieldset list entry. Named but not
     * existing elements will be skipped.
     * @param   array   $aElements  An array with one or more already existing form elements.
     * @param   str     $sName      The legend of the fieldset.
     * @param   array   $aOptions   Optional attributes to add to the fieldset.
     * @return  bool
     */
    public function addElementsToFieldset($aElements, $sName, $aOption = array()) {
        if (!isset($this->_aFieldsets[$sName])) {
            $this->_aFieldsets[$sName] = array('aFields' => array(), 'aOptions'                => $aOption);
            $this->_aElements[$sName] = 'fieldset';
        }

        foreach ($aElements as $sElementName) {
            // Search in element list
            if (isset($this->_aElements[$sElementName]) && !isset($this->_aFieldsets[$sElementName])) {
                $this->_aFieldsets[$sName]['aFields'][] = $this->_aElements[$sElementName];
                unset($this->_aElements[$sElementName]);
            }
            // Search in fieldset list
            if (isset($this->_aFieldsets[$sElementName])) {
                $this->_aFieldsets[$sName]['aFields'][$sElementName] = 'fieldset';
                unset($this->_aElements[$sElementName]);
            }
        }

        return true;
    }

    /**
     * Renders the form elements to string.
     * @return string
     */
    public function renderElements($aElements) {
        $sElements = '';
        foreach ($aElements as $sElementName => $oElement) {
            if ($oElement == 'fieldset') {
                $sFieldsetElements = $this->renderElements($this->_aFieldsets[$sElementName]['aFields']);
                $oFieldset         = new Mjoelnir_Form_Element_Fieldset($sElementName, $sFieldsetElements, $this->_aFieldsets[$sElementName]['aOptions'], $this->_sTemplateDir);
                $sElements .= $oFieldset;
            }
            else {
                if ($oElement->getType() === 'file') {
                    $this->_bIsMultipart = true;
                }
                $sElements .= $oElement->__toString();
            }
        }

        return $sElements;
    }

    /**
     * Renderes the form start tag to string.
     * @return string
     */
    protected function renderStartTag() {
        $this->_oView = new Mjoelnir_View();
        $this->_oView->setTemplateDir($this->getTemplatePath());

        $aTmpOptions = $this->_aOptions;
        $aOptions    = array();
        if ($this->_bIsMultipart) {
            $aOptions[] = 'enctype="multipart/form-data"';
        }
        foreach ($aTmpOptions as $sParam => $mValue) {
            $aOptions[] .= $sParam . '="' . $mValue . '"';
        }

        $this->_oView->assign('name', $this->_sName);
        $this->_oView->assign('action', ($this->_bUseSsl) ? 'https://' . $this->_sAction : 'http://' . $this->_sAction);
        $this->_oView->assign('method', $this->_sMethod);
        $this->_oView->assign('options', implode(' ', $aOptions));

        return $this->_oView->fetch('formStart.tpl.html');
    }

    /**
     * Renders the form end tag to string.
     * @return type
     */
    protected function renderEndTag() {
        $this->_oView = new Mjoelnir_View();
        $this->_oView->setTemplateDir($this->getTemplatePath());
        return $this->_oView->fetch('formEnd.tpl.html');
    }

    /**
     * Returns the form output.
     * @return str
     */
    public function __toString() {
        return $this->renderStartTag() . $this->renderElements($this->_aElements) . $this->renderEndTag();
    }

    /**
     * Returns teh template path to use for mthe form templates.
     * @return string
     */
    protected function getTemplatePath() {
        return (file_exists($this->_sTemplateDir . '/form.tpl.html')) ? $this->_sTemplateDir : DOCUMENT_ROOT . Mjoelnir_Form::$_sDefaultTemplateDir;
    }

    /**
     * Configures the form to use https or not. If you call the function without a parameter given, https will be activated. To deactivate it, $value has to be false.
     * If an non-boolean value is given, false will be returned.
     * @param   bool    $value  Activate or deactivate the usage of https.
     * @return  bool
     */
    public function useSsl($value = true) {
        if (is_bool($value)) {
            $this->_bUseSsl = $value;
            return true;
        }

        return false;
    }

}

?>
