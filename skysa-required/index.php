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

// Setup Class and Global Object and Register Local Required Includes

// Nothing should EVER be changed in this file except the version number of
// the SDK the files are from on line 114.

if ( !function_exists( 'add_action' ) ) {exit;}
if(!$GLOBALS['SkysaApps']){
	class skysa_plugins_constructor {
		private $functions = array();
		private $printFunctions = array();
		public $appList = array();

		public function RegisterFunctions($v,$adminsetup,$output,$ajax){
            if(!isset($this->functions['version']) || $this->functions['version'] < $v){
                $this->functions = array(
                    'version' => $v,
                    'adminsetup' => $adminsetup,
                    'output' => $output,
                    'ajax' => $ajax
                );
            };
		}
		public function AdminSetup(){
            include $this->functions['adminsetup'];
		}
        public function Output(){
            include $this->functions['output'];
		}
        public function Ajax(){
            include $this->functions['ajax'];
		}
		public function RegisterApp($app){
			$this->appList[count($this->appList)] = $app;
		}
	}
    $GLOBALS['SkysaApps'] = new skysa_plugins_constructor;

	function SkysaApps_Admin(){
		global $SkysaApps;
		$SkysaApps->AdminSetup();
	};
    function SkysaApps_Output(){
		global $SkysaApps;
		$SkysaApps->Output();
	};
    function SkysaApps_Ajax(){
		global $SkysaApps;
		$SkysaApps->Ajax();
	};

    if(!is_admin()){
	    if( function_exists( 'wp_print_footer_scripts' ) ) {
		    if(!in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php')))
			    add_action( 'wp_print_footer_scripts', 'SkysaApps_Output',11 );
	    } else {
		    add_action( 'wp_footer', 'SkysaApps_Output',11 );
	    }
    }
    else{
		add_action('admin_menu', 'SkysaApps_Admin',11);
    }
    add_action( 'wp_ajax_skysa_appload', 'SkysaApps_Ajax');
    add_action( 'wp_ajax_nopriv_skysa_appload', 'SkysaApps_Ajax');
};

// Include URLs, with version number. The newest version will be used.
$GLOBALS['SkysaApps']->RegisterFunctions(1.8,dirname( __FILE__ ) . '/admin.php',dirname( __FILE__ ) . '/output.php',dirname( __FILE__ ) . '/ajax.php');

// Compatibility with older versions of PHP/WP which do not have json_encode/json_decode functions built in.
if (!function_exists('json_encode')) {
    function json_encode($data) {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = json_encode($value);
                    $output_associative[] = json_encode($key) . ':' . json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else {
                    return '{' . implode(',', $output_associative) . '}';
                }
            default:
                return ''; // Not supported
        }
    }
}
if (!function_exists('json_decode')) {
    function json_decode($json) {
      $comment = false;
      $out     = '$x=';
      for ($i=0; $i<strlen($json); $i++) {
        if (!$comment) {
          if (($json[$i] == '{') || ($json[$i] == '[')) {
            $out .= 'array(';
          }
          elseif (($json[$i] == '}') || ($json[$i] == ']')) {
            $out .= ')';
          }
          elseif ($json[$i] == ':') {
            $out .= '=>';
          }
          elseif ($json[$i] == ',') {
            $out .= ',';
          }
          elseif ($json[$i] == '"') {
            $out .= '"';
          }
          /*elseif (!preg_match('/\s/', $json[$i])) {
            return null;
          }*/
        }
        else $out .= $json[$i] == '$' ? '\$' : $json[$i];
        if ($json[$i] == '"' && $json[($i-1)] != '\\') $comment = !$comment;
      }
      eval($out. ';');
      return $x;
    }
  }