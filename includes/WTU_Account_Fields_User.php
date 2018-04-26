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

// —————————————–
// Create Form for WTU Fields
// —————————————–
function WTU_Account_User_Add( $user ) {
	// —————————————–
	// Lecture Balance WTU et UserKey du compte client
	// —————————————–
	$UserApiKey = get_user_meta($user->ID, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {	
		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user->ID;
		$action['UserApiKey']= $UserApiKey;
		$action['UserMail'] = $user->user_email;
		
		$reponse = WTU_Send($action,$user);
		$UserKey = $reponse['Userkey'];
		$Balance = $reponse['Balance'];
		$UserAffiliatedApiKey = $reponse['UserAffiliatedApiKey'];
	}
	//===================================================
	//	Le choix être ou ne pas être Trader
	//===================================================
	$WTU_Trader = get_user_meta($user->ID, 'WTU_Trader',True);	
	//===================================================
	//	Le compte WTU
	//===================================================
?>
<script type='text/javascript' >
jQuery(document).ready(function() {
	jQuery('#slideMe').hide();
   	jQuery('#clickMe').click(function() {
   		jQuery('#slideMe').slideToggle(400);
   		return false;
	});
	jQuery('#slideMe1').hide();
   	jQuery('#clickMe1').click(function() {
   		jQuery('#slideMe1').slideToggle(400);
   		return false;
	});
});
</script>
<script type="text/javascript" src="<?php echo WTU_PLUGIN_URL;?>js/jquery.qrcode.min.js"></script>
<table class="form-table">
		<tr>
			<th colspan=4>
				<label for="WTU_UserAccount"><?php _e("WorldTradingUnit account")?></label>
			</th>
		</tr>
		<tr>
				<th style=\"text-align : center;\"><?php _e("Trader") ?></th>
				<th style=\"text-align : center;\"><?php _e("Notoriety") ?></th>
				<th style=\"text-align : center;\"><?php _e("Balance") ?></th>
				<th style=\"text-align : center;\"><?php _e("WTU APIKey") ?></th>
		</tr>
		<tr>
			<td>
				<center>
				<input type="radio" name="WTU_Trader" value=true <?php if($WTU_Trader === 'true') echo "checked";?>><?php _e("Yes"); ?>
				<input type="radio" name="WTU_Trader" value=false <?php if($WTU_Trader === 'false') echo "checked";?>>      <?php _e("No"); ?>
				</center>
			</td>
			<td><input type="button" id="clickMe" value=" <?php echo $WTU_Account[Notoriety]; ?>"></td>
			<td>	<input type="button" name="Balance" value=" <?php echo $Balance; ?> WTU"></td>
			<td>	<input type="button" id="clickMe1" value=" <?php _e('View'); ?>"></td>
		</tr>
	</table>
	<div id="slideMe">
<?php
		echo "<hr>" . __("My notoriety is increased when another user clicks on &quot;I like&quot; in book of your trades.") . "<hr>";
?>
	</div>
	<div id="slideMe1">
<?php 
		echo $msg;
		if (is_array($reponse)) {
			echo ("<center>" .
			__("Your unique WTU ApiKey key allows you to get your WTU balance on all websites that have WTU_plugin installed.") .
			"<hr>" . 
			$UserApiKey  . 
			"<br><br>" . 
			"<div id='QRC1'></div><script>jQuery('#QRC1').qrcode('" . $UserApiKey . "');</script>" .
			"</center><hr>"
			);
		} else {
			if ($UserApiKey != "") {
				echo "<font color=red><b>" . __("You are enter a wrong ApiKey") . " : $UserApiKey</b></font><br><br>";
				unset($UserApiKey);
			}
		}
		if ($UserApiKey == "") {
			_e("If your have alway an ApiKey create on another web site hosting this WTU_plugin, your should enter it here but :<br><font color = red> <b>WARNING</b></font> if you enter an ApiKey not available, you can lose all your WTU balance and your WTU parametters.<br>");
			echo '<input type="text" name="WTU_UserApiKeyManual" value="'.$UserApiKey.'">';
			echo '<input type="submit" name="NewWallet" Value="'.__('Create New ApiKey').'"><br>';
		}
		if ($UserAffiliatedApiKey != "") echo "<font style='font-size:xx-small'>" . __('Affiliated ApiKey') . " : $UserAffiliatedApiKey </font>";
		echo "</div>";
}
add_action( 'show_user_profile', 'WTU_Account_User_Add' );
add_action( 'edit_user_profile', 'WTU_Account_User_Add' );

// —————————————–
// Save additional profile fields.
// —————————————–
function WTU_Account_User_Save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	if (isset($_POST['NewWallet'])) {
		$action['Action']= "CreateUserAccount";
		$action['UserId']= $user_id;
		$SiteApiKey = get_option( 'WTU_settings')['WTU_SiteAPIKEY'];
		$action['SiteApiKey'] = $SiteApiKey;
		if (in_array('cimy-user-extra-fields/cimy_user_extra_fields.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
			$action['UserAffiliatedApiKey'] =  get_cimyFieldValue($user_id, 'REFERAL');
		}
		$action['UserMail'] = $user->user_email;
		$reponse = WTU_Send($action,$user);
		if (is_array($reponse)) {
			$UserKey = $reponse['Userkey'];
			$UserApiKey = $reponse['UserApiKey'];
			$Balance = $reponse['Balance'];
			//$msg = "<br><font color=blue><b>" . __('A new WTU account are created for you') . "</b></font><br><br>";
			update_user_meta( $user_id, 'WTU_UserApiKey',  $UserApiKey  );
		//} else {
			//$msg = "<br><font color=red><b>$reponse</b></font><br><br>";
		}
	}
	
	// save Trader Yes/No params
	if (! empty( $_POST['WTU_Trader'] ) ) {
		update_user_meta( $user_id,'WTU_Trader', $_POST['WTU_Trader'] );
	} 
	// save WTU Account  UserApiKey
	$Length= strlen($_POST['WTU_UserApiKeyManual']);
	if ($Length == 42) {
		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $_POST['WTU_UserApiKeyManual'];
		$action['UserMail'] = $user->user_email;
		$user = get_userdata($user_id);
		$reponse = WTU_Send($action,$user);
		if (is_array($reponse)) {
			update_user_meta( $user_id, 'WTU_UserApiKey',  $_POST['WTU_UserApiKeyManual']  );
		}
	} else {
		// save WTU Account  UserApiKey
		if (! empty( $_POST['WTU_UserApiKey'] ) ) {
			update_user_meta( $user_id, 'WTU_UserApiKey',  $_POST['WTU_UserApiKey']  );
		}
	}
}
add_action( 'personal_options_update', 'WTU_Account_User_Save' );
add_action( 'edit_user_profile_update', 'WTU_Account_User_Save' );

?>
