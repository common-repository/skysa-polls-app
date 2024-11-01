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

function SkysaApps_Admin_BoxDisplay($boxtype,$tfield,$defaultvalue,$sizev,$selectvalues){
    $size = explode('|',$sizev);
    $bwidth = $size[0];
    $bheight = $size[1];
    $boxdisplay = '';
    if($boxtype == 'text'){
        $boxdisplay .= '<input type="text" name="'.$tfield.'" id="field-'.$tfield.'" size="'.$bwidth.'" maxlength="'.($bwidth*3).'" value="'.$defaultvalue.'" />';
    }
    else if($boxtype == 'date'){
        $boxdisplay .= '<input type="text" name="'.$tfield.'" id="field-'.$tfield.'" size="'.$bwidth.'" maxlength="'.($bwidth*3).'" value="'.$defaultvalue.'" class="show-datepick" />';
    }
    else if($boxtype == 'image'){ 
        $boxdisplay .= '<input type="text" name="'.$tfield.'" id="field-'.$tfield.'" size="'.$bwidth.'" maxlength="'.($bwidth*3).'" value="'.$defaultvalue.'" class="upload-url" /> <a id="button-'.$tfield.'" href="#" class="upload_image_button">Upload/Insert <img src="images/media-button.png?ver=20111005" /></a>';
    }
    else if($boxtype == 'hidden'){
        $boxdisplay .= '<input type="hidden" name="'.$tfield.'" value="'.$defaultvalue.'" />';
    }
    else if($boxtype == 'textarea'){
        $boxdisplay .= '<textarea name="'.$tfield.'" id="field-'.$tfield.'" rows="'.$bheight.'" cols="'.$bwidth.'">'.$defaultvalue.'</textarea>';
    }
    else if($boxtype == 'editor'){
        if(function_exists(wp_editor)){ // If using the editor, revert html entitiy correction, so it does not interfere with the editor's own filter.
            $defaultvalue = str_replace('&#34;','"', $defaultvalue);
            $defaultvalue = str_replace('&lt;/textarea&gt;','</textarea>', $defaultvalue);
            $boxdisplay .= wp_editor( $defaultvalue, 'field-'.$tfield, $settings = array( 'textarea_name' => $tfield, 'textarea_rows' => $bheight ) );
        }
        else{
            $boxdisplay .= '<textarea name="'.$tfield.'" id="field-'.$tfield.'" rows="'.$bheight.'" cols="'.$bwidth.'">'.$defaultvalue.'</textarea>';

        }
    }
    else if($boxtype == 'selectbox'){
        $boxdisplay = $boxdisplay . '<select size="'.$bheight.'" name="'.$tfield.'" id="field-'.$tfield.'" style="width: '.($bwidth*0.535).'em; height: 1.6em;">'; // Match other field dementions as close as possible.
        $mydefaultvalues = explode('|',$selectvalues);
        foreach($mydefaultvalues as $value){
            if(is_numeric($defaultvalue)){
                $defaultvalue = round($defaultvalue);
                $value1 = round($value);
            }
            else{
                $value1 = $value;
            }
            if($value == $defaultvalue){
                $selected = ' selected="selected"';
            }
            else{
                $selected = '';
            }
            $boxdisplay .= '<option'.$selected.'>'.$value1.'</option>';
        }
        unset($value);
        $boxdisplay .= '</select>';
    }
    if($boxdisplay != ''){
        echo $boxdisplay;
    }
}



function SkysaApps_Admin_CreateDBTable($table_name,$app_id,$options,$window){
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $charset_collate = '';
    if ( !empty($wpdb->charset) )
		$chcharset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
    $sql = array();
    $sql[] = "CREATE TABLE $table_name (
	    id int NOT NULL AUTO_INCREMENT,
	    app_id varchar(150) NOT NULL DEFAULT '',
	    title varchar(255) NOT NULL DEFAULT '',
        bar_label varchar(255) NOT NULL DEFAULT '',
        option1 varchar(255) NOT NULL DEFAULT '',
        option2 varchar(255) NOT NULL DEFAULT '',
        option3 varchar(255) NOT NULL DEFAULT '',
        option4 varchar(8126) NOT NULL DEFAULT '',
        data longtext NOT NULL DEFAULT '',
        content longtext NOT NULL DEFAULT '',
        align varchar(50) NOT NULL DEFAULT '',
        icon varchar(8126) NOT NULL DEFAULT '',
        order_by smallint NOT NULL DEFAULT 0,
        width smallint NOT NULL DEFAULT 0,
        height smallint NOT NULL DEFAULT 0,
        position varchar(20) NOT NULL DEFAULT '',
        PRIMARY KEY  id(id)
	) $charset_collate;";

    dbDelta($sql);

    $updateArray = array('app_id' => $app_id);
    if($options){
        foreach($options as $field => $valuelist){
            if($valuelist['type'] == 'selectbox'){
                $optionvalues = explode('|',$valuelist['value']);
                $updateArray[$field] = $optionvalues[0];
            }
            else if($valuelist['type'] == 'checkbox'){
                $updateArray[$field] = '';
            }
            else{
                $updateArray[$field] = $valuelist['value'];
            }
        }
        unset($valuelist);
    }
    if($window){
        foreach($window as $field => $value){                              
            $updateArray[$field] = $value;
        }
        unset($value);
    }
    $wpdb->insert( 
	    $table_name, 
	    $updateArray
    );
    unset($updateArray);
}

function SkysaApps_Admin_DrawTabs($tabs,$current){
    $curset = $current || false;
    $activet = null;
    switch($current){
        case 'manage':
            $classN = 'icon-page';
            break;
        case 'window':
            $classN = 'icon-appearance';
            break;
        default:
            $classN = 'icon-settings';
    }
    echo '<h2 class="nav-tab-wrapper"><div class="'.$classN.' icon32" style="margin: 0 10px 0 0;"></div>';
    foreach( $tabs as $tab => $name ){
        if($name){
                    
            $class = ( ($current && $tab == $current) || !$curset ) ? ' nav-tab-active' : '';
            if($class != ''){
                $curset = true;
                $activet = $tab;
            }
            echo '<a class="nav-tab'.$class.'" href="admin.php?page='.$_GET['page'].'&tab='.$tab.'">'.$name.'</a>';
        }
    }
    echo '</h2>';
    return $activet;
}

function SkysaApps_Admin_AppPage(){
	global $SkysaApps; 
	global $wpdb;
	$page_id = explode('skysa_app_',$_GET['page']);
	$app = $SkysaApps->appList[$page_id[1]];
    $app_id = $app['id'];
    $options = $app['options'];
    $window = $app['window'];
    $manage = $app['manage'];
    $table_name=$wpdb->prefix . "skysa_apps";
    $updateArray = array();
    $curRec = $wpdb->get_row(
        "
        SELECT * FROM $table_name 
        WHERE app_id = '$app_id'
        ",
        ARRAY_A
    );
    if(isset($_POST['submit'])) {
        echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Saved!</p></div>\n";
		echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 1500);</script>";	
    }
	?>
    
    <style type="text/css">
        .datepick{background-color:#fff;color:#222;border:1px solid #aaa;border-radius:.25em;-moz-border-radius:.25em;-webkit-border-radius:.25em;font-family:Arial,Helvetica,Sans-serif;font-size:90%}.datepick-rtl{direction:rtl}.datepick-popup{z-index:1000}.datepick-disable{position:absolute;z-index:100;background-color:white;opacity:.5;filter:alpha(opacity=50)}.datepick a{color:#222;text-decoration:none}.datepick a.datepick-disabled{color:#888;cursor:auto}.datepick button{margin:.25em;padding:.125em 0;background-color:#fcc;border:0;border-radius:.25em;-moz-border-radius:.25em;-webkit-border-radius:.25em;font-weight:bold}.datepick-nav,.datepick-ctrl{float:left;width:100%;background-color:#fff;font-size:90%;font-weight:bold}.datepick-ctrl{background-color:#fee6e3}.datepick-cmd{width:30%}.datepick-cmd:hover{background-color:#e0e0e0}.datepick-ctrl .datepick-cmd:hover{background-color:#f08080}.datepick-cmd-prevJump,.datepick-cmd-nextJump{width:8%}a.datepick-cmd{height:1.5em}button.datepick-cmd{text-align:center}.datepick-cmd-prev,.datepick-cmd-prevJump,.datepick-cmd-clear{float:left;padding-left:2%}.datepick-cmd-current,.datepick-cmd-today{float:left;width:35%;text-align:center}.datepick-cmd-next,.datepick-cmd-nextJump,.datepick-cmd-close{float:right;padding-right:2%;text-align:right}.datepick-rtl .datepick-cmd-prev,.datepick-rtl .datepick-cmd-prevJump,.datepick-rtl .datepick-cmd-clear{float:right;padding-left:0;padding-right:2%;text-align:right}.datepick-rtl .datepick-cmd-current,.datepick-rtl .datepick-cmd-today{float:right}.datepick-rtl .datepick-cmd-next,.datepick-rtl .datepick-cmd-nextJump,.datepick-rtl .datepick-cmd-close{float:left;padding-left:2%;padding-right:0;text-align:left}.datepick-month-nav{float:left;text-align:center}.datepick-month-nav div{float:left;width:12.5%;margin:1%;padding:1%}.datepick-month-nav span{color:#888}.datepick-month-row{clear:left}.datepick-month{float:left;width:15em;border:1px solid #aaa;text-align:center}.datepick-month-header,.datepick-month-header select,.datepick-month-header input{height:1.5em;background-color:#e0e0e0;color:#222;font-weight:bold}.datepick-month-header select,.datepick-month-header input{height:1.4em;border:0}.datepick-month-header input{position:absolute;display:none}.datepick-month table{width:100%;border-collapse:collapse}.datepick-month thead{border-bottom:1px solid #aaa}.datepick-month th,.datepick-month td{margin:0;padding:0;font-weight:normal;text-align:center}.datepick-month thead tr{border:1px solid #aaa}.datepick-month td{background-color:#eee;border:1px solid #aaa}.datepick-month td.datepick-week *{background-color:#e0e0e0;color:#222;border:0}.datepick-month a{display:block;width:100%;padding:.125em 0;background-color:#eee;color:#000;text-decoration:none}.datepick-month span{display:block;width:100%;padding:.125em 0}.datepick-month td span{color:#888}.datepick-month td .datepick-other-month{background-color:#fff}.datepick-month td .datepick-weekend{background-color:#ddd}.datepick-month td .datepick-today{background-color:#fbf9ee}.datepick-month td .datepick-highlight{background-color:#dadada}.datepick-month td .datepick-selected{background-color:#fcc}.datepick-status{clear:both;text-align:center}.datepick-clear-fix{clear:both}.datepick-cover{display:none;display:block;position:absolute;z-index:-1;filter:mask();top:-1px;left:-1px;width:100px;height:100px}
        .datepick-ctrl {display: none;}
        .datepick-month-header {height: 22px;}
        .datepick-month-header select {height: 20px;}
        .wrap input, .wrap select, .wrap label, .wrap option {font-size: 16px !important;}
        .wrap .form-table tr > * {border-bottom: 1px solid #f5f5f5;}
        .wrap .wp-list-table .button-secondary { display: block; text-align: center; margin-bottom: 2px;}
        .wrap td, .wrap textarea, .wrap input, .wrap select { font-family: sans-serif; }
        .wrap .upload_image_button { display: inline-block; vertical-align: top;}
        .wrap .upload_image_button img {vertical-align: middle;}
        .nav-tab-wrapper {padding-bottom: 0; border-bottom: 1px solid #CCC;}
        .nav-tab {display: inline-block; padding: 4px 10px 6px; font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif; font-weight: 200; border-style: solid;border-color: #DFDFDF #DFDFDF white;border-width: 1px 1px 0;color: #AAA;text-shadow: white 0 1px 0;font-size: 20px;line-height: 24px;display: inline-block;padding: 4px 14px 6px;text-decoration: none;margin: 0 6px -1px 0;-webkit-border-top-left-radius: 3px;-webkit-border-top-right-radius: 3px;border-top-left-radius: 3px;border-top-right-radius: 3px;}
        .nav-tab:hover, .nav-tab-active {#CCC #CCC white;}
        .nav-tab-active {border-width: 1px;color: #464646;}
    </style>
	<div class="wrap">
	    
	<h2><?php echo $app['label']; ?> App</h2>

    <?php
        
    $tabs = array(
        'manage' => $manage ? $manage['label'] : null,
        'settings' => $options || $app['html'] ? 'App Settings' : null,
        'window' => $window ? 'Window Options' : null
    );


    if ( isset ( $_GET['tab'] ) ){
        $activeTab = SkysaApps_Admin_DrawTabs($tabs,$_GET['tab']); 
    }
    else if(!$curRec && $options){
        $activeTab = SkysaApps_Admin_DrawTabs($tabs,'settings');
    }
    else{
        $activeTab = SkysaApps_Admin_DrawTabs($tabs,null);
    }
                                     
    if($activeTab == 'settings'){
        ?>
        <form action="" method="post" id="skysa-conf">
			<div id="bar_settings">
				<div class="inside">
					<table class="form-table">
                        <?php
                        if(is_null($options)) $options = array();
                        if ( isset($_POST['submit'])) {
                            if(!$curRec){
                                SkysaApps_Admin_CreateDBTable($table_name,$app_id,$options,$window);
                            }
                            foreach($options as $field => $valuelist){
                                    if(isset($_POST[$field])){
                                        $updateArray[$field] = stripcslashes($_POST[$field]);
                                    }
                            }
                            unset($valuelist);
                            if(isset($_POST['align'])){
                                $updateArray['align'] = $_POST['align'];
                            }
                            $wpdb->update( 
	                            $table_name, 
                                $updateArray,
	                            array('app_id' => $app_id)
                            );           
                        }
                                                
                        foreach($options as $field => $valuelist){
                            if(isset($_POST[$field])){
                                $defaultvalue = $_POST[$field];
								$defaultvalue = stripcslashes($defaultvalue);
                            }
                            else if($curRec && !is_null($curRec[$field])){
                                $defaultvalue = $curRec[$field];
                            }
                            else{
                                $defaultvalue = $valuelist['value'];
                            }
                            if(is_null($defaultvalue)) $defaultvalue = '';
                            $defaultvalue = str_replace('&&!',';', $defaultvalue);
                            $defaultvalue = str_replace('\"','&#34;', $defaultvalue);
                            $defaultvalue = str_replace('"','&#34;', $defaultvalue);
                            $defaultvalue = str_replace('</textarea>','&lt;/textarea&gt;', $defaultvalue);
                            ?>
                            <tr>
								<th valign="top" scrope="row">
									<label for="field-<?php echo $field; ?>"><?php echo $valuelist['label']; ?>:</label>
								</th>
								<td valign="top">
									<?php
                                    SkysaApps_Admin_BoxDisplay($valuelist['type'],$field,$defaultvalue,$valuelist['size'],$valuelist['value']);
                                    if($valuelist['info']){
                                        echo '<div class="info-text">'.$valuelist['info'].'</div>';
                                    }
                                    ?>
								</td>
                            </tr>
                            <?php
                        }
                        unset($valuelist);           
                        if($app['html']){
                                    
                            ?>
                            <tr>
								<th valign="top" scrope="row">
									<label for="field-align">App Alignment:</label>
								</th>
								<td valign="top">
									<?php
                                    $field = 'align';
                                    if(isset($_POST[$field])){
                                        $defaultvalue = $_POST[$field];
                                    }
                                    else if($curRec && !is_null($curRec[$field])){
                                        $defaultvalue = $curRec[$field];
                                    }
                                    else{
                                        $defaultvalue = 'Left';
                                    }
                                    SkysaApps_Admin_BoxDisplay('selectbox',$field,$defaultvalue,'10|1','Left|Right');
                                    unset($defaultvalue);
                                    unset($field);
                                    ?>
                                    <div class="info-text">How would you like this app aligned in your site bar?</div>
								</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
				</div>
			</div>
			<div class="submit">
				<input type="submit" class="button-primary" name="submit" value="Save Settings" />
			</div>
		</form>
        <?php
    }
    else if($activeTab == 'manage'){
        ?>
        <form action="admin.php?page=<?php echo $_GET['page'].'&tab='.$activet; ?>" method="post" id="skysa-conf">
			<div id="bar_settings">
				<div class="inside">
					<table class="form-table">
                        <?php
                        if(is_array($manage)){
                            if($curRec && $curRec['content']){
                                $contentItems = json_decode($curRec['content']);
                            }
                            else{
                                $contentItems = new stdClass;
                            }
                            if ( isset($_POST['submit'])) {
                                if(!$curRec){
                                    SkysaApps_Admin_CreateDBTable($table_name,$app_id,$options,$window);
                                }
                                foreach($manage['records'] as $field => $valuelist){
                                        if(isset($_POST[$field])){
                                            $updateArray[$field] = stripcslashes($_POST[$field]);
                                        }
                                }
                                unset($valuelist);
                                unset($field);
                                if(isset($_POST['sk_recid'])){
                                    $updateArray['sk_recid'] = $_POST['sk_recid'];
                                }
                                if(isset($_POST['sk_created'])){
                                    $updateArray['sk_created'] = $_POST['sk_created'];
                                }

                                if($updateArray['sk_recid']){
                                        
                                    // update current values
                                    foreach($manage['records'] as $field => $valuelist){
                                            if($valuelist['type'] == 'hidden'){
                                                if($contentItems -> $updateArray['sk_recid'] && $contentItems -> $updateArray['sk_recid'][$field]){ // if the hidden field is already set in the database copy over the contents
                                                    $updateArray[$field] = $contentItems -> $updateArray['sk_recid'][$field];
                                                }
                                                else{ // if not set yet, set to default value
                                                    $updateArray[$field] = $valuelist['value'];
                                                }
                                            }
                                    }
                                    unset($valuelist);
                                    unset($field);

                                    $contentItems -> $updateArray['sk_recid'] = $updateArray;
                                    
                                    // JSON encode/decode is needed instead of a single inline encode so that the new values are correctly written to the object for later use on this page.
                                    
                                    $contentItems = json_encode($contentItems);
                                    
                                    $wpdb->update( 
	                                    $table_name, 
                                        array('content' => $contentItems),
	                                    array('app_id' => $app_id)
                                    );     
                                    

                                    $contentItems = json_decode($contentItems);
                                }      
                            }
                            else if(isset ( $_GET['delete'] ) && $contentItems && $contentItems -> $_GET['delete']){
                                unset($contentItems -> $_GET['delete']);
                                $wpdb->update( 
	                                $table_name, 
                                    array('content' => json_encode($contentItems)),
	                                array('app_id' => $app_id)
                                );  
                            }
                            if(isset ( $_GET['edit'] ) && $contentItems && $contentItems -> $_GET['edit']){
                                $curContentItem = $contentItems -> $_GET['edit'];
                            }
                                
                            foreach($manage['records'] as $field => $valuelist){ 
                                if($valuelist['type'] && $valuelist['type'] != 'hidden'){
                                if($savefailed && isset($_POST[$field])){
                                    $defaultvalue = $_POST[$field];
									$defaultvalue = stripcslashes($defaultvalue);
                                }
                                else if($curContentItem && !is_null($curContentItem -> $field)){
                                    $defaultvalue = $curContentItem -> $field;
                                }
                                else{
                                    $defaultvalue = $valuelist['value'];
                                }
                                if(is_null($defaultvalue)) $defaultvalue = '';
                                $defaultvalue = str_replace('&&!',';', $defaultvalue);
                                $defaultvalue = str_replace('\"','&#34;', $defaultvalue);
                                $defaultvalue = str_replace('"','&#34;', $defaultvalue);
                                $defaultvalue = str_replace('</textarea>','&lt;/textarea&gt;', $defaultvalue);
                                ?>
                                <tr>
									<th valign="top" scrope="row">
										<label for="field-<?php echo $field; ?>"><?php echo $valuelist['label']; ?>:</label>
									</th>
									<td valign="top">
										<?php
                                            
                                        SkysaApps_Admin_BoxDisplay($valuelist['type'],$field,$defaultvalue,$valuelist['size'] ? $valuelist['size'] : null,$valuelist['value'] ? $valuelist['value'] : null);
                                        if($valuelist['info']){
                                            echo '<div class="info-text">'.$valuelist['info'].'</div>';
                                        }
                                        ?>
									</td>
                                </tr>
                                <?php
                                }
                                unset($defaultvalue);
                            }
                            unset($valuelist);
                            unset($field);
                            // if edit pull in id, timestamp from record. else create new values.
                            SkysaApps_Admin_BoxDisplay('hidden','sk_recid',$curContentItem && $curContentItem ->sk_recid ? $curContentItem ->sk_recid : uniqid(),null,null);
                            SkysaApps_Admin_BoxDisplay('hidden','sk_created',$curContentItem && $curContentItem ->sk_created ? $curContentItem ->sk_created : time(),null,null);
                                
                                
                        }
                        ?>
                    </table>
				</div>
			</div>
			<div class="submit">
				<input type="submit" class="button-primary" name="submit" value="<?php echo $manage['add_label'] ?  $curContentItem && $curContentItem ->sk_recid ? 'Save Changes' : $manage['add_label'] : 'Add'; ?>" />
                <?php
                    if($curContentItem && $curContentItem->sk_recid){
                    echo ' <a href="admin.php?page='.$_GET['page'].'&tab='.$activet.'" class="button-secondary">Cancel Edit</a>';
                    }  
                ?>
			</div>
		</form>
        <?php
            
        unset($idstr);                 
        unset($crstr);
        if($contentItems && count(get_object_vars($contentItems)) > 0){
            $toprow = array();
            $rows = array();
            $tablestr = '<tr>';
            foreach($manage['records'] as $field => $valuelist){
                    if($valuelist['type'] != 'hidden'){
                        $toprow[$field] = $valuelist['label'];
                        $tablestr = $tablestr . '<th>'. $valuelist['label'] . '</th>';
                    }
            }
            unset($field);
            unset($valuelist);
            $tablestr = $tablestr . '<th>Created</th>'.(!$manage['dis_edit']?'<th style="width: 75px;"> </th>' : '').'<th style="width: 75px;"> </th><tr>';
            foreach($contentItems as $id => $valuelist){
                $rowstr = '<tr class="alternate">';
                foreach($toprow as $key => $value){
                    if(!$manage['records'][$key]['output']){
                        $vstr = strip_tags($valuelist -> $key);
                        $rowstr .= '<td>'.(strlen($vstr) < 50 ? $vstr : substr($vstr, 0, strrpos(substr($vstr, 0, 50), ' ')) . '...').'</td>';
                        unset($vstr);
                    }
                    else{
                        try{
                            $rowstr .= '<td>'.$manage['records'][$key]['output']($valuelist).'</td>';
                        }catch(Exception $e){
                            $vstr = strip_tags($valuelist -> $key);
                            $rowstr .= '<td>'.(strlen($vstr) < 50 ? $vstr : substr($vstr, 0, strrpos(substr($vstr, 0, 50), ' ')) . '...').'</td>';
                            unset($vstr);
                        }
                    }
                }
                $rowstr = $rowstr . '<td>'.date("m/d/Y g:i A",intval($valuelist ->sk_created) + (get_option( 'gmt_offset' )*3600)).'</td>';
                if(!$manage['dis_edit']){
                    $rowstr = $rowstr . '<td><a class="button-secondary" href="admin.php?page='.$_GET['page'].'&tab='.$activet.'&edit='.$valuelist ->sk_recid.'">Edit</a></td>';
                }
                $rowstr = $rowstr . '<td><a class="button-secondary" href="admin.php?page='.$_GET['page'].'&tab='.$activet.'&delete='.$valuelist ->sk_recid.'" onclick="return confirm(\'Are you sure you want to delete this announcement?\');return false">Delete</a></td>';
                $rowstr = $rowstr . '</tr>';
                //$tablestr = $tablestr . $rowstr;
                $rows[$valuelist ->sk_created] = $rowstr . '';
                unset($value);
                unset($key);
                unset($rowstr);
            }
            unset($valuelist);
            unset($id);
            unset($toprow);
            echo '<h3>Current '.$manage['label'].'</h3><table class="wp-list-table widefat fixed">';
            echo $tablestr;
            krsort($rows);
            echo implode($rows);
            echo '</table>';
            unset($tablestr);
            unset($rows);
        }
        else{
            echo 'No '.$manage['label'].' Added Yet';
        }
        //echo var_dump($contentItems);
        unset($curContentItem);
    }
    else if($activeTab == 'window'){
        ?>
        <form action="" method="post" id="skysa-conf">
			<div id="bar_settings">
				<div class="inside">
					<table class="form-table">
                        <?php
                        if(is_array($window)){
                            if ( isset($_POST['submit'])) {
                                if(!$curRec){
                                    SkysaApps_Admin_CreateDBTable($table_name,$app_id,$options,$window);
                                }
                                foreach($window as $field => $value){
                                    if(isset($_POST[$field])){
                                            
                                        $updateArray[$field] = $_POST[$field];
                                    }
                                }
                                unset($value);
                                $wpdb->update( 
	                                $table_name, 
                                    $updateArray,
	                                array('app_id' => $app_id)
                                );
                                                    
                            }
                            foreach($window as $field => $value){
                                if(isset($_POST[$field])){
                                    $defaultvalue = $_POST[$field];
                                }
                                else if($curRec && !is_null($curRec[$field]) && $curRec[$field] !== 0 && $curRec[$field] != ''){
                                    $defaultvalue = $curRec[$field];
                                }
                                else{
                                    $defaultvalue = $value;
                                }
                                if(is_null($defaultvalue)) $defaultvalue = '';
                                switch($field){
                                    case 'width':
                                        $label = 'Window Width';
                                        $after = 'px';
                                        $type = 'text';
                                        $opt = null;
                                        $size = '10|1';
                                        break;
                                    case 'height':
                                        $label = 'Window Height';
                                        $after = 'px';
                                        $type = 'text';
                                        $opt = null;
                                        $size = '10|1';
                                        break;
                                    case 'position':
                                        $label = 'Window Position';
                                        $after = '';
                                        $type = 'selectbox';
                                        $opt = 'Page Center|Above the Bar';
                                        $size = '20|1';
                                        break;
                                }
                                ?>
                                <tr>
						            <th valign="top" scrope="row">
							            <label for="field-<?php echo $field; ?>"><?php echo $label; ?>:</label>
						            </th>
						            <td valign="top">
							            <?php
                                        SkysaApps_Admin_BoxDisplay($type,$field,$defaultvalue,$size,is_null($opt) ? $value : $opt);
                                        echo $after;
                                        ?>
						            </td>
                                </tr>
                                <?php
                            }
                            unset($value); 
                        }
                        ?>
                    </table>
				</div>
			</div>
			<div class="submit">
				<input type="submit" class="button-primary" name="submit" value="Save Settings" />
			</div>
		</form>
        <?php
    }
    else{
        ?>
        <h2>No settings available.</h2>
        <?php
    }
    ?>
      
    </div>
    <script type="text/javascript" src="<?php echo plugins_url( '/jquery.datepick.js', __FILE__ ) ?>"></script>
    <script type="text/javascript">
        // Attach functions for date and image input types.
        jQuery('input.show-datepick').datepick({ 
            renderer: jQuery.extend({}, jQuery.datepick.defaultRenderer, 
            {picker: jQuery.datepick.defaultRenderer.picker.replace(/\{link:clear\}/, '')})});
        jQuery(document).ready(function() {

        var sendtoEditor = function(html) {
            if(window.activeformfield){
             imgurl = jQuery('img',html).attr('src');
             jQuery('#field-'+window.activeformfield).val(imgurl);
                window.activeformfield = false;
             }
             if(window.prev_send_to_editor){
                window.send_to_editor = window.prev_send_to_editor;
             }
             tb_remove();
        };

        
        if(window.tb_show && jQuery('.upload_image_button').length > 0){
        if(window.send_to_editor) window.prev_send_to_editor = window.send_to_editor;
        window.original_tb_remove = tb_remove;
        tb_remove = function () { 
            if(window.prev_send_to_editor){
                window.send_to_editor = window.prev_send_to_editor;
             }
            original_tb_remove(); 
            return false; 
        } 
        jQuery('.upload_image_button').click(function(e) {
         window.activeformfield = jQuery(this).attr('id').split('button-')[1];
         tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
            window.send_to_editor = sendtoEditor;
         return false;
        });
        }
        else{
            jQuery('.upload_image_button').remove();
        }

        });
    </script>
	<?php
	wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('my-upload');
    wp_enqueue_style('thickbox');

};

// START Main Config Page
function SkysaApps_Admin_ConfigLite() {
	$skysa_hide = get_option('SkysaAppbarHide');
	if ( isset($_POST['submit']) ) {
		if ($_POST['skysa_hide'] == 'on')
		{
			$skysa_hide = 1;
		}
		else
		{
			$skysa_hide = 0;
		}
		update_option('SkysaAppbarHide', $skysa_hide);
		echo "<div id=\"updatemessage\" class=\"updated fade\"><p>Skysa settings updated.</p></div>\n";
		echo "<script type=\"text/javascript\">setTimeout(function(){jQuery('#updatemessage').hide('slow');}, 3000);</script>";	
	}
	?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2>Skysa App Bar Lite Configuration</h2>
	<div class="postbox-container">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<form action="" method="post" id="skysa-conf">
					<div id="bar_settings" class="postbox">
						<div class="handlediv" title="Click to toggle" style="display: none;">
							<br />
						</div>
						<h3 class="hndle" style="cursor: default;">
							<span>Bar Settings</span>
						</h3>
						<div class="inside">
							<table class="form-table">
								<tr>
									<th valign="top" scrope="row">
										<label for="skysa_hide">Logged-Out Bar Visibility:</label>
									</th>
									<td valign="top">
										<input type="checkbox" id="skysa_hide" name="skysa_hide" <?php echo ($skysa_hide ? 'checked="checked"' : ''); ?> /> <label for="skysa_hide">Hide Skysa Bar for Logged-Out Users?</label><br/>
									</td>
								</tr>
							</table>
						</div>
					</div>
                    <div class="postbox closed"><div class="handlediv" title="Click to toggle"><br /></div><h3 class="hndle" style="cursor: pointer;">Want more?</h3><div class="inside">
                        <div>Loading information...</div>
                    </div></div>
					<div class="submit">
						<input type="submit" class="button-primary" name="submit" value="Save Settings" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
    //jQuery(document).ready(function() {
        jQuery('.postbox .hndle').click(function(e){
            var box = jQuery(this).closest('.postbox');
            if(box.hasClass('closed')){
                box.removeClass('closed');
                if(!box.hasClass('loaded')){
                    jQuery.getJSON('http://www.skysa.com/plugins/wordpress/standalone/?callback=?', function(data) {
                      box.addClass('loaded');
                      box.children('.inside').html(data.str);
                    });
                }
            }
            else{
                box.addClass('closed');
            }
        });//hide('slow');
   // });
</script>
<?php
} ;

if ( ! function_exists( 'is_ssl' ) ) {
	function is_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
	if ( 'on' == strtolower($_SERVER['HTTPS']) )
		return true;
	if ( '1' == $_SERVER['HTTPS'] )
		return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
	return true;
	}
	return false;
	}
}

if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
	$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
} else {
	$wp_content_url = get_option( 'siteurl' );
}
$wp_content_url .= '/wp-content';
$wp_content_dir = ABSPATH . 'wp-content';
$wp_plugin_url = $wp_content_url . '/plugins';
$wp_plugin_dir = $wp_content_dir . '/plugins';
$wpmu_plugin_url = $wp_content_url . '/mu-plugins';
$wpmu_plugin_dir = $wp_content_dir . '/mu-plugins';
global $_registered_pages;
global $SkysaApps; 

if(!$_registered_pages[get_plugin_page_hookname( 'skysa-key-config', '' )]){ // check if the parent Skysa menu exists. If not, create it.
	add_menu_page(__('Skysa Bar Apps'), __('Skysa Bar Apps'), 'manage_options', 'skysa-key-config', 'SkysaApps_Admin_ConfigLite', plugins_url( '/icon.png', __FILE__ ));
	add_submenu_page( 'skysa-key-config',__('Bar Configuration'), __('Bar Configuration'), 'manage_options', 'skysa-key-config', 'SkysaApps_Admin_ConfigLite');
}

// add the options pages for apps.
foreach (array_keys($SkysaApps->appList) as $app) {
	add_submenu_page( 'skysa-key-config', __($SkysaApps->appList[$app]['label']), __($SkysaApps->appList[$app]['label']), 'manage_options', 'skysa_app_'.$app,'SkysaApps_Admin_AppPage' );
}
unset($app);
