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

//==============================================================
// Page de du graphique chart
//==============================================================			
function WTU_chart() {
	$Retour = @'
	<!-- TradingView Widget BEGIN -->
	<div class="tradingview-widget-container">
	  <div id="tradingview_16b45"></div>
	  <div class="tradingview-widget-copyright"><a href="https://www.tradingview.com/symbols/BINANCE-BTCUSDT/" rel="noopener" target="_blank"><span class="blue-text">BTCUSDT</span> <span class="blue-text">chart</span> by TradingView</a></div>
	  <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
	  <script type="text/javascript">
	  new TradingView.widget(
	  {
	  "width": 980,
	  "height": 610,
	  "symbol": "BINANCE:BTCUSDT",
	  "interval": "60",
	  "timezone": "Etc/UTC",
	  "theme": "Light",
	  "style": "1",
	  "locale": "fr",
	  "toolbar_bg": "#f1f3f6",
	  "enable_publishing": false,
	  "withdateranges": true,
	  "hide_side_toolbar": true,
	  "allow_symbol_change": true,
	  "watchlist": [
	    "BINANCE:BTCUSDT",
	    "BINANCE:NEOUSDT",
	    "BINANCE:ETHUSDT",
	    "BINANCE:BCCUSDT",
	    "BINANCE:LTCUSDT"
	  ],
	  "details": true,
	  "hideideas": true,
	  "studies": [
	    "BB@tv-basicstudies",
	    "PSAR@tv-basicstudies"
	  ],
	  "show_popup_button": false,
	  "popup_width": "1000",
	  "popup_height": "650",
	  "referral_id": "8874",
	  "container_id": "tradingview_16b45"
	}
	  );
	  </script>
	</div>
	<!-- TradingView Widget END -->
			';
	echo $Retour;
}
?>