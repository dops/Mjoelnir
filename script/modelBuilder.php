<?php

/*
 * This script automatically builds a model class for a given table based on a model template.
 */

if ((!isset($argv[1]) || $argv[1] == '?') || !isset($argv[2]) || !isset($argv[3])) {
    echo 'Please state the following parameters:' . "\n\t1. environment (development | stage | production)\n\t2. tablename\n\t3. modelname\n";
    die();
}

if (!isset($_SERVER['APPLICATION_ENV']))    { $_SERVER['APPLICATION_ENV'] = $argv[1]; }
if (empty($_SERVER['DOCUMENT_ROOT']))       { $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__); }
$_SERVER['REQUEST_URI'] = '';

$iStartTime = microtime(true);

// Load initial includes
include('initialIncludes.php');

$sTableName         = $argv[2];
$sModelName         = $argv[3];
$sTabelNameConst    = 'TABLE_' . strtoupper($sTableName);
$sUniqueIdField     = $sTableName . '_id';

// First check if model does not already exist
$sFileName  = dirname(__FILE__) . '/../application/model/' . $sModelName . '.php';
if (file_exists($sFileName)) {
    die('Model does already exist!' . "\n\n");
}
else {
    echo 'Model does not exist an will be build...' . "\n";
}

$oDb    = Mjoelnir_Db::getInstance();

$aFields    = $oDb->query('SHOW COLUMNS FROM ' . $sTableName)->fetchAll();

// build data array, get and set methods
$sDataArray     = '';
$sGetMethods    = '';
$sSetMethods    = '';
foreach ($aFields as $iKey => $aField) {
    // Build data array
    $sDataArray  .= "\n" . '        \'' . $aField['Field'] . '\' => null,';
   
    if (!in_array($aField['Field'], array($sUniqueIdField, 'time_insert', 'time_update'))) {
        // Build get and method
        $sTempFieldName = str_replace('-', '_', $aField['Field']);
        $aNameParts = explode('_', $sTempFieldName);
        foreach ($aNameParts as &$sValue)   { $sValue = ucfirst(strtolower($sValue)); }
        $sGetMethodName = 'get' . implode('', $aNameParts);
        $sSetMethodName = 'set' . implode('', $aNameParts);

        // Get and set id is not needed, because it is in abstract class.
        if ($sGetMethodName == 'getId') { continue; }

        $sGetMethods    .= '

    public function ' . $sGetMethodName . '() {
        return $this->_aData[\'' . $aField['Field'] . '\'];
    }';

        $sSetMethods    .= '

    public function ' . $sSetMethodName . '($mValue) {
        if ($this->valueIsValid(\'' . $aField['Field'] . '\', $mValue)) {
            $this->_aData[\'' . $aField['Field'] . '\'] = $mValue;
            return true;
        }
        return false;
    }';
    }
}

// Insert all into class template
$oView  = new Mjoelnir_View();
$oView->setTemplateDir(dirname(__FILE__));

$oView->assign('sModelName', $sModelName);
$oView->assign('sTableName', $sTableName);
$oView->assign('sUniqueIdField', $sUniqueIdField);
$oView->assign('sTableNameConst', $sTabelNameConst);
$oView->assign('sDataArray', $sDataArray);
$oView->assign('sGetMethods', $sGetMethods);
$oView->assign('sSetMethods', $sSetMethods);

$sFile  = $oView->fetch('modelBuilderTemplate.tpl.php');
file_put_contents($sFileName, $sFile);

echo 'Model ist created. Adding table name const to database config...' . "\n";

// Adding constant to db config
$sConfig    = file_get_contents(PATH_CONFIG . 'Db/Adzlocal/Config.php');

if (!preg_match('/' . $sTabelNameConst . '/is', $sConfig)) {
    $sConfig    = str_replace('// MODEL_BUILDER_TABLE_NAME_CONST //', 'const ' . $sTabelNameConst . ' = \'' . $sTableName . '\';' . "\n" . '    // MODEL_BUILDER_TABLE_NAME_CONST //', $sConfig);
    file_put_contents(PATH_CONFIG . 'Db/Adzlocal/Config.php', $sConfig);
}

echo 'Done!' . "\n\n";