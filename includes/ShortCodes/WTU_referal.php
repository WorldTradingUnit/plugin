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

function Referal() {
	if (in_array('cimy-user-extra-fields/cimy_user_extra_fields.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
		$user = get_userdata(get_current_user_id());
		// —————————————–
		// Lecture Balance WTU et UserKey du compte client
		// —————————————–
		$UserApiKey = get_user_meta($user->ID, 'WTU_UserApiKey',True);	
		if ($UserApiKey != "") {
			$Retour = "<div>";
			$Retour .= __("your referal link is") . " : <br><p>". wp_registration_url() . "?REFERAL=$UserApiKey</p>";
			$Retour .= "</div>";
			return $Retour;
		} else {
			return __("You must param your WTU Acount for this Action");
		}
	} else {
		return __("You must install plugin 'cimy user extra fields' and configure REFERAL field with lenght=42 for use referal programme");
	}
		
}

?>