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



/**
This are the same errorcodes as the KIO::error from the kde-libraries

*/

 define('ERR_CANNOT_OPEN_FOR_READING', 1);
 define('ERR_CANNOT_OPEN_FOR_WRITING', 2);
 define('ERR_CANNOT_LAUNCH_PROCESS', 3);
 define('ERR_INTERNAL', 4);
 define('ERR_MALFORMED_URL', 5);
 define('ERR_UNSUPPORTED_PROTOCOL', 6);
 define('ERR_NO_SOURCE_PROTOCOL', 7);
 define('ERR_UNSUPPORTED_ACTION', 8);
 define('ERR_IS_DIRECTORY', 9); // ... where a file was expected
 define('ERR_IS_FILE', 10); // ... where a directory was expected (e.g. listing)
 define('ERR_DOES_NOT_EXIST', 11);
 define('ERR_FILE_ALREADY_EXIST', 12);
 define('ERR_DIR_ALREADY_EXIST', 13);
 define('ERR_UNKNOWN_HOST', 14);
 define('ERR_ACCESS_DENIED', 15);
 define('ERR_WRITE_ACCESS_DENIED', 16);
 define('ERR_CANNOT_ENTER_DIRECTORY', 17);
 define('ERR_PROTOCOL_IS_NOT_A_FILESYSTEM', 18);
 define('ERR_CYCLIC_LINK', 19);
 define('ERR_USER_CANCELED', 20);
 define('ERR_CYCLIC_COPY', 21);
 define('ERR_COULD_NOT_CREATE_SOCKET', 22); // KDE4: s/COULD_NOT/CANNOT/ or the other way round
 define('ERR_COULD_NOT_CONNECT', 23);
 define('ERR_CONNECTION_BROKEN', 24);
 define('ERR_NOT_FILTER_PROTOCOL', 25);
 define('ERR_COULD_NOT_MOUNT', 26);
 define('ERR_COULD_NOT_UNMOUNT', 27);
 define('ERR_COULD_NOT_READ', 28);
 define('ERR_COULD_NOT_WRITE', 29);
 define('ERR_COULD_NOT_BIND', 30);
 define('ERR_COULD_NOT_LISTEN', 31);
 define('ERR_COULD_NOT_ACCEPT', 32);
 define('ERR_COULD_NOT_LOGIN', 33);
 define('ERR_COULD_NOT_STAT', 34);
 define('ERR_COULD_NOT_CLOSEDIR', 35);
 define('ERR_COULD_NOT_MKDIR', 37);
 define('ERR_COULD_NOT_RMDIR', 38);
 define('ERR_CANNOT_RESUME', 39);
 define('ERR_CANNOT_RENAME', 40);
 define('ERR_CANNOT_CHMOD', 41);
 define('ERR_CANNOT_DELETE', 42);
 // The text argument is the protocol that the dead slave supported.
 // This means for example: file, ftp, http, ...
 define('ERR_SLAVE_DIED', 43);
 define('ERR_OUT_OF_MEMORY', 44);
 define('ERR_UNKNOWN_PROXY_HOST', 45);
 define('ERR_COULD_NOT_AUTHENTICATE', 46);
 define('ERR_ABORTED', 47); // Action got aborted from application side
 define('ERR_INTERNAL_SERVER', 48);
 define('ERR_SERVER_TIMEOUT', 49);
 define('ERR_SERVICE_NOT_AVAILABLE', 50);
 define('ERR_UNKNOWN', 51);
 // (was a warning) define('ERR_CHECKSUM_MISMATCH', 52);
 define('ERR_UNKNOWN_INTERRUPT', 53);
 define('ERR_CANNOT_DELETE_ORIGINAL', 54);
 define('ERR_CANNOT_DELETE_PARTIAL', 55);
 define('ERR_CANNOT_RENAME_ORIGINAL', 56);
 define('ERR_CANNOT_RENAME_PARTIAL', 57);
 define('ERR_NEED_PASSWD', 58);
 define('ERR_CANNOT_SYMLINK', 59);
 define('ERR_NO_CONTENT', 60); // Action succeeded but no content will follow.
 define('ERR_DISK_FULL', 61);
 define('ERR_IDENTICAL_FILES', 62); // src==dest when moving/copying
 define('ERR_SLAVE_DEFINED', 63); // for slave specified errors that can be
                         // rich text.  Email links will be handled
                         // by the standard email app and all hrefs
                         // will be handled by the standard browser.
                         // <a href="exec:/khelpcenter ?" will be
                         // forked.
 define('ERR_UPGRADE_REQUIRED', 64); // A transport upgrade is required to access this
                            // object.  For instance, TLS is demanded by
                            // the server in order to continue.
 define('ERR_POST_DENIED', 65);    // Issued when trying to POST data to a certain Ports
                            // see job.cpp

#require_once(t3lib_extMgm::extPath("xmlrpc_lib").'/xmlrpc.php');

// This is only to create the output !!! Parsing the xml fails because of changes
// to make it compatible to php5
require_once(t3lib_extMgm::extPath("hr_kde_vfs").'/xmlrpc_5.php');



require_once (PATH_t3lib.'class.t3lib_tcemain.php');
#define('XMLRPC_DEBUG',1);


class tx_server_base {
    
    function request($methodName, $params){
        XMLRPC_error(ERR_SERVICE_NOT_AVAILABLE, XMLRPC_prepare("Method Name: ".$methodName));
    }    

    


    /**
        * Clears all the cache
        */
    function clearCache() {
            $tce = t3lib_div::makeInstance("t3lib_TCEmain");
            $tce->stripslashes_values=0;
            $tce->start(Array(),Array());
            $tce->clear_cacheCmd("all");// Datenbankfelder
            $tce->clear_cacheCmd("temp_CACHED");// cache files in typo3conf
    }        

    function error($errorcode, $errorstring){
            XMLRPC_error($errorcode, XMLRPC_prepare($errorstring));

    }


}




?>