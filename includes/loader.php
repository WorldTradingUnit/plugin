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
/*
// —————————————–
// Charge JQuery
// —————————————–
	// Pour des raisons de performance ne pas charger ceux de WordPress et d’utiliser les CDN de google à la place 
	// Charge JQuery
	wp_enqueue_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js', array(), '1.7',false );
	wp_enqueue_script( 'jquery' ); // voir liste script dispo sur https://developer.wordpress.org/reference/functions/wp_enqueue_script/#Default_Scripts_Included_and_Registered_by_WordPress	
	// Charge JQuery UI Dialog
	wp_deregister_script( 'jquery-ui-core' );
	wp_deregister_script( 'jquery-ui-tab' );
	wp_deregister_script( 'jquery-ui-autocomplete' );
	wp_deregister_script( 'jquery-ui-accordion' );
	wp_deregister_script( 'jquery-ui-autocomplete' );
	wp_deregister_script( 'jquery-ui-button' );
	wp_deregister_script( 'jquery-ui-datepicker');
	wp_deregister_script( 'jquery-ui-dialog' );
	wp_deregister_script( 'jquery-ui-draggable' );
	wp_deregister_script( 'jquery-ui-droppable' );
	wp_deregister_script( 'jquery-ui-mouse' );
	wp_deregister_script( 'jquery-ui-position' );
	wp_deregister_script( 'jquery-ui-progressbar');
	wp_deregister_script( 'jquery-ui-resizable' );
	wp_deregister_script( 'jquery-ui-selectable');
	wp_deregister_script( 'jquery-ui-slider' );
	wp_deregister_script( 'jquery-ui-sortable' );
	wp_deregister_script( 'jquery-ui-tabs' );
	wp_deregister_script( 'jquery-ui-widget' );
	$urlui = "https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/jquery-ui.min.js";
	wp_enqueue_script( 'jquery-ui-core', $urlui, array( 'jquery' ), '1.8', false);

	// load the jquery ui theme
	wp_enqueue_style('jquery-ui-dialog', $urlui, false, null);
	
*/	
// —————————————–
// Load Cryptos name table
// —————————————–
include (WTU_PLUGIN_DIR . "includes/CryptoNames.php");
require_once(WTU_PLUGIN_DIR.'ccxt/ccxt.php');
require_once(WTU_PLUGIN_DIR . "includes/CryptoNames.php");
require_once(WTU_PLUGIN_DIR.'phpseclib/Math/BigInteger.php');
require_once(WTU_PLUGIN_DIR.'phpseclib/Crypt/RSA.php');
require_once(WTU_PLUGIN_DIR.'phpseclib/Crypt/AES.php');
//require_once(WTU_PLUGIN_DIR .'includes/css/Css_WorldTradingUnit.css');
require_once(WTU_PLUGIN_DIR .'includes/WTU_Account_Fields_User.php');
require_once(WTU_PLUGIN_DIR .'includes/WTU_Account_Fields_Exchanges.php');
require_once(WTU_PLUGIN_DIR .'includes/WTU_Account_Fields_Traders.php');
require_once(WTU_PLUGIN_DIR .'includes/Main_WorldTradingUnit.php');
require_once(WTU_PLUGIN_DIR .'includes/ShortCodes/ShortCodes_WorldTradingUnit.php');
?>