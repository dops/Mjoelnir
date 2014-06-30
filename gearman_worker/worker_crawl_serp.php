<?php

include_once dirname(__FILE__) . '/../script/crawlerInit.php';

class Worker_Crawl_Serp
{
    function run(GearmanJob $oJob) {
        $oLogger        = Mjoelnir_Logger_Abstract::getLogger('LOGGER_CRAWL');
        $aCrawlerConfig = unserialize($oJob->workload());

        $oOrder = CrawlOrderModel::getInstance($aCrawlerConfig['crawlOrderId']);

        $oOrder->setExecutionState('working');
        $oOrder->setTimeExecution(time());
        $oOrder->save();

        $sCrawlerClassName      = 'Adzlocal_Crawler_Type_' . $oOrder->getCrawlType();
        $sCrawlerModelClassName = $oOrder->getCrawlType() . 'ResultModel';

        // Request crawler config
        $aParameters    = $oOrder->getAllParameters();
        foreach ($aParameters as $oParameter) {
            $aCrawlerConfig[$oParameter->getKey()]   = $oParameter->getValue();
        }

        $oLogger->log('Job found for "' . $sCrawlerClassName . '". Config: ' . print_r($aCrawlerConfig, true), Mjoelnir_Logger_Abstract::DEBUG);

        try {
            $oCrawler   = new $sCrawlerClassName();
            
            if ($aCrawlerConfig['keyphrase'] != 'selfcheck') {
                $oCrawler->setConfig($aCrawlerConfig);
                $oCrawler->execute($oJob);
                $oCrawler->saveResults($sCrawlerModelClassName);
            }
            else {
                $oCrawler->selfCheck();
            }

            $oOrder->setExecutionState('open');
        } catch (Adzlocal_Crawler_Exception $e) {
            $oOrder->setExecutionState('failed');
            $oOrder->setErrorCount($oOrder->getErrorCount() + 1);
            
            $sErrorMessage  = 'An error occured while executing Gearman job "Worker_Crawl_Serp": ' . $e->getMessage() . "\n" . $e->getTraceAsString();
            $oOrder->setErrorMessage($sErrorMessage);
            $oLogger->log($sErrorMessage . "\n\n" . $oOrder, $e->getCode());
        }
        
        $oOrder->save();

        $oLogger->log('Job done!', Mjoelnir_Logger_Abstract::DEBUG);
    }
}