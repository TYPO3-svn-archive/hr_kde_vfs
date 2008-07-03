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

//require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/class.tx_vfs_base.php');


class tx_fileadmin extends tx_vfs_base {
    
    /** Virtual rootdirectory, this will be prefixed by a string to make it easy for clients to identify
    the real rootfolder of the TYPO3 installation */
    var $rootdir = "fileadmin";    
    /** Directory relative to the TYPO3 rootdirectory with a trailing slash */
    var $rel_real_rootfolder = "fileadmin/";
    
    /** The absolute path to the TYPO3 rootfolder */
    var $path_to_rootdir;
    
    /**
    Constructor
    */
    function tx_fileadmin(){
            
        $this->path_to_rootdir  =   PATH_site.$this->rel_real_rootfolder;
            
    }

    function list_dir($path){
        //error_log(__LINE__.__FUNCTION__.$path,0);
        //echo "path= $path , path_to_rootdir = ".$this->path_to_rootdir."\n";
        
        if( ! is_dir($this->path_to_rootdir.$path)){
            XMLRPC_error(ERR_DOES_NOT_EXIST, XMLRPC_prepare("path does not exists or is not a directory:".$path));
            return; 
        }
        //echo __LINE__."\n";
        $files = array();
        $dir = opendir($this->path_to_rootdir.$path); 
        if(!$dir){ 
            XMLRPC_error(ERR_DOES_NOT_EXIST, XMLRPC_prepare("path does not exists:".$path));
            return; 
        } 
        //echo __LINE__."\n";
       clearstatcache();
        while($entry = readdir($dir)){ 
        //echo __LINE__."\n";
                if($entry == ".." || $entry == "."){
                    continue;
                }
                //echo "entry=".$this->path_to_rootdir.$entry."\n";
        //echo __LINE__." $entry \n";
                
                
                $file= array();
                $file['title'] = $entry;
                $file['uid'] = intval(0);
                $file['size'] = filesize($this->path_to_rootdir.$path."/".$entry);
                $file['modified'] = filemtime($this->path_to_rootdir.$path."/".$entry);
                $file['perms'] = fileperms($this->path_to_rootdir.$path."/".$entry);
                $file['owner'] = fileowner($this->path_to_rootdir.$path."/".$entry);
                $file['group'] = filegroup($this->path_to_rootdir.$path."/".$entry);
                
                if(is_file($this->path_to_rootdir.$path."/".$entry)){                            
                    $file['filetype'] = 'f';
                    $file['mime'] = $this->mimetype($this->path_to_rootdir.$path."/".$entry);        
                } 
                else  {
                    $file['filetype'] = 'd';
                    $file['mime'] = "inode/directory";
                }
                $files[] = $file;
                //print_r($file);
         //echo __LINE__."\n";
       
        
        }
 //print_r($files);
        XMLRPC_response(XMLRPC_prepare($files, 'array'));
            
        
    }
    function stat_url($path){
        $files = array();
        $file= array();
        $file['title'] = basename($path);
        $file['uid'] = intval(0);
        $file['size'] = filesize($this->path_to_rootdir.$path);
        $file['modified'] = filemtime($this->path_to_rootdir.$path);
        $file['perms'] = fileperms($this->path_to_rootdir.$path);
        $file['owner'] = fileowner($this->path_to_rootdir.$path);
        $file['group'] = filegroup($this->path_to_rootdir.$path);
        
        
        if(is_dir($this->path_to_rootdir.$path)){
            $file['filetype'] = 'd';
            $file['mime'] = "inode/directory";
        
        
        }else if(is_file($this->path_to_rootdir.$path)){
            $file['filetype'] = 'f';
            $file['mime'] = $this->mimetype($this->path_to_rootdir.$path);;
        
        }else{
             XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare(__LINE__.__FUNCTION__." cannot parse url:".$path));   return;     
        
        }
        $files[] = $file;

        XMLRPC_response(XMLRPC_prepare($files, 'array'));
           
     }
    /**
     
    */ 
    function get($path){
        if( ! is_file($this->path_to_rootdir.$path) ){
            XMLRPC_error(ERR_DOES_NOT_EXIST, XMLRPC_prepare("file does not exists or is a directory:".$path));
            return;     
            
        }
        if( ! is_readable($this->path_to_rootdir.$path) ){
            XMLRPC_error(ERR_COULD_NOT_READ, XMLRPC_prepare("file is not readable:".$path));
            return;     
            
        }
        
        
        //echo "path=".$this->path_to_rootdir.$path;
         $fh = fopen($this->path_to_rootdir.$path, 'rb');
         $ret['content'] = base64_encode(fread($fh, filesize($this->path_to_rootdir.$path)));
         fclose($fh);
         $ret['mime'] = $this->mimetype($this->path_to_rootdir.$path);;
         $ret['size'] = filesize($this->path_to_rootdir.$path);
         $ret['modified'] = filemtime($this->path_to_rootdir.$path);
            
        XMLRPC_response(XMLRPC_prepare($ret));
            
    
    
    }
    /**
    Overrides a file or create a new one if not exists 
    */ 
    function put($path, $data){
        $fh = fopen($this->path_to_rootdir.$path, 'wb');
        if($fh == false){
            XMLRPC_error(ERR_COULD_NOT_WRITE, XMLRPC_prepare("file is not writeable:".$path));
            return;     
        }
        fwrite($fh, base64_decode($data));
        fclose($fh);
        
        $this->clearCache();
        
        XMLRPC_response(XMLRPC_prepare(true));
         
    }

    function del($path){
        if(is_file($this->path_to_rootdir.$path) ){
            if( ! @ unlink($this->path_to_rootdir.$path) ){
                XMLRPC_error(ERR_CANNOT_DELETE, XMLRPC_prepare("could not delete the file:".$path));
                return; 
            }    
        }
        if(is_dir($this->path_to_rootdir.$path) ){
            if( ! @ rmdir($this->path_to_rootdir.$path) ){
                XMLRPC_error(ERR_COULD_NOT_RMDIR, XMLRPC_prepare("could not delete the directory:".$path));
                return; 
            }    
        }
        
        $this->clearCache();
        
        XMLRPC_response(XMLRPC_prepare(true));
         
    }
    function mkdir($path){
        if( ! @ mkdir($this->path_to_rootdir.$path) ){
                XMLRPC_error(ERR_COULD_NOT_MKDIR, XMLRPC_prepare("could not create directory:".$path));
            
        }
        $this->clearCache();
        
        XMLRPC_response(XMLRPC_prepare(true));
         
    }
    function chmod($path, $permissions){
        $perm = $permissions;
        if( ! @ chmod($this->path_to_rootdir.$path, $perm) ){
                XMLRPC_error(ERR_COULD_NOT_MKDIR, XMLRPC_prepare("could not chmod ($perm) for:".$path));
        }
        $this->clearCache();
        
        XMLRPC_response(XMLRPC_prepare(true));
         
    }

    function virt_root_dir(){
         $file= array();
                $file['size'] = filesize($this->path_to_rootdir);
                $file['modified'] = filemtime($this->path_to_rootdir);
                $file['perms'] = fileperms($this->path_to_rootdir);
                $file['owner'] = fileowner($this->path_to_rootdir);
                $file['group'] = filegroup($this->path_to_rootdir);
        
         return $file;
    }    
    

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_fileadmin.php'])     {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_fileadmin.php']);
}


?>