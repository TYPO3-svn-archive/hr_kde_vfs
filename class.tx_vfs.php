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




require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/class.tx_vfs_base.php');


#define('XMLRPC_DEBUG',1);


class tx_vfs extends tx_vfs_base {
    
    
    function request($methodName, $params){

        //echo "methodName=".$methodName;
        
        if (is_array($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['process_root_folder'])) {
                foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['process_root_folder'] as $_classRef) {
                        $this->procObj[] = & t3lib_div::getUserObj($_classRef);
                        //var_dump($this->procObj);
                }
        } 
        
        
        
        
        // Authentication has passed if we get here. Decode the request
        switch($methodName) {
        case 'list_dir':
            $this->list_dir($params[0]);
            return;
            break;
        case 'stat_url':
            $this->stat_url($params[0]);
            return;
            break;
        case 'get':
            $this->get($params[0]);
            return;
           break;
        case 'put':
            $this->put($params[0], $params[1]);
            return;
            break;
        case 'del':
            $this->del($params[0]);
            return;
            break;
         case 'mkdir':
            $this->mkdir($params[0]);
            return;
            break;
         case 'chmod':
            $this->chmod($params[0], $params[1]);
            return;
            break;
       
        }
        
        XMLRPC_error(ERR_INTERNAL, XMLRPC_prepare("unknown function: ".$methodName));
        
        //return "";    
    }
        function list_dir( $path){
            //$ret = $this->root_path($path);
            //echo "list_dir".$path."\n";
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            if(! $rootdir ){
                foreach($this->procObj as $obj){
                    $file = $obj->virt_root_dir();
                    $file_overwrite  = array(
                                    'title' => $this->rootpathprefix.$obj->rootdir, 
                                    'uid' => 0, 
                                    'filetype' => 'd',
                                    'mime' => "inode/directory",
                                    );
                    $files[] = array_merge($file, $file_overwrite);
                }
                XMLRPC_response(XMLRPC_prepare($files, 'array'));
                return;
            }
            foreach($this->procObj as $obj){
                //echo "list_dir rootdir = $rootdir \n";
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    //echo "list_dir => rootdir = $rootdir \n";
                    //echo "implode=".implode("/", $parts)."\n";
                    $obj->list_dir( implode("/", $parts));
                    return;
                }
            }
         }
         function stat_url($path ){
            //$ret = $this->root_path($path);
            //echo "stat_url:".$path;
            $parts = t3lib_div::trimExplode("/", $path, 1);
            // no path, so it is a nameless directory:
            if(count($parts) == 0 ){
                  $files[] = array('title' => "", 
                                    'uid' => 0, 
                                    'filetype' => 'd',
                                    'mime' => "inode/directory",
                                    'size' => 0,
                                    'modified' => 0,
                                    'perms' => 511,
                                    'owner' => "",
                                    'group' => "",
                                   
                                    );
                XMLRPC_response(XMLRPC_prepare($files, 'array'));
                return;
            }            
            // parts contains min. 1 directory, this is the root-directory:
            $rootdir = array_shift($parts);
            // we have only a root directory, but no more subdirectories:
            if(count($parts) == 0){
                foreach($this->procObj as $obj){
                    if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir)){
                        $file = $obj->virt_root_dir();
                        $file_overwrite  = array(
                                        'title' => $this->rootpathprefix.$obj->rootdir, 
                                        'uid' => 0, 
                                        'filetype' => 'd',
                                        'mime' => "inode/directory",
                                        );
                        $files[] = array_merge($file, $file_overwrite);
                     }
                }
                XMLRPC_response(XMLRPC_prepare($files, 'array'));
                return;
            
            
            
            }
            
            // We have subdirectories:
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->stat_url( implode("/", $parts));
                    return;
                }
            }
        }
       
         function get($path ){
            //$ret = $this->root_path($path);
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            //echo "rootdir=".$rootdir;
            if(! $rootdir ){
               XMLRPC_error(ERR_IS_DIRECTORY, XMLRPC_prepare("cann not do get because it is the root directory: ".$rootdir.", ".$methodName));
               return;
            }
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->get( implode("/", $parts));
                    return;
                }
            }
        }        
         function put($path, $content ){
            //$ret = $this->root_path($path);
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            //echo "rootdir=".$rootdir;
            if(! $rootdir ){
               XMLRPC_error(ERR_IS_DIRECTORY, XMLRPC_prepare("cann not do put because it is the root directory: ".$rootdir.", ".$methodName));
               return;
            }
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->put( implode("/", $parts), $content);
                    return;
                }
            }
        }        
        
        function del($path ){
            //$ret = $this->root_path($path);
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            //echo "rootdir=".$rootdir;
            if(! $rootdir ){
               XMLRPC_error(ERR_IS_DIRECTORY, XMLRPC_prepare("cann not do put because it is the root directory: ".$rootdir.", ".$methodName));
               return;
            }
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->del( implode("/", $parts));
                    return;
                }
            }
        }                
         
        function mkdir($path ){
            //$ret = $this->root_path($path);
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            //echo "rootdir=".$rootdir;
            if(! $rootdir ){
               XMLRPC_error(ERR_IS_DIRECTORY, XMLRPC_prepare("can not do put because it is the root directory: ".$rootdir.", ".$methodName));
               return;
            }
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->mkdir( implode("/", $parts));
                     return;
                }
            }
        }                
        
        function chmod($path, $permissions ){
            //$ret = $this->root_path($path);
            $parts = t3lib_div::trimExplode("/", $path, 1);
            $rootdir = array_shift($parts);
            //echo "rootdir=".$rootdir;
            if(! $rootdir ){
               XMLRPC_error(ERR_IS_DIRECTORY, XMLRPC_prepare("can not do put because it is the root directory: ".$rootdir.", ".$methodName));
               return;
            }
            foreach($this->procObj as $obj){
                if(0 == strcmp($this->rootpathprefix.$obj->rootdir, $rootdir) ){
                    $obj->chmod( implode("/", $parts), $permissions);
                     return;
                }
            }
        }                
        

}




?>