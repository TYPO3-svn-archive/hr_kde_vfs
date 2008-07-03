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


//require_once(t3lib_extMgm::extPath("xmlrpc_lib").'/xmlrpc.php');

class tx_rootdir extends tx_fileadmin {
    
    /** virtual directory name, this can be any string,
    It will be prepend by a string
    */
    var $rootdir = "root";    
    /** Directory relative to the TYPO3 rootdirectory with a trailing slash 
    for example: "/", "fileamin/" */
    var $rel_real_rootfolder = "/";
    


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_rootdir.php'])     {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_kde_vfs/class.tx_rootdir.php']);
}


?>