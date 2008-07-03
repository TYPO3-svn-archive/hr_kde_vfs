<?php
//$TYPO3_CONF_VARS['FE']['eID_include']['hr_kde_vfs'] = 'EXT:hr_kde_vfs/class.tx_server.php'; 


$ext_path = t3lib_extMgm::extPath($_EXTKEY);
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/index.php'] = $ext_path.'class.ux_SC_index.php';
 

// Here you can add own classes.
// It is necessary to set a key and not empty braces. In this case the key is "vfs".
// This is the classname of a call: vfs.put or vfs.get.
// I recomment to set the classname similar like this key: tx_vfs
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['response_class']['vfs'] = 'EXT:hr_kde_vfs/class.tx_vfs.php:tx_vfs';

$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['response_class']['dev'] = 'EXT:hr_kde_vfs/class.tx_dev.php:tx_dev';
 
 

 
////////////////////////////////////////////////////////////////////////////////////////////////////////
// Hooks für das vfs Dateisystem: 
$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['process_root_folder'][] = 'EXT:hr_kde_vfs/class.tx_templates.php:tx_templates';


$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['process_root_folder'][] = 'EXT:hr_kde_vfs/class.tx_fileadmin.php:tx_fileadmin';


$GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['hr_kde_vfs']['process_root_folder'][] = 'EXT:hr_kde_vfs/class.tx_rootdir.php:tx_rootdir';

 
 
 
 
?>