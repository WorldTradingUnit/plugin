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


 //Detect plugin if wp-super-cache. For use on Front End only.
if (in_array('wp-super-cache/wp-cache.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
	// Gestion de "WP Super Cache" avec option  "Enable dynamic caching", 
	// ne pas oublier  de cocher 'Enable dynamic caching'  
	// ne pas oublier  de cocher 'Late init. Display cached files after WordPress has loaded.'
	// ne pas oublier de cocher  'Don’t cache pages with GET parameters. (?x=y at the end of a url)'
	// ne pas oublier de décocher  'Compresse les pages afin qu'elles soient servies plus rapidement aux visiteurs. (Recommandé)'
	// dans la config de "WP Super Cache"
	function dynamic_cache_WTU_safety( $safety ) {
		return 1;
	}
	add_cacheaction( 'wpsc_cachedata_safety', 'dynamic_cache_WTU_safety' );

	function dynamic_cache_WTU_filter($cachedata ) {
		if (strpos($cachedata,'OPEN_ORDER_HERE') !== false ) {
			$contenu = "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . "</font><br>" . OpenOrders();
			$cachedata = str_replace( 'OPEN_ORDER_HERE', $contenu, $cachedata );
		}
		
		if (strpos($cachedata,'BOOK_ORDER_HERE') !== false ) {
			$contenu = "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . "</font><br>" . BookOrders();
			$cachedata = str_replace( 'BOOK_ORDER_HERE', $contenu, $cachedata );
		}
		
		if (strpos($cachedata,'BALANCES_HERE') !== false ) {
			$contenu = "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . "</font><br>" . Balances();
			$cachedata = str_replace( 'BALANCES_HERE', $contenu, $cachedata );
		}
		return $cachedata;
	}
	add_cacheaction( 'wpsc_cachedata', 'dynamic_cache_WTU_filter' );
/*	
	function dynamic_cache_WTU_init() {
		add_action( 'wp_footer', 'dynamic_cache_WTU_template_tag' );
	}
	add_cacheaction( 'add_cacheaction', 'dynamic_cache_WTU_init' );
*/
}          

//===================================================
//   DÃ©finition des shortcodes
//===================================================
//------------------------------------------------------------------------
//   Buy WTU
//------------------------------------------------------------------------
include "WTU_BuyWTU.php";
function SC_WTU_BUYWTU() {
	$Return = BuyWTU();
	return $Return;
}
add_shortcode( 'WTU_BuyWTU', 'SC_WTU_BUYWTU');

//------------------------------------------------------------------------
//   Donate WTU
//------------------------------------------------------------------------
include "WTU_DonateWTU.php";
function SC_WTU_DONATEWTU() {
	$Return = DonateWTU();
	return $Return;
}
add_shortcode( 'WTU_DonateWTU', 'SC_WTU_DONATEWTU');

//------------------------------------------------------------------------
//   Referal WTU
//------------------------------------------------------------------------
include "WTU_referal.php";
function SC_WTU_REFERAL() {
	return Referal();
}
add_shortcode( 'WTU_Referal', 'SC_WTU_REFERAL');

//------------------------------------------------------------------------
//   Trading
//------------------------------------------------------------------------
include "WTU_trading.php";
function SC_WTU_TRADING() {
	return  Trading();
}
add_shortcode( 'WTU_trading', 'SC_WTU_TRADING');

//------------------------------------------------------------------------
//   OPEN ORDERS
//------------------------------------------------------------------------
include "WTU_open_orders.php";
function SC_WTU_OPENORDERS() {
/*
	global $cache_enabled;
	if ( $cache_enabled && function_exists( 'dynamic_cache_WTU_filter' ) ) {
	    return  "OPEN_ORDER_HERE";
	} else {
*/
		return  "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . " (no cache)</font><br>" . OpenOrders();
//	}
}
add_shortcode( 'WTU_open_orders', 'SC_WTU_OPENORDERS');

//------------------------------------------------------------------------
//   ORDERS BOOK
//------------------------------------------------------------------------
include "WTU_book_orders.php";
function SC_WTU_BOOKORDERS() {
/*
	global $cache_enabled;
	if ( $cache_enabled && function_exists( 'dynamic_cache_WTU_filter' ) ) {
	    return  "BOOK_ORDER_HERE";
	} else {
*/
		return  "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . " (no cache)</font><br>" . BookOrders();
//	}
}
add_shortcode( 'WTU_book_orders', 'SC_WTU_BOOKORDERS');

//------------------------------------------------------------------------
//  BALANCES
//------------------------------------------------------------------------
include "WTU_balances.php";
function SC_WTU_BALANCES() {
/*
	global $cache_enabled;
	if ( $cache_enabled && function_exists( 'dynamic_cache_WTU_filter' ) ) {
	    return  "BALANCES_HERE";
	} else {
*/
		return  "<font style='font-size: 6px; font-color: light-grey;'>" . date("Y-m-d H:i:s") . " (no cache)</font><br>" . Balances();
//	}
}
add_shortcode( 'WTU_balances', 'SC_WTU_BALANCES');

//------------------------------------------------------------------------
//   CHART
//------------------------------------------------------------------------
include "WTU_chart.php";
function SC_WTU_CHART() {
	$Return = WTU_chart();
	return $Return;
}
add_shortcode( 'WTU_chart', 'SC_WTU_CHART');
	
//------------------------------------------------------------------------
//   OVERVIEW
//------------------------------------------------------------------------
include "WTU_overview.php";
function SC_WTU_OVERVIEW() {
	$Return = WTU_overview();
	return $Return;
}
add_shortcode( 'WTU_overview', 'SC_WTU_OVERVIEW');


//------------------------------------------------------------------------
//   PERFORMANCE
//------------------------------------------------------------------------
include "WTU_performance.php";
function SC_WTU_PERFORMANCE() {
	$Return = WTU_performance();
	return $Return;
}
add_shortcode( 'WTU_performance', 'SC_WTU_PERFORMANCE');

//------------------------------------------------------------------------
//   OSCILATORS
//------------------------------------------------------------------------
include "WTU_oscilators.php";
function SC_WTU_OSCILATORS() {
	$Return = WTU_oscilators();
	return $Return;
}
add_shortcode( 'WTU_oscilators', 'SC_WTU_OSCILATORS');

//------------------------------------------------------------------------
//   MOVING AVERAGES
//------------------------------------------------------------------------
include "WTU_moving_averages.php";
function SC_WTU_MOVING_AVERAGES() {
	$Return = WTU_moving_averages();
	return $Return;
}
add_shortcode( 'WTU_moving_averages', 'SC_WTU_MOVING_AVERAGES');

//------------------------------------------------------------------------
//  ...
//------------------------------------------------------------------------



// les shortcodes peuvent aussi être appeler de cette façon :  echo do_shortcode('[WTU_balances]'); 
?>