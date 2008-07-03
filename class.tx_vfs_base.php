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


#define('XMLRPC_DEBUG',1);


class tx_vfs_base extends tx_server_base {
    /** Virtual rootdirectory */
    var $rootdir = "templates";    
    
    
    
    /** this is necessary for the client to identify the path to TYPO3.
    The client parse the path: digits.digits.digits.some_title
    f.e.:
    eagle.lan/dummy-4.0.1/0.0.0.templates/000.1.0.page.root
    TYPO3 is in /dummy-4.0.1/ installed.
    The current client need this prefix in that form. If this change, the clients won't work!
    */
    var $rootpathprefix = "0.0.0.res.";
       
    /**
    Constructor
    */
    function tx_vfs_base(){
            
        $this->path_to_rootdir  =   PATH_site.$this->rel_real_rootfolder;
            
    }    

    function list_dir( $path){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action:".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }
    function stat_url($path ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }
    
    function get($path ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }        
    function put($path, $content ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
    
    }        
    
    function del($path ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }                
        
    function mkdir($path ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }                
    
    function chmod($path, $permissions ){
            XMLRPC_error(ERR_UNSUPPORTED_ACTION, XMLRPC_prepare("unsupported action".__FUNCTION__." for this directory: ".$this->rootpathprefix.$this->rootdir));                        
        
    }                
    
    
    
    
    /**
    not used
    
    */
    function get_site_root_url() {
    $ret = array('site_root_url' => t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
    XMLRPC_response(XMLRPC_prepare($ret));

    }
    /**
    Try to determine the mimetype. Maybe there exists better solutions.
    This is not plattform independend.        
    */
    function mimetype($file){
        $type= @ exec("file -i -b $file");
        //echo $type."<br/>\n";   // just comment this line
        $split=split(";",$type);
        //print_r($split);
        $type=trim($split[0]); 
        if(strlen($type) == 0){
            $type = "application/octet-stream";
        }
        return $type;  
    
    
    }
    /**
    This values can be set for the virtual root folder which appears in the first level.
    Just overwrite this function in your own class.
    But the root folder must be a directory!
    */
    function virt_root_dir(){
        $file = array(
                        'size' => 0,
                        'modified' => 0,
                        'perms' => 511,
                        'owner' => "",
                        'group' => "",
                        
                        );
         return $file;
    
    
    
    }
    

}




?>