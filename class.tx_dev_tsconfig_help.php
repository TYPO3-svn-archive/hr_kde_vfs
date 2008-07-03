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

/** based on /typo3/wizard_tsconfig.php
*/

class tx_dev_tsconfig_help{


    function get_ts_config_objects($params){
         //echo "hallo ein Test";
         $objTree = $this->getObjTree();
         //print_r($objTree);
         if($objTree === false || count($objTree) <= 0){
            return false;
         }
         $mode="tsref";// "page", "tsref" or "beuser"
         $objTree = $this->removePointerObjects($objTree[$mode.'.']);
         
         return $objTree;
    }
    function get_ts_config_objects_properties($params){
        return $this->properties( $params[0]);
    }



        function properties($show)      {
                global $LANG;

                        // Get the entry data:
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_tsconfig_help', 'uid='.intval($show));
                $rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $table = unserialize($rec['appdata']);
                //print_r($table['rows'];
                //exit();
                return $table['rows'];
        }




	/**
	 * Create object tree from static_tsconfig_help table
	 *
	 * @return	array		Object tree.
	 * @access private
	 */
	function getObjTree()	{
		$objTree=array();
//echo "getObjTree";
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,obj_string,title', 'static_tsconfig_help', '');
	    if(false === $res){
            return false;	
        }   
        while($rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
            $rec['obj_string'] = $this->revertFromSpecialChars($rec['obj_string']);
 			$p = explode(';',$rec['obj_string']);
			while(list(,$v)=each($p))	{
				$p2 = t3lib_div::trimExplode(':',$v,1);
				$subp=t3lib_div::trimExplode('/',$p2[1],1);
				while(list(,$v2)=each($subp))	{
					$this->setObj($objTree,explode('.',$p2[0].'.'.$v2),array($rec,$v2));
				}
			}
		}
//t3lib_div::debug($objTree);		
        return $objTree;
	}

	/**
	 * Sets the information from a static_tsconfig_help record in the object array.
	 * Makes recursive calls.
	 *
	 * @param	array		Object tree array, passed by value!
	 * @param	array		Array of elements from object path (?)
	 * @param	array		Array with record and something else (?)
	 * @return	void
	 * @access private
	 * @see getObjTree()
	 */
	function setObj(&$objTree,$strArr,$params)	{
		$key = current($strArr);
		reset($strArr);
		if (count($strArr)>1)	{
			array_shift($strArr);
			if (!isset($objTree[$key.'.']))	$objTree[$key.'.']=array();
			$this->setObj($objTree[$key.'.'],$strArr,$params);
		} else {
			
                        //$objTree[$key]=$params;
			//$objTree[$key]['_LINK']=$this->doLink($params);
                        $objTree[$key]=intval($params[0]['uid']);
                        //$objTree[$key]['obj_string']=$params[1];
                        //$properties = $this->properties('tsref', $params[0]['uid']);
                        //$objTree[$key]['properties']=$properties['rows'];                    
		}
	}

    /**
     * Remove pointer strings from an array
     *
     * @param   array       Input array
     * @return  array       Modified input array
     * @access private
     */
    function removePointerObjects($objArray)    {
        reset($objArray);
        while(list($k)=each($objArray)) {
            if (substr(trim($k),0,2)=="->" && trim($k)!='->.')  {
                $objArray['->.'][substr(trim($k),2)]=$objArray[$k];
                unset($objArray[$k]);
            }
        }
        return $objArray;
    }

    function ext_getObjTree($arr, $depth_in, $depthData, $parentType='',$parentValue='', $alphaSort='0')    {
        $HTML='';
        $a=0;

        reset($arr);
        if($alphaSort == '1')   {
            ksort($arr);
        }
        $keyArr_num=array();
        $keyArr_alpha=array();
        while (list($key,)=each($arr))  {
            if (substr($key,-2)!='..')  {   // Don't do anything with comments / linenumber registrations...
                $key=ereg_replace('\.$','',$key);
                if (substr($key,-1)!='.')   {
                    if (t3lib_div::testInt($key))   {
                        $keyArr_num[$key]=$arr[$key];
                    } else {
                        $keyArr_alpha[$key]=$arr[$key];
                    }
                }
            }
        }
        ksort($keyArr_num);
        $keyArr=$keyArr_num+$keyArr_alpha;
        reset($keyArr);
        $c=count($keyArr);
        if ($depth_in)  {$depth_in = $depth_in.'.';}
        
        return $keyArr;
     }
       
       
       
       
       /**
         * Converts &gt; and &lt; to > and <
         *
         * @param       string          Input string
         * @return      string          Output string
         * @access private
         */
        function revertFromSpecialChars($str)   {
                $str = str_replace('&gt;','>',$str);
                $str = str_replace('&lt;','<',$str);
                return $str;
        }
}



?>