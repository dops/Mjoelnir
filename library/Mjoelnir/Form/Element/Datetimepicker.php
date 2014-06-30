<?php

/**
 * Description of Text
 *
 * @author Michael Streb <kontakt@michael-streb.de>
 */
class Mjoelnir_Form_Element_Datetimepicker extends Mjoelnir_Form_Element_Abstract
{

    public function __construct($sName, $mValue, $aOptions = array(), $sTemplateDir = null, $sPrefix = '', $sSuffix = '') {
        parent::__construct($sName, $mValue, $aOptions, $sTemplateDir, $sPrefix, $sSuffix);

        if (isset($this->_aOptions['class']))   { $this->_aOptions['class']    .= ' datetimepicker'; }
        else                                    { $this->_aOptions['class']    = ' datetimepicker'; }
    }

    /**
     * Type definition needed in abstract class.
     * @var str
     */
    protected $_sType    = 'datetimepicker';
}