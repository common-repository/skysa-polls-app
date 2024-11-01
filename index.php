<?php
/*
Plugin Name: Skysa Polls App
Plugin URI: http://wordpress.org/extend/plugins/skysa-polls-app
Description: Add multiple polls to your website which can popup if a user has not yet seen one.
Version: 1.8
Author: Skysa
Author URI: http://www.skysa.com
*/

/*
*************************************************************
*                 This app was made using the:              *
*                       Skysa App SDK                       *
*    http://wordpress.org/extend/plugins/skysa-app-sdk/     *
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

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) exit;

// Skysa App plugins require the skysa-req subdirectory,
// and the index file in that directory to be included.
// Here is where we make sure it is included in the project.
include_once dirname( __FILE__ ) . '/skysa-required/index.php';


//POLLS APP
$GLOBALS['SkysaApps']->RegisterApp(array(
    'id' => '501b364156e93',
    'label' => 'Polls',
	'options' => array(
		'bar_label' => array(
            'label' => 'Button Label',
			'info' => 'What would you like the bar link label name to be?',
			'type' => 'text',
			'value' => 'Poll',
			'size' => '30|1'
		),
        'icon' => array(
            'label' => 'Button Icon URL',
            'info' => 'Enter a URL for the an Icon Image. (You can leave this blank for none)',
			'type' => 'image',
			'value' => plugins_url( '/icons/poll-icon.png', __FILE__ ),
			'size' => '50|1'
        ),
        'title' => array(
            'label' => 'App Title',
            'info' => 'What would you like to set as the title for the Polls window?',
			'type' => 'text',
			'value' => 'Site Polls',
			'size' => '30|1'
        ),
        'option1' => array(
            'label' => 'Auto Popup for New Polls',
            'info' => 'Would you like this App to Popup for your visitor when you have a new poll which they have not yet seen?',
			'type' => 'selectbox',
			'value' => 'Yes|No',
			'size' => '10|1'
        ),
        'option2' => array(
            'label' => 'Track Voted by IP',
            'info' => 'If IP tracking is turned on, only one vote is allowed per IP address; otherwise voted tracking is done using a browser cookie.',
            'type' => 'selectbox',
            'value' => 'No|Yes',
            'size' => '10|1' 
        )
	),
    'window' => array(
        'width' => '350',
        'height' => '250',
        'position' => 'Page Center'
    ),
    'manage' => array(
        'label' => 'Polls',
        'add_label' => 'Add Poll',
        'dis_edit' => 1, // Disable editing.
        'records' => array(
            'question' => array(
			    'label' => 'Poll Question',
			    'type' => 'text',
			    'value' => '',
			    'size' => '30|1'
		    ),
            'answers' => array(
			    'label' => 'Poll Answers',
                'info' => 'Enter one answer per line.',
			    'type' => 'textarea',
			    'value' => '',
			    'size' => '50|6',
                'output' => 'skysa_app_polls_manage_output'
		    ),
            'expires' => array(
                'label' => 'Expiration Date',
			    'info' => 'Date to display the poll until.',
			    'type' => 'date',
			    'value' => date("m/d/Y",mktime(0,0,0,date("m"),date("d")+7,date("Y"))), // default to 1 week from today
			    'size' => '8|1'
		    ),
            'votes' => array( // Set a hidden field for storing poll votes.
                'type' => 'hidden',
                'value' => ''
            ),
            'votedip' => array(
                'type' => 'hidden',
                'value' => ''
            )
        )
    ),
    'fvars' => array(
        'created' => skysa_app_polls_fvar_created
    ),
    'views' => array(
        'main' => skysa_app_polls_view_main
    ),
    'html' => '<div id="$button_id" class="bar-button" time="#fvar_created" apptitle="$app_title" w="$app_width" h="$app_height" bar="$app_position">$app_icon<span class="label">$app_bar_label</span></div>',
    'js' => "
        S.on('click',function(){S.open('window','main')});
        S.load('cssStr','.SKYUI-polls h3 { font-size: 20px;}');
     "
));

// // Main Poll App View function
function skysa_app_polls_view_main($rec,$saveitem){ // second variable is a save content item function($sk_recid,$field,$newval);
    $str = '';
    if($rec['content'] && count($rec['content']) > 0){
        if(isset($_GET['page'])){ // Look for the page query string parameter.
            $pageindex = $_GET['page'];
        }
        else{ // If the page has not been set, set the page index to zero.
            $pageindex = 0;
        }
        $recCount = count($rec['content']); // Count the total polls which have been added.
        $page = array_slice($rec['content'],$pageindex,1,false); // Make an array with a single item based on the page.
        $item = $page[0]; // Set the item based on the page array.
        $answers = explode(chr(13),$item->answers); // Get all the answers for the current poll.
        $votes = $item->votes; // Get all the votes.
        $votes = explode(',',$votes); // Turn the votes into an array.
        if(array_key_exists('votedip',$item)){
            $ips = explode(',',$item->votedip);
            $ips = array_flip($ips);
        }
        else{
            $ips = array();
        }
        $voteCount = 0; // Set a total vote count variable to store the total number of votes for the poll.
        
        foreach($answers as $i => $answer){ // Count the votes
            if(!array_key_exists($i,$votes)){
                $votes[$i] = 0;
            }
            else{
                $voteCount += intval($votes[$i]);
            }
        }
        unset($answer);
        unset($i);
        
        $exp = strtotime($item->expires); // Set an expires time varible based on the date the poll is set to end.
        if(isset($_COOKIE["sk_app_poll_voted"])){ // Check if the user has voted on any polls.
            $voted = explode(',',$_COOKIE["sk_app_poll_voted"]);
            $voted = array_flip($voted); // flip voted array to allow easy checking against keys to see if the user has already voted on this poll.
        }
        else{
            $voted = array(); // So we can check against a voted array without errors later, set the voted variable to an array if not set cookie is not found.
        }
        if(isset($_GET['vote'])){ // Check for the 'vote' query string parameter. This is set if a vote needs to be recorded because a vote link has been clicked.
            $voted[$item->sk_recid] = 1; // Set the voted variable for this poll.
            $ips[$_SERVER["REMOTE_ADDR"]] = 1;
            $votes[intval($_GET['vote'])] += 1;
            $voteCount += 1;
            
            //save the vote to this poll record in the database using the $saveitem function.
            $saveitem($item->sk_recid,'votes',implode(',',$votes)); // item record ID, record field (the hidden votes field), the new votes value. The votes value is based on the votes array implode to turn into a comma seperated string.
            $saveitem($item->sk_recid,'votedip',implode(',',array_keys($ips))); 
            setcookie("sk_app_poll_voted", implode(',',array_keys($voted)), time()+60*60*24*90,'/'); // Save a cookie to the user to not allow further voting and to just display poll results.
        }
        
        $str .= '<div class="SKYUI-polls">
            <h3 style="padding:0px;margin:0px 0px 10px 0px">' . $item->question . '</h3>'; // Set the opening CSS for the poll.
        if($exp > time() && !array_key_exists($item->sk_recid,$voted) && ($rec['option2'] != 'Yes' || !array_key_exists($_SERVER["REMOTE_ADDR"],$ips))){ // If the poll is not expired and has not been voted on by the current viewer, show the answer options and vote links. Vote links have a class as button, so they display as buttons.
            $answerindex = 0;
            foreach($answers as $i => $answer){ // setup the HREF in the vote link to point to this view, set the current page and set the vote answer number.
                $str .= '<div style="margin:5px 0; padding:3px 0;"><a href="#view=main&page='.$pageindex.'&vote='.$answerindex.'" class="button" style="margin-left: 0; margin-right: 5px;">Vote</a> <strong>' . $answer .'</strong></div>';
                $answerindex++;
            }
            unset($answer);
            unset($answerindex);
            $str .= '<p style="margin-top: 10px;">This poll was created on <strong>'.date("F j, Y",$item->sk_created+ (get_option( 'gmt_offset' )*3600)).'</strong> and closes on <strong>'.date("F j, Y",$exp+ (get_option( 'gmt_offset' )*3600)).'</strong></p>';
        }
        else{ // If the poll has expired or has already been voted on by the current viewer, draw the results.
            foreach($answers as $i => $answer){
                $answerVotes = $votes[$i] && $votes[$i] != '' ? $votes[$i] : 0;
                $percent = $answerVotes != 0 && $voteCount != 0 ? round($answerVotes/($voteCount/100)) : 0; // calculate percentages based on answer count and total poll answer count.
                $str .= '<div style="margin:5px 0; padding:3px 0;"><div style="margin-bottom: 3px;">'.$answer.' - <strong>Votes: '.$answerVotes.' ('.$percent.'%)</strong></div><div class="SKYUI-Poll-bar-outer"><div class="SKYUI-Poll-bar" style="width: '.$percent.'%;"></div></div>';
            }
            unset($answer);
            $str .= '<p style="margin-top: 10px;">Total Votes: <strong>'.$voteCount.'</strong></p><p style="margin-bottom: 0;">This poll was created on <strong>'.date("F j, Y",$item->sk_created+ (get_option( 'gmt_offset' )*3600)).'</strong> and ';
            if($exp <= time()){
                $str .= 'closed on <strong>'.date("F j, Y",$exp+ (get_option( 'gmt_offset' )*3600)).'</strong></p>';
            }
            else{
                $str .= 'closes on <strong>'.date("F j, Y",$exp+ (get_option( 'gmt_offset' )*3600)).'</strong></p>';
            }
        }
        $str .= '</div>';
        if($recCount > 1){ // Setup same basic Older, Newer pageing. 
            $str .= '<!--D--><div style="text-align: center;">'; // App content can be devided by the <!--D--> tag to add the content after the tag to the footer of the app window instead of the body section.
            if($pageindex != $recCount -1){
                $str .= '<a href="#view=main&page='.($pageindex + 1).'" class="button" style="margin: 0 3px;">Older</a>';
            }
            if($pageindex != 0){
                 $str .= '<a href="#view=main&page='.($pageindex - 1).'" class="button" style="margin: 0 3px;">Newer</a>';
            }
            $str .= '</div>';
        }
    }
    if($str == ''){ // No active polls
        $str = 'There are no active polls.';
    }
    return $str;
}

// Polls Manage Records Output
function skysa_app_polls_manage_output($item){
    $str = '';
    $answers = explode(chr(13),$item->answers); // Get all the answers for the current poll.
    $votes = $item->votes; // Get all the votes.
    $votes = explode(',',$votes); // Turn the votes into an array.
    $voteCount = 0; // Set a total vote count variable to store the total number of votes for the poll.
        
    foreach($answers as $i => $answer){ // Count the votes
        if(!array_key_exists($i,$votes)){
            $votes[$i] = 0;
        }
        else{
            $voteCount += intval($votes[$i]);
        }
    }
    unset($answer);
    unset($i);
    foreach($answers as $i => $answer){
        $answerVotes = $votes[$i] && $votes[$i] != '' ? $votes[$i] : 0;
        $percent = $answerVotes != 0 && $voteCount != 0 ? round($answerVotes/($voteCount/100)) : 0; // calculate percentages based on answer count and total poll answer count.
        $str .= '<div style="margin:3px 0;"><div style="margin-bottom: 3px;">'.$answer.' - <strong>Votes: '.$answerVotes.' ('.$percent.'%)</strong></div><div style="height: 10px; border: 1px solid; border-color: #ddd #eee #fff #eee; background: #f5f5f5 ; -moz-box-shadow: inset 0 5px 5px rgba(0,0,0,0.05); -webkit-box-shadow: inset 0 5px 5px rgba(0,0,0,0.05); box-shadow: inset rgba(0,0,0,0.05) 0 5px 5px;"><div class="SKYUI-Poll-bar" style="width: '.$percent.'%; font-size: 10px; font-weight: bold; color: white; background: #166ccd; height: 10px; overflow: hidden; -moz-box-shadow: inset 0 0px 1px rgba(0,0,0,0.5), inset 0 5px 0px rgba(255,255,255,0.25), inset 0 0px 5px rgba(255,255,255,1); -webkit-box-shadow: inset 0 0px 1px rgba(0,0,0,0.5), inset 0 5px 0px rgba(255,255,255,1), inset 0 0px 5px rgba(255,255,255,1); box-shadow: inset rgba(0,0,0,0.5) 0 0px 1px, inset rgba(255,255,255,0.25) 0 5px 0px, inset rgba(255,255,255,1) 0 0px 5px;"></div></div>';
    }
    unset($answer);
    $str .= '<p style="margin: 3px 0;">Total Votes: <strong>'.$voteCount.'</strong></p>';
    return $str;
}

// Polls Created Function Variable
function skysa_app_polls_fvar_created($rec){
    if($rec['content'] && count($rec['content']) > 0 && $rec['option1'] == 'Yes'){
        foreach( $rec['content'] as $created => $item ){
            $exp = strtotime($item->expires);
            if($exp > time()){
                return $created+ (get_option( 'gmt_offset' )*3600);
                break;
            }
        }
    }
    return 0;
}
?>