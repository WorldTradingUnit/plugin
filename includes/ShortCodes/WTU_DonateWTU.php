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

function DonateWTU() {
	$return = @'
<div>
  <a class="donate-with-crypto"
     href="https://commerce.coinbase.com/checkout/f4750d0a-a126-4ea7-bf4e-a694cf2e1808">
    <span>Donate for WTU Team</span>
  </a>
  <script src="https://commerce.coinbase.com/v1/checkout.js">
  </script>
</div>';
	return $return;
}
?>
