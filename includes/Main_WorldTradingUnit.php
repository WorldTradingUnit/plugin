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

function WorldTradingUnit_init() {
	$tw = new WTU_Lib;
}
add_action('plugins_loaded','WorldTradingUnit_init');

//===============================================
// crypter en RSA et envoie la demande et reçois la réponse
//===============================================
function f_crypt($UserKey,$decrypted) {
	$aes = new Crypt_AES();
	$aes->setKey($UserKey);
	$encrypted =  $aes->encrypt($decrypted);
	return $encrypted;
}
function f_decrypt($UserKey,$encrypted) {
	$aes = new Crypt_AES();
	$aes->setKey($UserKey);
	$decrypted =  $aes->decrypt($encrypted);
	return $decrypted;
}
function WTU_Send($action,$user) {
	$action['REMOTE_HOST']=$_SERVER['HTTP_HOST'];
	$PKEY['timestamp'] = 0;
	$PKEY['publickey']='';
	// ne pas confondre encrypter/decrypter avec encoder/décoder
	$PKEY = get_user_meta($user->ID, 'publickey',true);
	$PKEY = str_replace(' ','+',$PKEY);
	$PKEY = json_decode($PKEY,true);
	$vieux= time() - 300;
	if (!is_array($PKEY) || strval($PKEY['timestamp']) < $vieux) {
		//-----------------------------------
		// demande de la cle public si plus vieux de 5 Mn
		//-----------------------------------
		$url = WTU_Dialog. "?Action=Sendpublickey";
		$json = file_get_contents($url);
		// traitement de la reponse
		$raw = json_decode($json,true);
		$publickey = str_replace(' ','+',$raw['publickey']);
		$publickey = base64_decode($publickey);
		$PKEY['timestamp'] = time();
		$PKEY['publickey'] = $raw['publickey'];
		$JSON_PKEY = json_encode($PKEY);
		update_user_meta( $user->ID, 'publickey',  $JSON_PKEY);
	} else {
		$publickey = str_replace(' ','+',$PKEY['publickey']);
		$publickey = base64_decode($publickey);
	}
	if (isset($PKEY['publickey'])) {
		//-----------------------------------
		// on encode le tableau de demande
		//-----------------------------------
		$data = json_encode($action);
		// Encrypt the data to $encrypted using the public key
		$rsa = new Crypt_RSA();
		$rsa->loadKey($publickey);
		$encrypted =  $rsa->encrypt($data);
		// on encode en base64
		$encrypted = base64_encode($encrypted);

		// on fait la demande d'action avec public key non decodé
		$url = WTU_Dialog. "?Data=$encrypted&publickey=" . $PKEY['publickey'];
		//-----------------------------------
		// on decode la réponse de base64
		//-----------------------------------
		$encrypted=file_get_contents($url);
		$encrypted = str_replace(' ','+',$encrypted);
		$encrypted = base64_decode($encrypted);
		// decrypte la réponse
		$decrypted =  $rsa->decrypt($encrypted);
		// decode la réponse en json
		$raw = json_decode($decrypted,true);
	}
	return $raw;
}
	
/**
 * Hide Profile link if user is not logged in
 *
 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
 *
 * @see wp_setup_nav_menu_item()
 * @since 6.4
 * @access public
 *
 * @param object $menu_item The menu item
 * @return object The (possibly) modified menu item
 */
// cache les menus pour les pages dont on a creer un "Champ personalisé" : "WTU-hide-not-logged" sur "yes"
function wp_setup_nav_menu_WTU_item( $menu_item ) {
	if ( is_admin() )
		return $menu_item;

	if ( 'page' != $menu_item->object )
		return $menu_item;

	// User is not logged in
	if ( ! is_user_logged_in() ) {
		// Hide Profile
		if (strtoupper(get_post_meta( $menu_item->object_id,'WTU-hide-not-logged',true )) === 'YES' ) {
			$menu_item->_invalid = true;
		}
	}
	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'wp_setup_nav_menu_WTU_item', 12 );


//===================================================
// Suppression d'un ordre ouvert / Cancel Open Order
//===================================================
if (isset($_POST['CancelOrderId']) && isset($_POST['Plateforme']) && isset($_POST['UserApiKey'])){
	$UserApiKey = get_user_meta($user->ID, 'WTU_UserApiKey',True);
	//echo "Cancel  Order: " . $_POST['CancelOrderId'] .  "  Sur : " . $_POST['Plateforme'] . " A=" . $_POST['ApiKey'] . " B=" . $_POST['SecretApiKey'];
	$exchange = '\\ccxt\\' . $_POST['Plateforme'];			
	$exchange = new $exchange ();
	$exchange->apiKey = $_POST['ApiKey'];
	$exchange->secret = $_POST['SecretApiKey'];
	$exchange->verbose = false;
	$exchange->timeout = 30000;
	$exchange->options[warnOnFetchOpenOrdersWithoutSymbol] = false;

	try	{
		$InputLines = $exchange->cancel_order($_POST['CancelOrderId'],$_POST['Symbol']);
		//$InputLines = $exchange->fetch_order($_POST['CancelOrderId'],$_POST['Symbol'] );
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
	$action['UserId']= $_POST['UserID'];
	$action['UserApiKey']= $_POST['UserApiKey'];
	$action['Action']= "WriteLog";
	If ($erreur) {
		$action['Resu']= "KO";
		$action['Param']= $err . " POST:" . json_encode($_POST);
	} else {		
		$action['Resu']= "OK";
		$action['Param']= json_encode($_POST);
	}
	$reponse = WTU_Send($action,$user);
//die();	
}


//====================================
// lecture et mise en cache des info CCXT
//====================================
function WTU_load_ccxt($plateforme) {
	if ($plateforme != "") {
		$exchange = '\\ccxt\\' . $plateforme;			
		$exchange = new $exchange ();
		if (	$plateforme != __('--Disabled--') 
			&& in_array ($plateforme, \ccxt\Exchange::$exchanges)
		) {
			$exchange->verbose = false;
			$exchange->timeout = 30000;
			$exchange->options[warnOnFetchOpenOrdersWithoutSymbol] = false;
			$CCXT_cache['timestamp'] = 0;
			// ne pas confondre encrypter/decrypter avec encoder/décoder
			$CCXT_cache = get_option( 'WTU_CCXT_'.$plateforme );
			$CCXT_cache = str_replace(' ','+',$CCXT_cache);
			$CCXT_cache = json_decode($CCXT_cache,true);
			$vieux= time() - 7200;
			if (!is_array($CCXT_cache) || strval($CCXT_cache['timestamp']) < $vieux) {
					$CCXT_cache['timestamp'] = time();
					$ccxt[markets] = $exchange->load_markets ();
					$CCXT_cache[markets] = $ccxt[markets];
					
					update_option('WTU_CCXT_'.$plateforme,json_encode($CCXT_cache),false);
			} else {
				$ccxt[markets] = $CCXT_cache[markets];
			}
		}
	}
	return $ccxt;
}
function WTU_get_user_meta($user_id, $meta_key, $single=false) {
	$user = get_userdata($user_id);
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		//============================
		// Lecture Balance WTU et UserKey du compte client
		//============================
		$action['Action']= "GetMeta";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$action['MetaKey']= $meta_key;
		$user = get_userdata($user_id);
		$reponse = WTU_Send($action,$user);
		return $reponse['MetaValue'];
	}
}
function WTU_update_user_meta( $user_id, $meta_key, $meta_value, $prev_value ){
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		//============================
		// Lecture Balance WTU et UserKey du compte client
		//============================
		$action['Action']= "UpdateMeta";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$action['MetaKey']= $meta_key;
		$action['MetaValue']= $meta_value;
		$action['PrevValue']= $prev_value;
		$user = get_userdata($user_id);
		$reponse = WTU_Send($action,$user);
		//$UserKey = $reponse['Userkey'];
		//$Balance = $reponse['Balance'];
		//$MetaID =  $reponse['MetaID'];
		return $reponse;
	}
}
function WTU_add_user_meta( $user_id, $meta_key, $meta_value, $unique=false ){
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		//============================
		// Lecture Balance WTU et UserKey du compte client
		//============================
		$action['Action']= "AddMeta";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$action['MetaKey']= $meta_key;
		$action['MetaValue']= $meta_value;
		$user = get_userdata($user_id);
		$reponse = WTU_Send($action,$user);
		return $reponse;
	}
}
function WTU_delete_user_meta( $user_id, $meta_key, $meta_value, $prev_value ){
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		//============================
		// Lecture Balance WTU et UserKey du compte client
		//============================
		$action['Action']= "DeleteMeta";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$action['MetaKey']= $meta_key;
		$action['MetaValue']= $meta_value;
		$action['PrevValue']= $prev_value;
		$user = get_userdata($user_id);
		$reponse = WTU_Send($action,$user);
		//$UserKey = $reponse['Userkey'];
		//$Balance = $reponse['Balance'];
		//$MetaID =  $reponse['MetaID'];
		return $reponse;
	}
}
function WTU_Display_Array($A) {
	echo "<hr><pre>";
	print_r($A);
	echo "</pre><hr>";
}
?>