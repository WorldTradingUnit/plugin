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
function WTU_Account_Exchanges_Add( $user ) {
	// —————————————–
	// Load Cryptos name table
	// —————————————–
	include (WTU_PLUGIN_DIR . "includes/CryptoNames.php");
	
	// —————————————–
	// Lecture Balance WTU et UserKey du compte client
	// —————————————–
	$UserApiKey = get_user_meta($user->ID, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user->ID;
		$action['UserApiKey']= $UserApiKey;
		$reponse = WTU_Send($action,$user);
		$UserKey = $reponse['Userkey'];
		$Balance = $reponse['Balance'];
		if (is_array($reponse)) {
?>
<script type="text/javascript" src="<?php echo WTU_PLUGIN_URL;?>js/jquery.qrcode.min.js"></script>
<?php 
	//====================================================
	// Gestion des Exchanges
	//====================================================
		//===================================================
		//   DÃ©finition des paramettres des plateformes
		//===================================================
		$default	= array(		'Exchange' => __('--Disabled--'),
							'ApiKey' => __("Your Api Key"),
							'SecretApiKey' => __("Your Secret Key"),
					);
?>
	<table class="form-table">
		<tr>
			<th colspan=3>
				<? _e("Your Exchanges");?>
			</th>
		</tr>
		<tr>
<?php
		//================================================
		// pour chaque groupe de parametres d'Exchange
		//================================================
		unset($WTU_ExangesList);
		$crypts = get_user_meta($user->ID, 'WTU_ExchangesParams',false);
		if (is_array($crypts)){
			foreach($crypts as $KeyOfExchangesParams => $crypt)
			{
				$crypt = str_replace(' ','+',$crypt);
				$crypt = base64_decode($crypt);
				$clair = f_decrypt($UserKey,$crypt);
				$clair = str_replace(' ','+',$clair);
				$data = unserialize(base64_decode($clair));
				$WTU_ExchangesParams = wp_parse_args( $data, $default );
				$WTU_ExangesList[] = $WTU_ExchangesParams['Exchange'];

				if ($WTU_ExchangesParams['Exchange'] != __('--Disabled--')) {
					if (isset( $WTU_ExchangesParams['ApiKey']) &&  $WTU_ExchangesParams['ApiKey'] !== "") {
						//The API credentials usually include the following:

						//-  ``apiKey``. This is your public API Key and/or Token. This part is *non-secret*, it is included in your request header or body and sent over HTTPS in open text to identify your request. It is often a string in Hex or Base64 encoding or an UUID identifier.
						//-  ``secret``. This is your private key. Keep it secret, don't tell it to anybody. It is used to sign your requests locally before sending them to exchanges. The secret key does not get sent over the internet in the request-response process and should not be published or emailed. It is used together with the nonce to generate a cryptographically strong signature. That signature is sent with your public key to authenticate your identity. Each request has a unique nonce and therefore a unique cryptographic signature.
						//-  ``uid``. Some exchanges (not all of them) also generate a user id or *uid* for short. It can be a string or numeric literal. You should set it, if that is explicitly required by your exchange. See `their docs <https://github.com/ccxt/ccxt/wiki/Manual#exchanges>`__ for details.
						//-  ``password``. Some exchanges (not all of them) also require your password/phrase for trading. You should set this string, if that is explicitly required by your exchange. See `their docs <https://github.com/ccxt/ccxt/wiki/Manual#exchanges>`__ for details.
						
						$plateforme =$WTU_ExchangesParams['Exchange'];		
						$exchange = '\\ccxt\\' . $plateforme;
						$exchange = new $exchange ();
						$exchange->apiKey = $WTU_ExchangesParams['ApiKey'];
						$exchange->secret = $WTU_ExchangesParams['SecretApiKey'];
						$exchange->verbose = false;
						$exchange->timeout = 30000;
						//  Lecture de la balance sur l'API de la plateforme choisie
						$err = "";
						$erreur=false;
						$errmess = "";
						try	{
							$balance = $exchange->fetch_balance ();
						}
						catch (\ccxt\NetworkError $e) {
							$err = $e->getMessage ();
							$errmess .= $plateforme . " : Line " . __LINE__ . " Network Error " . $err . "<br>";
							$erreur = true;
						} 
						catch (\ccxt\ExchangeError $e) {
							$err = $e->getMessage ();
							$errmess .= $plateforme . " : Line " . __LINE__ . " Exchange Error " . $err . "<br>";
							$erreur = true;
						} 
						catch (Exception $e) {
							$err = $e->getMessage ();
							$errmess .= $plateforme . " : Line " . __LINE__ . " Exeption Error " . $err . "<br>";
							$erreur = true;
						}	
					}
					If ($erreur === false && isset( $WTU_ExchangesParams['ApiKey']) &&  $WTU_ExchangesParams['ApiKey'] !== "") {
						$message = "<font color=green><img alt=\"" . $WTU_ExchangesParams['Exchange'] . "\" src=\"" . WTU_PLUGIN_URL . "images/exchanges/" . $WTU_ExchangesParams['Exchange'] . ".jpg\"> " . __(" API keys are correct") .	"</font>";
					} 
					else
					{
						if (isset( $WTU_ExchangesParams['ApiKey']) 
							&& $WTU_ExchangesParams['ApiKey']!= __("Your Api Key") 
							&& $WTU_ExchangesParams['SecretApiKey'] != __("Your Secret Key")
							&& $WTU_ExchangesParams['SecretApiKey'] !== "" 
							&&  $WTU_ExchangesParams['SecretApiKey'] !=  __("Your Api Key") 
							&& in_array ($WTU_ExchangesParams['Exchange'], \ccxt\Exchange::$exchanges)
						) {
							$message = "<img alt=\"" . $WTU_ExchangesParams['Exchange'] . "\" src=\"" . WTU_PLUGIN_URL . "images/exchanges/" . $WTU_ExchangesParams['Exchange'] . ".jpg\"><font color=red> $err</font>";
						}
					}
?>
		<tr>
			<td colspan=2>
				<?php echo $message;?>
			</td>
			<td>
				<input type="checkbox" name="WTU_EraseExchange[][<?php echo $plateforme;?>]" value="<?php echo $WTU_ExchangesParams['UniqueRef'];?>"><?php _e("Delete"); ?>
			</td>
		</tr>
<?php
				}		
			}
		}

		//=========================================================
		// Add new Exchange
		//=========================================================
?>
			<tr>
				<th colspan=3>
					<?php _e("New Exchange"); ?>
				</th>
			</tr>
			<tr>
				<th><?php _e("Name");?></th>
				<th><?php _e("Api Key");?></th>
				<th><?php _e("Secret Key"); ?></th>
			</tr>
			<tr>
				<td>
					<select id="Exchange" onchange="change();" name="WTU_ExchangesParams[Exchange]">
<?php
						//   Liste les plateformes d'Ã©change
						$Exchanges = \ccxt\Exchange::$exchanges;
						 echo "<option value='" .  __('--Disabled--') . "'>" .  __('--Disabled--') . "</option>";
						 foreach ( $Exchanges as $Exchange ) {
							 echo "<option value='$Exchange'>$Exchange</option>"; 
						 }				 
?>
					</select>
				</td>
				<td>
					<input  id="ApiKey" name="WTU_ExchangesParams[ApiKey]" value="">
				</td>
				<td>
					<input  id="SecretApiKey" name="WTU_ExchangesParams[SecretApiKey]" value="">
				</td>
			</tr>
		</table>
<?php
		}
	}
}
add_action( 'show_user_profile', 'WTU_Account_Exchanges_Add' );
add_action( 'edit_user_profile', 'WTU_Account_Exchanges_Add' );

// —————————————–
// Save additional profile fields.
// —————————————–
function WTU_Account_Exchanges_Save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	// —————————————–
	// Lecture Balance WTU et UserKey du compte client
	// —————————————–
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);
	if ($UserApiKey != "") {

		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$user = get_userdata($user_id);		
		$reponse = WTU_Send($action,$user);
		$UserKey = $reponse['Userkey'];
		$Balance = $reponse['Balance'];
	//==================================================
	// Exchanges parameters update
	//==================================================
		// Delete Exchanges marked 'WTU_EraseExchange'
		if (is_array($_POST['WTU_EraseExchange'])) {
			$crypts = get_user_meta($user_id, 'WTU_ExchangesParams',false);
			foreach($crypts as $KeyOfExchangesParams => $crypt)
			{
				$decode = str_replace(' ','+',$crypt);
				$decode = base64_decode($decode);
				$clair = f_decrypt($UserKey,$decode);
				$clair = str_replace(' ','+',$clair);
				$data = unserialize(base64_decode($clair));
				foreach($_POST['WTU_EraseExchange'] as $key => $WTU_EraseExchange)
				{
					if(	    $data['UniqueRef'] == $WTU_EraseExchange[$data['Exchange']]
						|| $data['Exchange'] == __('--Disabled--') 
						|| empty( $data['Exchange'] )
						|| empty( $data['ApiKey'] )
						|| empty( $data['SecretApiKey'] )
						
						) {
						delete_user_meta( $user_id, 'WTU_ExchangesParams', $crypt );
					}
				}
			}
		}
	//==================================================
	// New Exchanges params crypted
	//==================================================
		if ( !empty( $_POST['WTU_ExchangesParams']['Exchange'] ) ) {
			if (
				$_POST['WTU_ExchangesParams']['Exchange'] != __('--Disabled--')
				&& !empty( $_POST['WTU_ExchangesParams']['Exchange'] )
				&& !empty( $_POST['WTU_ExchangesParams']['ApiKey'] )
				&& !empty( $_POST['WTU_ExchangesParams']['SecretApiKey'] )
			) {
				$_POST['WTU_ExchangesParams']['UniqueRef'] = time();
				$clair = base64_encode(serialize($_POST['WTU_ExchangesParams']));			
				$crypt=base64_encode(f_crypt($UserKey,$clair));			
				add_user_meta( $user_id, 'WTU_ExchangesParams', $crypt, false );
			}
		}	
	}
}
add_action( 'personal_options_update', 'WTU_Account_Exchanges_Save' );
add_action( 'edit_user_profile_update', 'WTU_Account_Exchanges_Save' );

?>
