<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Herbert Roider <herbert.roider@utanet.at>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/




require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/class.tx_server_base.php');
require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/class.tx_dev_tsconfig_help.php');


#define('XMLRPC_DEBUG',1);


class tx_dev extends tx_server_base {
    
    
    function request($methodName, $params){
        //echo "hallo das ist ein TEEST";
        // split the method name into parts:
        // vfs.put, or x.y.test.mach_was
        // the first part is the class
        $parts = t3lib_div::trimExplode(".", $methodName, 1);
        //print_r($parts);
        
        if(count($parts) != 1){
            $this->error(ERR_INTERNAL, "unknown function: ".$methodName);
        
        }
        //echo "methodName=".$methodName."\n";
        if(0 == strcmp($methodName, 'verify_typoscript')){
            $this->verify_typoscript($params);
            return;
        }
        if(0 == strcmp($methodName, 'php_tune_beauty')){
            $this->php_tune_beauty($params);
            return;
        }
        if(0 == strcmp($methodName, 'get_ts_config_objects')){
            $this->get_ts_config_objects($params);
            return;
        }
        if(0 == strcmp($methodName, 'get_ts_config_objects_properties')){
            $this->get_ts_config_objects_properties($params);
            return;
        }
        if(0 == strcmp($methodName, 'clear_cache')){
            $this->clear_cache($params);
            return;
        }
        
        
        $this->error(ERR_INTERNAL, "unknown function: ".$methodName);
        
        //return "";    
    }
    function verify_typoscript($params){
          //$this->error(ERR_INTERNAL, "unknown function: ".$methodName);
        //print_r($params);
        $ts = base64_decode($params[1]);
        //echo "typoscript=".$ts." \n";
        $tsparser = t3lib_div::makeInstance("t3lib_TSparser");
        $tsparser->lineNumberOffset=0;
        //echo __LINE__."\n";
        $tsparser->parse($ts );
        
        //$tsparser->lineNumberOffset=0;
        //$formattedContent = $tsparser->doSyntaxHighlight($ts);
//echo "formattedContent= $formattedContent \n";
        
        //echo __LINE__."\n";
        //echo "errors=\n";
        //print_r($tsparser->errors);
        //echo __LINE__."\n";
        
        $ret = array();
        foreach($tsparser->errors as $error){
             $ret[] = array(
                'text' => $error[0],
                'level' => $error[1],
                'line_nr' => $error[2],
                'line_offset' => $error[3],
             );
        }
        XMLRPC_response(XMLRPC_prepare($ret));
    }
    function php_tune_beauty($params){
        //echo "php_tune_beauty";
        
        require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/class.tx_hr_kde_vfs_tunebeautify.php');
        //echo "php_tune_beauty";
                //require_once './class.tx_extdeveval_tunebeautify.php';
        $phpcode = base64_decode($params[1]);
        $beauty = & new tx_hr_kde_vfs_tuneBeautify();
        $beautified_phpcode = $beauty->beautify($phpcode);
        
        //$beautified_phpcode = "hallo ein Tess";
        $size = strlen($beautified_phpcode);
        //echo "beautified_phpcode= $beautified_phpcode \n";
        $ret['content'] = base64_encode($beautified_phpcode);
        $ret['mime'] = "application/octet-stream"; //not used, but set for compatibility
        $ret['size'] = $size;
        $ret['modified'] = 0;// don't know
        
        XMLRPC_response(XMLRPC_prepare($ret));
    }
    function get_ts_config_objects($params){
        //echo "hallo ein Test";
        //$ret = array('hallo' => "test");
        $tsconfig = t3lib_div::makeInstance('tx_dev_tsconfig_help');
        $ts_reference = $tsconfig->get_ts_config_objects($params);
        if(false === $ts_reference){
             $this->error(ERR_INTERNAL, "No typoscript help available, maybe too old TYPO3 version.");

        
        }
        XMLRPC_response(XMLRPC_prepare($tsconfig->get_ts_config_objects($params) ));

    
    
    }
    function get_ts_config_objects_properties($params){
        $tsconfig = t3lib_div::makeInstance('tx_dev_tsconfig_help');
        XMLRPC_response(XMLRPC_prepare($tsconfig->get_ts_config_objects_properties($params) ));
        
    
    
    }
    function clear_cache($params){
        $this->clearCache();
        XMLRPC_response(XMLRPC_prepare(true));
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_dev.php'])     {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_dev.php']);
}


?>