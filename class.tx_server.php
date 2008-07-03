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

ini_set("include_path", ini_get('include_path').':'.t3lib_extMgm::extPath("hr_kde_vfs") );

include("XML/Server.php");


#define('XMLRPC_DEBUG',1);


class tx_server  extends XML_RPC_Server {
    var $procObj ;
    
    function tx_server()
    {
        global $HTTP_RAW_POST_DATA;

        if ($debug) {
            $this->debug = 1;
        } else {
            $this->debug = 0;
        }

        $this->dmap = $dispMap;

//         if ($serviceNow) {
//             $this->service();
//         } else {
//             $this->createServerPayload();
//             $this->createServerHeaders();
//         }
    }    
    function main(){
        global $HTTP_RAW_POST_DATA, $BE_USER;
        
        $this->service();
    }
  
     function parseRequest($data = '')
    {
        global $XML_RPC_xh, $HTTP_RAW_POST_DATA,
                $XML_RPC_err, $XML_RPC_str, $XML_RPC_errxml,
                $XML_RPC_defencoding, $XML_RPC_Server_dmap, $BE_USER;

        if ($data == '') {
            $data = $HTTP_RAW_POST_DATA;
        }

        $this->encoding = XML_RPC_Message::getEncoding($data);
        $parser_resource = xml_parser_create($this->encoding);
        $parser = (int) $parser_resource;

        $XML_RPC_xh[$parser] = array();
        $XML_RPC_xh[$parser]['cm']     = 0;
        $XML_RPC_xh[$parser]['isf']    = 0;
        $XML_RPC_xh[$parser]['params'] = array();
        $XML_RPC_xh[$parser]['method'] = '';
        $XML_RPC_xh[$parser]['stack'] = array();    
        $XML_RPC_xh[$parser]['valuestack'] = array();   

        $plist = '';

        // decompose incoming XML into request structure

        xml_parser_set_option($parser_resource, XML_OPTION_CASE_FOLDING, true);
        xml_set_element_handler($parser_resource, 'XML_RPC_se', 'XML_RPC_ee');
        xml_set_character_data_handler($parser_resource, 'XML_RPC_cd');
        if (!xml_parse($parser_resource, $data, 1)) {
            // return XML error as a faultCode
            $r = new XML_RPC_Response(0,
                                      $XML_RPC_errxml+xml_get_error_code($parser_resource),
                                      sprintf('XML error: %s at line %d',
                                              xml_error_string(xml_get_error_code($parser_resource)),
                                              xml_get_current_line_number($parser_resource)));
            xml_parser_free($parser_resource);
            return $r;
        } elseif ($XML_RPC_xh[$parser]['isf']>1) {
            $r = new XML_RPC_Response(0,
                                      $XML_RPC_err['invalid_request'],
                                      $XML_RPC_str['invalid_request']
                                      . ': '
                                      . $XML_RPC_xh[$parser]['isf_reason']);
            xml_parser_free($parser_resource);
            return $r;
        } 
        xml_parser_free($parser_resource);
        $m = new XML_RPC_Message($XML_RPC_xh[$parser]['method']);
        // now add parameters in
        for ($i = 0; $i < sizeof($XML_RPC_xh[$parser]['params']); $i++) {
            // print '<!-- ' . $XML_RPC_xh[$parser]['params'][$i]. "-->\n";
            $plist .= "$i - " . var_export($XML_RPC_xh[$parser]['params'][$i], true) . " \n";
            $m->addParam($XML_RPC_xh[$parser]['params'][$i]);
        }

        if ($this->debug) {
            XML_RPC_Server_debugmsg($plist);
        }

        // now to deal with the method
        $methName = $XML_RPC_xh[$parser]['method'];
        if (strpos($methName, 'system.') === 0) {
            $dmap = $XML_RPC_Server_dmap;
            $sysCall = 1;
        } else {
            $dmap = $this->dmap;
            $sysCall = 0;
        }



        // Decode the incoming request
        //$xmlrpc_request = XMLRPC_parse($HTTP_RAW_POST_DATA);
        //$methodName = XMLRPC_getMethodName($xmlrpc_request);
        //$params = XMLRPC_getParams($xmlrpc_request);
        
        
        $methodName = $methName;
        $anz_params = $m->getNumParams();
        $params = array();
        for($i=0;$i<$anz_params;$i++){
            $par = $m->getParam($i);
            $params[] =  $par->scalarval();
        }
        
        
        
        
        // this functions can be called without login:
        switch($methodName) {
        case 'login'://fallthrou is intend
        case '': 
        case 'vfs.login_status':// only for compatibility with older clients
            $this->login_status();
            die();
            break;
        }       
        
        // only admin users have access beyond this point
        if ($BE_USER->user['admin'] != 1) {
            XMLRPC_error(ERR_ACCESS_DENIED, XMLRPC_prepare("only be_user who is admin is allowed to access"));
            die();
        }
        // split the method name into parts:
        // vfs.put, or x.y.test.mach_was
        // the first part is the class
        $parts = t3lib_div::trimExplode(".", $methodName, 1);
        //print_r($parts);
        if(count($parts) > 1){
            $base_class = array_shift($parts);  
            //echo "base_class=".$base_class;
            $methodName = implode("/", $parts);
            //echo "methodName=".$methodName."\n";
            if (is_array($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['response_class'])) {
                    //echo "suchen von basisklasse\n";
                    foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['response_class'] as $key => $_classRef) {
                            //echo "key= $key, $base_class \n";
                            if(0 == strcmp($key, $base_class)){
                                //echo "call usermethod\n";
                                $this->procObj = & t3lib_div::getUserObj($_classRef);
                                $this->procObj->request($methodName, $params);
                                die();
                            }
                            //var_dump(
                    }
                    XMLRPC_error(ERR_SERVICE_NOT_AVAILABLE, XMLRPC_prepare("no matching handler class defined"));
                    die();
                
            }         
            XMLRPC_error(ERR_SERVICE_NOT_AVAILABLE, XMLRPC_prepare("there are no handler classes defined"));
            die();
       
        }

        XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unknown method"));
        
        die();
        //return "";    
    }
   
    function login_status() {
        
        header("Location:".t3lib_div::getIndpEnv('TYPO3_SITE_URL')."typo3/index.php?hr_kde_vfs=1");
        exit();
        //XMLRPC_response(XMLRPC_prepare($ret));

        }
            

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_server.php'])     {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_server.php']);
}



?>