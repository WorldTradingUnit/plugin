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

function BuyWTU() {
	$return = @'
<div>
  <a class="buy-with-crypto"
     href="https://commerce.coinbase.com/checkout/d6b5b983-3034-442d-908f-212e1c374c82">
    <span>Buy WTU</span>
  </a>
  <script src="https://commerce.coinbase.com/v1/checkout.js">
  </script>
</div>';
	return $return;
}
?>
