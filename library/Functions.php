<?php

/**
 * <p>This method tries to find the file for a givven class. If a file could be found, its path is returned, otherwise false.</p>
 * @param   string  $className  <p>The name of the class to find the file for.</p>
 * @return  string|boolean      <p>Returns the class file path or false if no file could be found.</p>
 */
function getPathToClass($className) {
    if (strpos($className, '\\')) {
        $className  = substr($className, strpos($className, '\\') + 1);
    }

    $fileExtension  = '.php';

    if (strpos($className, 'Controller') !== false && strpos($className, 'Controller') === (strlen($className) - 10)) {
        $pathToFile = PATH_CONTROLLER . $className . $fileExtension;
    }
    elseif (strpos($className, 'Model') !== false && strpos($className, 'Model') === (strlen($className) - 5)) {
        $pathToFile = PATH_MODEL . $className . $fileExtension;
    }
    elseif (strpos($className, '_Interface') === false && strpos($className, 'Interface') !== false && strpos($className, 'Interface') === (strlen($className) - 9)) {
        $pathToFile = PATH_INTERFACE . str_replace('_', '/', $className) . $fileExtension;
    }
    elseif (strpos($className, 'Config') !== false && strpos($className, 'Config') === (strlen($className) - 6)) {
        $pathToFile = PATH_CONFIG . str_replace('_', '/', $className) . $fileExtension;
    }
    elseif (strpos($className, 'Bootstrap') !== false && strpos($className, 'Bootstrap') === (strlen($className) - 9)) {
        $pathToFile = PATH_APPLICATION . 'Bootstrap' . $fileExtension;
    }
    else {
        if ($className == 'Smarty') {
            $fileExtension  = '.class.php';
            $pathToFile     = PATH_LIBRARY . 'Smarty/' . str_replace('_', '/', $className) . $fileExtension;
        }
        elseif (
                strpos($className, 'Smarty_') !== false
                || strpos($className, 'Gearman') !== false
                || strpos($className, 'worker_') !== false
        ) {
            return false;
        }
        else {
            $pathToFile = PATH_LIBRARY . str_replace('_', '/', $className) . $fileExtension;
        }
    }

    if (file_exists($pathToFile)) {
        return $pathToFile;
    }
    else {
        return false;
    }
}

/*
 * This is a function library with objectless functions.
 */

function array_diff_recursive($aArray1, $aArray2) {
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = array_diff_recursive($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            }
            else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        }
        else {
            $aReturn[$mKey] = $mValue;
        }
    }
   
    return $aReturn;
}


function renameArrayIndex(&$aArray, $mOldIndex, $mNewIndex) {
    // If the old or the new index is an array...
    if (is_array($mOldIndex) || is_array($mNewIndex)) {
        // ... the other one has to be an array too.
        if (!is_array($mOldIndex) || !is_array($mNewIndex)) {
            return false;
        }
        
        // If the number of indexes is not equal.
        if (count($mOldIndex) !== count($mNewIndex)) {
            return false;
        }
        
        foreach ($mOldIndex as $iKey => $mIndex) {
            $aArray[$mNewIndex[$iKey]] = $aArray[$mOldIndex[$iKey]];
            unset($aArray[$mOldIndex[$iKey]]);
        }
    }
    else {
        $aArray[$mNewIndex] = $aArray[$mOldIndex];
        unset($aArray[$mOldIndex]);
    }
    
    return $aArray;
}

/**
 * Creates a request string out of an array. The array needs to have only one dimension with simple key value pairs.
 * @param   array   $aRequestParams The request parameters.
 * @return  string  The complete wellformed request string.
 */
function getRequestString($aRequestParams) {
    $sRequestString = '?';
    $aTemp          = array();
    foreach ($aRequestParams as $sKey => $mValue) {
        $aTemp[]    = $sKey . '=' . urlencode(strval($mValue));
    }
    
    $sRequestString .= implode('&', $aTemp);
    
    return $sRequestString;
}

/**
 * Removes all non numeric characters from the given string.
 * @param   string  $sString    The string be clean.
 * @return  string  Removes the clean string.
 */
function removeNonNumericChars($sString) {
    return preg_replace('/[^\d]/', '', $sString);
}

/**
 * Opposing to number_format() this function converts a formated number to float. If only the number is given, it is formated to a float without decimal places. If the second
 * parameter ist given, the number ist formated with the given number of decimal places, normaly rounded.
 * @param   string  $sNumber        The number to format to float.
 * @param   string  $iDecimalPlaces The number of decimal places to round to.
 * @return  float   The float value of teh given number.
 */
function float_format($sNumber, $iDecimalPlaces = 0) {
    $iDecimalPlaces = (int) $iDecimalPlaces;
    $sPattern       = '/(,|\.)(\d+)$/';
    $aMatches       = array();
    if (preg_match($sPattern, $sNumber, $aMatches)) {
        $iDecimal   = $aMatches[2];
        // Calc decimal places
        if (strlen($iDecimal) > $iDecimalPlaces) {
            if (substr($iDecimal, $iDecimalPlaces, 1) >= 5) {
                $fDecimal   = floatval('0.' . substr($iDecimal + 1, 0, $iDecimalPlaces));
            }
            else {
                $fDecimal   = floatval('0.' . substr($iDecimal, 0, $iDecimalPlaces));
            }
        }
        else {
            $fDecimal   = floatval('0.' . str_pad($iDecimal, $iDecimalPlaces, '0'));
        }
        
        // Get full number
        $iNumber    = str_replace(array('.', ','), '', preg_replace($sPattern, '', $sNumber));
        
        // Add decimal places to full number.
        $fNumber    = $iNumber + $fDecimal;
    }
    else {
        // Get full number.
        $iNumber    = str_replace(array('.', ','), '', $sNumber);
        
        // Cast number to float.
        $fNumber    = floatval($iNumber);
    }
    
    return $fNumber;
}

function replaceUmlauts($sText)         //Funktion um Umlaute umzu wandeln
{
    $aSearch        = array("Ã€", "ÃŒ", "Ã¶", "Ã", "Ã", "Ã", "Ã", "â", " ", "ÂŽ", "-", "/", "&");
    $aReplacements  = array("ae","ue","oe","ss","Ae","Ue","Oe","","","","","","");

    return str_replace($aSearch, $aReplacements, $sText); 
}