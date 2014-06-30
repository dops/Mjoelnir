<?php

include('systemLoader.php');

$oLogger    = Mjoelnir_Logger_Abstract::getLogger('LOGGER_CRAWL');

$aFilter    = array(
    'active'            => array('eq' => 1),
    'valid_from'        => array('lt' => time()),
    'valid_to'          => array('gt' => time()),
    'execution_state'   => array('eq' => 'open'),
    'repeat_condition'  => 'time_execution IS NULL 
        OR (`repeat` = \'minutely\' AND (FROM_UNIXTIME(time_execution, \'%Y%m%d%H%i\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y%m%d%H%i\')))
        OR (`repeat` = \'hourly\' AND (FROM_UNIXTIME(time_execution, \'%Y%m%d%H\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y%m%d%H\')))
        OR (`repeat` = \'daily\' AND (FROM_UNIXTIME(time_execution, \'%Y%m%d\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y%m%d\')))
        OR (`repeat` = \'weekly\' AND (FROM_UNIXTIME(time_execution, \'%Y%u\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y%u\')))
        OR (`repeat` = \'monthly\' AND (FROM_UNIXTIME(time_execution, \'%Y%m\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y%m\')))
        OR (`repeat` = \'yearly\' AND (FROM_UNIXTIME(time_execution, \'%Y\') < FROM_UNIXTIME(UNIX_TIMESTAMP(), \'%Y\')))
        OR (`repeat` = \'once\' AND `time_execution` IS NULL)
    ',
);
$aOrder = CrawlOrderModel::getAll(NULL, NULL, $aFilter);

if ($aOrder['count'] > 0) {
    $oGearmanClient = new GearmanClient();

    # Add default server (localhost).
    $oGearmanClient->addServer();
    
        foreach ($aOrder['rows'] as $oOrder) {
            $aWorkload      = array(
                'crawlOrderId'  => $oOrder->getId(),
            );
            
            if ($oOrder->getCrawlType() == 'GooglePlusLocal') {
                $sWorker    = 'worker_crawl_google_plus_local';
            }
            else {
                $sWorker    = 'worker_crawl_serp';
            }
            
            $oGearmanClient->doBackground($sWorker, serialize($aWorkload));
            $oLogger->log('Crawl-job created: ' . $oOrder->getCrawlType() . ', order-id: ' . $oOrder->getId(), Mjoelnir_Logger_Abstract::DEBUG);
            
            $oOrder->setExecutionState('job_created');
            $oOrder->save();
    }
}