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

function WTU_overview() {
	//==============================================================
	// Page overview
	//==============================================================			
	$Retour = @'
	<!-- TradingView Widget BEGIN -->
	<span id="tradingview-copyright"><a href="nofollow noopener" target="_blank" href="http://fr.tradingview.com" style="color: rgb(173, 174, 176); font-family: &quot;Trebuchet MS&quot;, Tahoma, Arial, sans-serif; font-size: 13px;">Marché des cryptodevises par <span style="color: #3BB3E4">TradingView</span></a></span>
	<script src="https://s3.tradingview.com/external-embedding/embed-widget-screener.js">{
	  "width": "1000",
	  "height": "500",
	  "defaultColumn": "overview",
	  "screener_type": "crypto_mkt",
	  "displayCurrency": "USD",
	  "referral_id": "8874",
	  "locale": "fr"
	}</script>
	<!-- TradingView Widget END -->
	';
	echo $Retour;
}
?>