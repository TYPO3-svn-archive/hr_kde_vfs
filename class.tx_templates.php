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


class tx_templates extends tx_vfs_base {
    
    var $rootdir = "templates";    

        
        
        /**
         Example Url:
         256.1.0.page.root
         <sorting>.<page uid>.<template uid>.<"page" oder "setup" oder "constants" oder "res" für Dateien>.<title> 
         the title for setup, constants and pages have to be urlencoded, 
         but the title of filenames of resources must not urlencoded.
         */
         
         function getDirectory($page_uid) {
                // Die Seiten sind die Directories:
                $pages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title,doktype,sorting,tstamp', 'pages', 'pid='.intval($page_uid) . $GLOBALS['TSFE']->sys_page->deleteClause('pages'), '', 'sorting');
                $sorting = 0;
                $files = array();
                /* 
                pages (shown as directories)
                */
                foreach($pages as $page){
                    $title = sprintf("%03u",$sorting++).'.'.$page['uid'].'.'.'0'.'.page.'.urlencode($page['title']);
                    $files[] = array('title' => $title, 
                                    'uid' => intval($page['uid']), 
                                    'filetype' => 'd',
                                    'mime' => "inode/directory",
                                    'size' => 0,
                                    'modified' => $page['tstamp'],
                                    'perms' => 511,
                                    'owner' => "",
                                    'group' => "",
                                   );
                }
                
                $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows (
                        'uid , title , resources, sorting, constants, config, tstamp',
                        'sys_template',
                        'pid='.intval($page_uid).$GLOBALS['TSFE']->sys_page->deleteClause('sys_template') 
                );
                
                foreach($templates as $templ){
                    $title_prefix = sprintf("%03u",$sorting++).'.'.$page_uid.'.'.$templ['uid'].'.';
                    /* setup (config) shown as textfiles
                    */
                    $files[] = array('title' => $title_prefix.'setup.'.urlencode($templ['title']).".ts", 
                                     'uid' => intval('0'), 
                                     'filetype' => 'f',
                                     'mime' => 'text/plain',
                                     'size' => strlen($templ['config']),
                                     'modified' => $templ['tstamp'],
                                     'perms' => 511,
                                     'owner' => "",
                                     'group' => "",
                                    );
                    /* constants shown as textfiles
                    */
                    $files[] = array('title' => $title_prefix.'constants.'.urlencode($templ['title']).".ts", 
                                     'uid' => intval(0), 
                                     'filetype' => 'f',
                                     'mime' => 'text/plain',
                                     'size' => strlen($templ['constants']),
                                     'modified' => $templ['tstamp'],
                                     'perms' => 511,
                                     'owner' => "",
                                     'group' => "",
                                     );
                    
                    /* resourcefiles:
                    */
                    $resources = t3lib_div::trimExplode(',', $templ['resources']);
                    foreach($resources as $filename){
                        if(strlen($filename)){
                            $fileinfo = $this->get_file_info($filename);
                            // no such file found:
                            if( ! is_array($fileinfo)){
                                continue;
                            }
                            //print_r($fileinfo);
                            $file= array();
                            $file['title'] = $title_prefix.'res.'.$filename;
                            $file['uid'] = intval(0);
                            $file['filetype'] = 'f';
                            $file['mime'] = $fileinfo['mime'];
                            $file['size'] = $fileinfo['size'];
                            $file['modified'] = $fileinfo['modified'];
                            $file['perms'] = $fileinfo['perms'];
                            $file['owner'] = $fileinfo['owner'];
                            $file['group'] = $fileinfo['group'];
                         
                            
                            $files[] = $file;
                        }
                        
                    }
                 }
                 //print_r($files);
                 return $files;
                   
                  
        }
        function list_dir($path){
            error_log(__LINE__.__FUNCTION__.$path,0);
            if(0 == strcmp($path, "/") || strlen($path) == 0){
                 $files = $this->getDirectory(0);
               
            
            }else{
                $pathinfo = $this->parse_path($path);
                if($pathinfo == false){
                            XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("cannot parse the path:".$path));
                            return;
                
                }
                //krsort($pathinfo);
                if($pathinfo[count($pathinfo)-1]['type'] != 'page'){
                            XMLRPC_error(ERR_IS_FILE, XMLRPC_prepare("This is not a Directory (page):".$path));
                            return;
                }
                if($pathinfo[count($pathinfo)-1]['templ_uid'] != 0){
                            XMLRPC_error(ERR_IS_FILE, XMLRPC_prepare("This is not a Directory (page):".$path));
                            return;
                }
                // The last entry in $pathinfo is the last element in the path:
                $files = $this->getDirectory($pathinfo[count($pathinfo)-1]['page_uid']);
            }
             XMLRPC_response(XMLRPC_prepare($files, 'array'));
            
        
        }
        function stat_url($url){
            $pathinfo = $this->parse_path($url);
            if($pathinfo == false){
                        error_log($url,0);
                        //print_r($pathinfo);
                        XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("cannot parse url:".$url));
                        return;
            
            }
            //print_r($pathinfo);
            $entry = $pathinfo[count($pathinfo)-1];
            //print_r($entry);    
            
            //echo "entry=";
            //print_r($entry);
            if(0 == strcmp($entry['type'], 'page')){
                /*
                pagees (this are shown as directories)
                */
                $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,title,doktype,sorting,tstamp', 'pages', 'uid='.$entry['page_uid'] . $GLOBALS['TSFE']->sys_page->deleteClause('pages'), 'sorting');
                $page = $rows[0];
                //print_r($page);
                if( ! is_array($page)){
                    XMLRPC_error(ERR_INTERNAL_SERVER, XMLRPC_prepare("cannot find file:".$url));
                    return ;
                }
            
                $title = $page['sorting'].'.'.$page['uid'].'.'.'0'.'.page.'.urlencode($page['title']);
                $file = array('title' => $title,
                                'uid' => intval($page['uid']), 
                                'filetype' => 'd',
                                'mime' => "inode/directory",
                                'size' => 0,
                                'modified' => $page['tstamp'],
                                'perms' => 511,
                                'owner' => "",
                                'group' => "",
                                
                                );
                
                
                //print_r( $file);
            
        
            }else if(0 == strcmp($entry['type'], 'res')){ // files: setup, constants, res
                /* 
                Resource Files 
                */
                    /** \todo Abfrage nach dem Filenamen !!! auch einbauen*/  
                $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows (
                        'uid , title , resources, sorting, tstamp',
                        'sys_template, pid',
                        'uid='.$entry['templ_uid'].$GLOBALS['TSFE']->sys_page->deleteClause('sys_template') 
                );
               if( ! is_array($templates) || count($templates) == 0){
                    XMLRPC_error(ERR_INTERNAL_SERVER, XMLRPC_prepare("cannot find file:".$url));
                   return ;
                }
                $templ = $templates[0];
                $title_prefix = $templ['sorting'].'.'.$templ['pid'].'.'.$templ['uid'].'.';
                
                $resources = t3lib_div::trimExplode(',', $templ['resources']);
                //echo "title=".$entry['title'];
                //print_r($resources);
                foreach($resources as $filename){
                    //echo "filename=".$filename;
                    if(0 == strcmp($entry['title'] , $filename)){
                        
                        $fileinfo = $this->get_file_info($entry['title']);
                        // no such file found:
                        if( ! is_array($fileinfo)){
                           XMLRPC_error(ERR_DOES_NOT_EXIST, XMLRPC_prepare("cannot find file:".$url));
                           return;
                        }
                        $file = array('title' => $title_prefix.'res.'.$filename,
                                    'uid' => intval("0"),
                                    'filetype' => 'f',
                                    'mime' => $fileinfo['mime'],
                                    'size' => $fileinfo['size'],
                                    'modified' => $fileinfo['modified'],
                                    'perms' => $fileinfo['perms'],
                                    'owner' => $fileinfo['owner'],
                                    'group' => $fileinfo['group'],
                                
                                
                                );
                        
                    }
                }
            }else if(0 == strcmp($entry['type'], 'setup') || 0 ==  strcmp($entry['type'], 'constants')) {
                /*
                setup(config) or constants
                */
                $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows (
                        'uid , title , resources, sorting, config, constants, tstamp',
                        'sys_template, pid',
                        'uid='.$entry['templ_uid'].$GLOBALS['TSFE']->sys_page->deleteClause('sys_template') 
                );
                if( ! is_array($templates) || count($templates) == 0){
                    XMLRPC_error(ERR_INTERNAL_SERVER, XMLRPC_prepare("cannot find file:".$url));
                    return false;
                }
                $templ = $templates[0];
                
                $title_prefix = $templ['sorting'].'.'.$templ['pid'].'.'.$templ['uid'].'.';
                $file = array('title' => $title_prefix.$entry['type'].'.'.urlencode($templ['title']).".ts", 
                                'uid' => intval('-1'), 
                                'filetype' => 'f',
                                'mime' => 'text/plain',
                                'modified' => $templ['modified'],
                                 'perms' => 511,
                                'owner' => "",
                                'group' => "",
                               );
                    switch($entry['type']){
                    case 'setup':
                        $file['size'] = strlen($templ['config']);
                    break;
                    case 'constants':
                        $file['size'] = strlen($templ['constants']);
                    
                    break;
                    default:
                    
                    }
             }else {
                  XMLRPC_error(ERR_COULD_NOT_STAT, XMLRPC_prepare("url is not a resource or setup or page or constants:".$url));
                  return;
                
             
             }
            
            $files = array($file);
            XMLRPC_response(XMLRPC_prepare($files, 'array'));
           
        }
        function get($url){
            $ret = array();
            $pathinfo = $this->parse_path($url);
            //print_r($pathinfo);
            if(! $pathinfo){
                    //error_log(__LINE__." fehler : ".$url,0);
                    //print_r($pathinfo);
                    XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("\"".__FUNCTION__."\" cannot parse url:".$url));
                    return;
             }
             $entry = $pathinfo[count($pathinfo)-1];
                 $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                        'constants, config, resources, tstamp', 'sys_template', 'uid='.intval($entry['templ_uid']) . $GLOBALS['TSFE']->sys_page->deleteClause('sys_template'), 'uid'
                );
             if(empty($templates)){
                        XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("Specified template ($templateuid) doesn't exist"));
                        return;
             }
              $templ = $templates[0];
              switch($entry['type']){
              case 'setup':
                    $ret['content'] = base64_encode($templ['config']);
                    $ret['mime'] = "text/plain";
                    $ret['size'] = strlen($ret['content']);
                    $ret['modified'] = $templ['tstamp'];
                    break;
               case 'constants':
                   $ret['content'] = base64_encode($templ['constants']);
                   $ret['mime'] = "text/plain";
                   $ret['size'] = strlen($ret['content']);
                   $ret['modified'] = $templ['tstamp'];
                   
                
                
                break;
                case 'res':
                    //$mime = mime_content_type("uploads/tf/".$file);
                    
                    $fileinfo = $this->get_file_info($entry['title']);
                    // no such file found:
                    if( ! is_array($fileinfo)){
                        XMLRPC_error(ERR_DOES_NOT_EXIST, XMLRPC_prepare("file doesn't exists: ".$url));
                        return;
                        
                    }
                    
                    $file = getcwd()."/uploads/tf/".$entry['title'];
                    if( ! is_readable($file)){
                        XMLRPC_error(ERR_COULD_NOT_READ, XMLRPC_prepare("cannot read file: ".$url));
                        return;
                    }
                    $fh = @ fopen($file, 'r');
                    if(false == $fh){
                        XMLRPC_error(ERR_COULD_NOT_READ, XMLRPC_prepare("cannot read file: ".$url));
                        return;
                    
                    }
                    
                    $ret['content'] = base64_encode(fread($fh, filesize($file)));
                    
                    fclose($fh);
                    $ret['mime'] = $fileinfo['mime'];
                    $ret['size'] = $fileinfo['size'];
                    $ret['modified'] = $fileinfo['modified'];
                    
                    
                    //$mime = mime_content_type($file);
                    //$content = "muss noch gemacht werden";
              
                break;
              default:
                       XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("Filename is not valid: ".$url));
                        return;
              
              
              }
                
             //$ret['content'] = base64_encode($content);
                           
                  
              XMLRPC_response(XMLRPC_prepare($ret));
                
        
        
        }
         function put($url, $data){
            //echo "\n".__LINE__." content=".$data." , url = ".$url."\n";
            $ret = array();
            $pathinfo = $this->parse_path($url);
            //print_r($pathinfo);
            if(! $pathinfo){
                    //error_log(__LINE__." fehler : ".$url,0);
                    //print_r($pathinfo);
                    XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("\"".__FUNCTION__."\" cannot parse url:".$url));
                    return;
             }
             $entry = $pathinfo[count($pathinfo)-1];

             $content = base64_decode($data);
             //echo "content decodet = ".$content;              
              switch($entry['type']){
              case 'setup':
              case 'constants':
                $fields = array();
                if(0 == strcmp($entry['type'], 'constants')){
                    $fields['constants'] = $content;
                } else  if(0 == strcmp($entry['type'], 'setup')){
                    $fields['config'] = $content;
                }
                $fields['tstamp'] = time();
                $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('sys_template', 'uid='.intval($entry['templ_uid']) . $GLOBALS['TSFE']->sys_page->deleteClause('sys_template'), $fields);
                if ($GLOBALS['TYPO3_DB']->sql_affected_rows($res) == 0) {
                        // The template has since been deleted
                        XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("Specified template (".$entry['templ_uid'].") doesn't exist, was it deleted?"));
                        return;
                } 
                
                
                break;
                case 'res':
                    $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                            'constants, config, resources, tstamp', 'sys_template', 'uid='.intval($entry['templ_uid']) . $GLOBALS['TSFE']->sys_page->deleteClause('sys_template'), 'uid'
                    );
                    if(empty($templates)){
                                XMLRPC_error(ERR_COULD_NOT_WRITE, XMLRPC_prepare("Specified template ($templateuid) doesn't exist and cannot create a new one"));
                                return;
                    }
                    $templ = $templates[0];
                    $resources = t3lib_div::trimExplode(',', $templ['resources']);
                    //echo "title=".$entry['title'];
                    //print_r($resources);
                    foreach($resources as $filename){
                        //echo "filename=".$filename;
                        if(0 == strcmp($entry['title'] , $filename)){
                            
                            $fh = @ fopen(getcwd()."/uploads/tf/".$filename, 'w');
                            if(! $fh){
                                XMLRPC_error(ERR_COULD_NOT_WRITE, XMLRPC_prepare("Cannot open or create file for writing: $filename"));
                                return;
                            }
                            fwrite($fh, $content);
                            fclose($fh);
                             
                        }
                    }
                    
                    
                break;
              default:
                       XMLRPC_error(ERR_MALFORMED_URL, XMLRPC_prepare("Filename is not valid: ".$url));
                        return;
              
              
              }                  
             
             
             $this->clearCache();
             XMLRPC_response(XMLRPC_prepare(true));
         
         }
         
         
         
         function parse_path($path){
             $parts = explode("/", $path);
             //print_r($parts);
             $dir = array();
             foreach($parts as $part){
                if(strlen($part)){
                    $dir[] = $part;
                }
             }
             $pathinfo = array();
             foreach($dir as $d){
                $filename_parts = $this->parse_filename($d);
                if($filename_parts == false){
                    return false;
                }
                $pathinfo[] = $filename_parts;
             }
             return $pathinfo;

        
        
        }
        /**
        split the filename and extract the necessary informations like page uid, template uid, type and title
        */
        function parse_filename($filename) {
            $parts = explode(".", $filename);
            for($i=0;$i<4;$i++){
                if(strlen($parts[$i]) <=0){
                    return false;
                    
                }
            }
            $file['sorting'] = intval($parts[0]);
            $file['page_uid'] = intval($parts[1]);
            $file['templ_uid'] = intval($parts[2]);
            $file['type'] = $parts[3];
            $file['title'] = $parts[4];
            for($i=5;$i<count($parts);$i++){
                $file['title'] .= ".".$parts[$i];
            }
            
            
            
            
            return $file;
        }
         /**
         
         
         */
         function get_file_info($name){
            $fileinfo = array();
            $fileinfo['name'] = $name;
            
            $file = PATH_site."uploads/tf/".$name;
            //echo "file = ".$file."<br>\n";
            if( ! is_readable($file)){
                return false;
            }
            $fileinfo['size'] =  filesize($file);
            //echo "filesize=".filesize($file);
            $fh = fopen($file, 'rb');
            fclose($fh);
            $fileinfo['mime'] = $this->mimetype($file);
            $fileinfo['modified'] = filemtime($file);
            $fileinfo['owner'] = fileowner($file);
            $fileinfo['group'] = filegroup($file);
            $fileinfo['perms'] = fileperms($file);
           
            
            return $fileinfo;
    
         
         
         }


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_templates.php'])     {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_templates.php']);
}


?>