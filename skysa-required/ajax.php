<?php
/*
*************************************************************
*                Skysa App SDK version 2.0                  *
*            Download the latest version here:              *
*    http://wordpress.org/extend/plugins/skysa-app-sdk/     *
*************************************************************
* Direct modification of this file for a production plugin  *
* is not recommended, due to incompatibilites it could      *
* cause for other plugins using this SDK.                   *
* Instead it is remmended that you contact and submit your  *
* proposed changes to Skysa's staff at staff@skysa.com.     *
* Your proposed changes can then be reviewed for inclusion  *
* in the next version of the SDK, which will be made        *
* available to you and publically available for use in      *
* creation of new plugins and to update old ones.           *
*                                                           *
* When these core files are included with plugins made      *
* using this SDK, the loader file will choose the most      *
* recent version of the core files to use. So it is very    *
* important that any changes made are updated in a version  *
* change in the core SDK. This will ensure that your plugn, *
* as well as other plugins, will not be broken by the       *
* installation of any others on the same site.              *
*                                                           *
* Thank you for taking this into consideration, and feel    *
* free to contact staff@sksya.com with any questions. You   *
* may also contact Skysa here:                              *
* http://www.skysa.com/page/contact                         *
*                                                           *
*   Please include these comments with any redistribution.  *
*************************************************************

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, 
MA  02110-1301, USA.
*/

global $SkysaApps;
global $wpdb;
$table_name=$wpdb->prefix . "skysa_apps";
$applist = $SkysaApps->appList;
$fn = $_GET['fn'];
$view = $_GET['view'];
$app = $applist[$_GET['app']];
header('Content-type: text/javascript');
if($app && $app['views'] && $app['views'][$view]){
    $GLOBALS["SKTABLENAME"] = $table_name;
    $GLOBALS["SKAPPID"] = $app['id'];
    function SkysaApps_SaveContentItem($itemid,$field,$val){
        global $SkysaApps;
        global $wpdb;
        $table_name = $GLOBALS["SKTABLENAME"];
        $app_id = $GLOBALS["SKAPPID"];
        $curRec = $wpdb->get_row(
            "
            SELECT * FROM $table_name 
            WHERE app_id = '$app_id'
            ",
            ARRAY_A
        );
        if($curRec && $curRec['content']){
            $contentItems = json_decode($curRec['content']);
            if($contentItems -> $itemid && array_key_exists($field,$contentItems -> $itemid)){
                $item = $contentItems -> $itemid;
                $item -> $field = $val;
                $contentItems -> $itemid = $item;
                $contentItems = json_encode($contentItems);
                                    
                $wpdb->update( 
	                $table_name, 
                    array('content' => $contentItems),
	                array('app_id' => $app_id)
                );    
            }
        }
    }

    function SkysaApps_HandleAjaxErr($errno, $errstr, $errfile, $errline){
        $GLOBALS["SKERRSTRING"] = '<h3>Server Error</h3> Number: ' . $errno .'<br />String: '.$errstr.'<br />File: '.$errfile.'<br />Line Number: '.$errline;
    };
    set_error_handler('SkysaApps_HandleAjaxErr');
    $options = isset($app['options']) ? $app['options'] : array();
    $app_id = $app['id'];
	// build apps array, get default values, lookup from database.
    $curRec = $wpdb->get_row(
        "
        SELECT * FROM $table_name 
        WHERE app_id = '$app_id'
        ",
        ARRAY_A
    );
    if($curRec['content'] && $curRec['content'] != ''){
        $curContent = json_decode($curRec['content']);
        $contentArray = array();
        foreach($curContent as $id => $valuelist){
            $contentArray[$valuelist ->sk_created] = $valuelist;
        }
        unset($id);
        krsort($contentArray);
        $curRec['content'] = $contentArray;

        //$curRec['content'] = json_decode($curRec['content']);
    }
    if(is_string($app['views'][$view]) && !function_exists($app['views'][$view])){
        $html = $app['views'][$view];
    }
    else{
        try{
            $html = $app['views'][$view]($curRec,'SkysaApps_SaveContentItem');
        }catch(Exception $e){
            if($app['debug']){
                $html = 'Caught exception: ' .  $e->getMessage();
            }
            else{
                $html = 'There was an error retreiving this content.';
            }
        }
    }
    unset($valuelist);
        
    foreach($options as $field => $valuelist){
        if($curRec && !is_null($curRec[$field])){
            $value = $curRec[$field];
        }
        else{
            if($valuelist['type'] == 'selectbox'){
                $optionvalues = explode('|',$valuelist['value']);
                $value = $optionvalues[0];
            }
            else if($valuelist['type'] == 'selectbox'){
                $value = '';
            }
            else{
                $value = $valuelist['value'];
            }
        }
        if($field == 'icon' && $value != ''){
            $value = '<span class="icon"><img src="'.$value.'" /></span>';
        }
        $values[$field] = $value;
        $html = str_replace('$app_'.$field,$value, $html);
    }
    unset($valuelist);
    if(isset($app['fvars'])){
        foreach($app['fvars'] as $var => $fun){
            try{
                $value = $fun($curRec);
            }catch(Exception $e){
                if($app['debug']){
                    $html = 'Caught exception: ' .  $e->getMessage();
                    break;
                }
                else{
                    $value = '';
                }
            }
            $html = str_replace('#fvar_'.$var,$value, $html);
        }
    }

    $html = str_replace(chr(13),'\\n', $html);
    $html = str_replace(chr(10),'\\n', $html);
    //$html = str_replace("'","\\'", $html);
    if($app['debug'] && $GLOBALS["SKERRSTRING"]){
        $html = $GLOBALS["SKERRSTRING"] .'<br />'.$html;
    }
    echo $fn . "('" . $html . "','".$fn."');";
}
die();