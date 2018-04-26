<?php
/**
 * @package WorldTradingUnit_Wordpress
 * @author Dominique Durand
 * @version 1.0
 */
/*
Plugin Name: WorldTradingUnit
Plugin URI: https://WorldTradingUnit.io
Description: This plugin WorldTradingUnit is designed to add Exhange Plateform on your web site and Trader's Acounts
Author: Dominique Durand 2018
Version: 1.0
Author URI: https://WorldTradingUnit.io/
Text Domain: WorldTradingUnit
Domain Path: /lang/
*/

/*
Copyright 2018 Dominique Durand
*/

if (!function_exists('get_option')) {
  header('HTTP/1.0 403 Forbidden');
  die;  // Silence is golden, direct call is prohibited
}
if (defined('WTU_PLUGIN_URL')) {
   wp_die('It seems that other version of WorldTradingUnit is active. Please deactivate it before use this version');
}

define('WTU_VERSION', '4.40');
define('WTU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WTU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WTU_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('WTU_PLUGIN_FILE', basename(__FILE__));
define('WTU_PLUGIN_FULL_PATH', __FILE__);
define('WTU_Dialog',"https://engine.worldtradingunit.io/Dialog.php");

// —————————————–
// WTU_Max_Capital est a lire dans les parametres du plugin
// —————————————–
$Settings = get_option( 'WTU_settings');
define('WTU_Max_Capital',$Settings['WTU_MaxCapital']);
//Autoriser les shortcodes dans les widgets
if ( !is_admin() ) {
	add_filter('widget_text', 'do_shortcode');
}
//require_once(WTU_PLUGIN_DIR.'includes/classes/WTU-base-lib.php');
require_once(WTU_PLUGIN_DIR.'includes/classes/WTU-lib.php');
require_once(WTU_PLUGIN_DIR .'includes/loader.php');
//$GLOBALS['WorldTradingUnit'] = WorldTradingUnit::get_instance();
function WTU_debug($D) {
	echo "<pre>";
	print_r($D);
	echo "<pre>";
}
?>