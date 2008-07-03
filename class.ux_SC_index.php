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

require_once(t3lib_extMgm::extPath("xmlrpc_lib").'/xmlrpc.php');


class ux_SC_index extends SC_index
{
        function main() {
                if(0 == strlen($_GET['hr_kde_vfs'])){
                    return parent::main();
                }
                global $TBE_TEMPLATE, $TYPO3_CONF_VARS, $BE_USER;
                //header("Content-type:text/plain");
                
                if (!$BE_USER->user['uid'])     {
                    $challenge = md5(uniqid('').getmypid());
    
                    session_start();
                    $_SESSION['login_challenge'] = $challenge;
                    $xml_data = array('status' => 'not_logged_in', 
                                     'callenge' => $challenge, 
                                     'loginSecurityLevel' => $this->loginSecurityLevel, 
                                     'site_root_url' => t3lib_div::getIndpEnv('TYPO3_SITE_URL')
                                     
                                     );
                    
                    
                }else{
                    $xml_data = array('status' => 'logged_in', 
                                      'username' => $BE_USER->user['username'],
                                      'user_uid' =>  $BE_USER->user['uid'],
                                      'site_root_url' => t3lib_div::getIndpEnv('TYPO3_SITE_URL')
                                      );
                }
                XMLRPC_response(XMLRPC_prepare($xml_data, 'array'));
                
        }



}
 
?>