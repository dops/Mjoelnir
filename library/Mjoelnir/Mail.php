<?php

class Mjoelnir_Mail extends Zend_Mail
{
    protected $aExtensionToMimeType = array(
        'pdf'       => 'application/pdf',
        'default'   => 'application/octet-stream',
    )

    public function usePreconfiguredMail($sConfigName, $aAttachments = array()) {
//        $sClassName = 'Mail_' . $sConfigName . '_Config';
//        
//        $oMailConfig    = new $sClassName();
//        
//        $aFrom  = $oMailConfig->getFrom();
//        $this->setFrom($aFrom['address'], $aForm['name']);
////        $this->setSubject(str_replace(array_keys($aAttachments), $aAttachments, $oMailConfig->getSubject()));
////        $this->setBodyText(str_replace(array_keys($aAttachments), $aAttachments, $oMailConfig->getBodyText()));
////        $this->setBodyHtml(str_replace(array_keys($aAttachments), $aAttachments, $oMailConfig->getBodyHtml()));
//        
//        if (count($oMailConfig->getAutoCc()) > 0) {
//            foreach ($oMailConfig->getAutoCc() as $sAutoCc) {
//                $this->addCc($sAutoCc);
//            }
//        }
//        
//        if (count($oMailConfig->getAutoBcc()) > 0) {
//            foreach ($oMailConfig->getAutoBcc() as $sAutoBcc) {
//                $this->addBcc($sAutoBcc);
//            }
//        }
//        
//        if (count($oMailConfig->getAttachments()) > 0) {
//            foreach ($oMailConfig->getAttachments() as $$sAttachment) {
//                $aFileInfo  = pathinfo($aAttachments);
//                
//                $this->createAttachment(
//                        $sAttachment, 
//                        (isset($this->aExtensionToMimeType[$aFileInfo['extension']]) ? $this->aExtensionToMimeType[$aFileInfo['extension']] : $this->aExtensionToMimeType['default']), 
//                        Zend_Mime::DISPOSITION_ATTACHMENT, 
//                        Zend_Mail::ENCODING_BASE64, 
//                        $aFileInfo['basname']);
//            }
//        }
    }
}