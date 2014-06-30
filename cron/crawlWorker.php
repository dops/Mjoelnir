<?php

include 'systemLoader.php';

# Create our worker object.
$oWorker= new GearmanWorker();
 
# Add default server (localhost).
$oWorker->addServer();
 
# Register function "reverse" with the server.
$oWorker->addFunction("crawl", "crawl");
 
while (1)
{
  print "Waiting for job...\n";
 
  $ret= $oWorker->work();
  if ($oWorker->returnCode() != GEARMAN_SUCCESS)
    break;
}
 
function crawl(GearmanJob $oJob) {
    $aCrawlerConfig  = unserialize($oJob->workload());
    
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
    
    echo 'Job found for "' . $sCrawlerClassName . '". Config: ' . print_r($aCrawlerConfig, true) . "\n";
    
    $oCrawler   = new $sCrawlerClassName();
    $oCrawler->setConfig($aCrawlerConfig);
    $oCrawler->execute($oJob);
    $oCrawler->saveResults($sCrawlerModelClassName);
    
    $oOrder->setExecutionState('open');
    $oOrder->save();
    
    echo 'Job done!' . "\n";
}