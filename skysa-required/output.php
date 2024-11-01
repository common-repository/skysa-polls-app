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

// Handle output of App code on all non-admin pages.

global $SkysaApps;
global $wpdb;
$table_name=$wpdb->prefix . "skysa_apps";
$applist = $SkysaApps->appList;
$buttonHTML = array();
$jsAll = array();
foreach (array_keys($SkysaApps->appList) as $appid) {
    $app = $applist[$appid];
    $options = $app['options'];
    $window = $app['window'];
    $fvars = $app['fvars'];
    $html = $app['html'] . '';
    $js = $app['js'] . '';
    $values = array();
    $label = '';
        
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
    $align = strtolower($curRec['align']);
    if($options){
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
            if($field == 'bar_label'){
                $label = $value.'';
            }
            else if($field == 'icon' && $value != ''){
                $value = '<span class="icon"><img src="'.$value.'" /></span>';
            }
            $values[$field] = $value;
            $html = str_replace('$app_'.$field,$value, $html);
            $js = str_replace('$app_'.$field,$value, $js);
        }
        unset($valuelist);
    }
    if($window){
        foreach($window as $field => $defaltvalue){
            if($curRec && !is_null($curRec[$field]) && $curRec[$field] !== 0 && $curRec[$field] != ''){
                $value = $curRec[$field];
            }
            else{
                $value = $defaltvalue;
            }
            $values[$field] = $value;
            $html = str_replace('$app_'.$field,$value, $html);
            $js = str_replace('$app_'.$field,$value, $js);
        }
        unset($value);
    }
    if($fvars){
        foreach($fvars as $var => $fun){
            try{
                $value = $fun($curRec);
            }catch(Exception $e){
                $value = '';
            }
            $html = str_replace('#fvar_'.$var,$value, $html);
            $js = str_replace('#fvar_'.$var,$value, $js);
        }
        unset($fun);
    }
    $html = str_replace('$app_id',$app_id, $html);
    $js = str_replace('$app_id',$app_id, $js);
    $html = str_replace('$button_id','SKYUI-Mod-lite-'.$app_id, $html);
    $js = str_replace('$button_id','SKYUI-Mod-lite-'.$app_id, $js);
    $html = str_replace(chr(13),'\\n', $html);
    $html = str_replace(chr(10),'\\n', $html);
    $html = str_replace("'","\\'", $html);
    $label = str_replace("'","\\'", $label);
    if(!$align || $align == '') $align = 'left';
    array_push($buttonHTML,"{label:'".$label."',align:'".$align."',id:'".$app_id."',html:'".$html."'}");
    $values['appnum'] = $appid;
    array_push($jsAll,'["'.$app_id.'",function(S){try{' . $js . '}catch(e){'.($app['debug'] ? 'if(window.console){console.log(e);}' : '').'}},'.json_encode($values).']');
}
unset($app);

echo '<script type="text/javascript">
        var _SKYAPPS = {h:['.implode(',',$buttonHTML).'],j:['.implode(',',$jsAll).'],a:"'.admin_url('admin-ajax.php') . '?action=skysa_appload"};
        </script>';
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active('skysa-official/skysa.php')){
    // Draw blank bar, since the Skysa App Bar is not installed.
    echo '<script type="text/javascript">var SKYUIStopPolling = true;</script>';
    echo '<a href="http://www.skysa.com" id="SKYSA-NoScript">Website Apps</a><script type="text/javascript" src="//static2.skysa.com?i=bar_lite" async="true"></script>';
}