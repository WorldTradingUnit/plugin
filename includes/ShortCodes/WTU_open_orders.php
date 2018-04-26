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

function OpenOrders() {
	$Retour = "<div>";
	$user = get_userdata(get_current_user_id());
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
	
		$crypts = get_user_meta($user->ID, 'WTU_ExchangesParams',false);
		foreach($crypts as $KeyOfExchangesParams => $crypt)
		{
			$crypt = str_replace(' ','+',$crypt);
			$crypt = base64_decode($crypt);
			$clair = f_decrypt($UserKey,$crypt);
			$clair = str_replace(' ','+',$clair);
			$data = unserialize(base64_decode($clair));
			$WTU_ExchangesParams = $data;
			//$WTU_ExangesList[] = $WTU_ExchangesParams['Exchange'];

			//==============================================================
			//  Paramettrage de l'API de la plateforme choisie
			//==============================================================
			$plateforme =$WTU_ExchangesParams['Exchange'];
			if ($plateforme != "") {
				$exchange = '\\ccxt\\' . $plateforme;			
				$exchange = new $exchange ();
				if (	$WTU_ExchangesParams['Exchange'] != __('--Disabled--') 
					&& $WTU_ExchangesParams['ApiKey']!= __("Your Api Key") 
					&& $WTU_ExchangesParams['SecretApiKey'] != __("Your Secret Key")
					&& $WTU_ExchangesParams['SecretApiKey'] !== "" 
					&&  $WTU_ExchangesParams['SecretApiKey'] !=  __("Your Api Key") 
					&& in_array ($WTU_ExchangesParams['Exchange'], \ccxt\Exchange::$exchanges)
					&& $exchange->has['fetchOpenOrders']
				) {

					$exchange->apiKey = $WTU_ExchangesParams['ApiKey'];
					$exchange->secret = $WTU_ExchangesParams['SecretApiKey'];
					$exchange->verbose = false;
					$exchange->timeout = 30000;
					$exchange->options[warnOnFetchOpenOrdersWithoutSymbol] = false;



					//==============================================================
					// Page de la balance du compte utilisateur de la plateforme choisie
					//==============================================================	
					$Retour .= "<img alt=\"" . 	$plateforme . "\" src=\"" . WTU_PLUGIN_URL . "images/exchanges/" . $plateforme . ".jpg\">\n";
							
					//  Lecture de la balance sur l'API de la plateforme choisie
					$err = "";
					$erreur=false;
					$errmess = "";
						
					try	{
						$InputLines = $exchange->fetchOpenOrders ();
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
					If ($erreur) {
						$Retour .= "<font color=red> $err </font>";
					} else {
						$Retour .= "	<font color=green>". __(" API keys are correct") . "</font>";
						$Retour .= "	<table style=\"background: white; font-size: 10px; color: #666; text-align: center; box-sizing: border-box; line-height: 22px;\">\n";
						$Retour .= "		<tr style=\"border-radius: 0px; background: #c2c2c2; text_align: center; \">\n";
						$Retour .= "			<th style=\"text-align : center;\">". _('Date') 		. "</th><th style=\"text-align : center;\">" .__('Symbol') 	. "</th><th style=\"text-align : center;\">" . __('Type') 		. "</th><th style=\"text-align : center;\">" .__('Side')		. "</th><th style=\"text-align : center;\">" .__('Price')		. "</th><th style=\"text-align : center;\">" . __('Amount') 	. "</th><th style=\"text-align : center;\">" .__('Filled %') 	. "</th><th style=\"text-align : center;\">". __('Cost') 		. " </th><th style=\"text-align : center;\">". __('Trigger')	. "</th><th style=\"text-align : center;\">" .__('Cancel') 	. "</th>\n";
						$Retour .= "		</tr>\n";
						$tickers = $exchange->fetch_tickers();
						$TotalExchangeBTC = 0;
						foreach ($InputLines as $key => $InputLine) {
							$InputLine["asset"]=substr($InputLine["symbol"],strpos ($InputLine["symbol"],"/")+1);
							// la base de depart 1 BTC
							$price = 1;
							// si ce n'est pas du BTC
							if ($InputLine["asset"] != "BTC") {
								// ramenne le prix en BTC
								$price = $tickers[$InputLine["asset"] . "/BTC"]["last"];
								// si ce prix est 0
								if ($price == 0) {
									//recherche la paire inverse
									if (isset($tickers["BTC/" . $InputLine["asset"] ]["last"])){
										$price = 1 / $tickers["BTC/" . $InputLine["asset"] ]["last"];
									// si ce n'est toujours pas bon merde !
									} else {
										$price = 0;
									}
								}
							}
							if($InputLine["cost"] == 0){
								$InputLine["cost"] = $InputLine["price"] * $InputLine["amount"];
							}
							$amountBTC = $InputLine["cost"] * $price;
		/*
		echo "<pre>";
		print_r($InputLine);
		echo "<pre>";
		die();
		*/
							$TotalExchangeBTC += $amountBTC;

							$Retour .= "		<tr>\n";
							$Retour .= "			<td>" . str_replace("T"," ",substr ($InputLine["datetime"],0,19)) . " </td>\n";
							$Retour .= "			<td>" . $InputLine["symbol"] . " </td>\n";
							$Retour .= "			<td>" . $InputLine["type"] . " </td>\n";
							$Retour .= "			<td>" . $InputLine["side"] . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . number_format($InputLine["price"],2) . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . $InputLine["amount"]. " </td>\n";							
							$Retour .= "			<td>" . $InputLine["filled"] . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . number_format($InputLine["cost"],8) . " " . $InputLine["asset"]  . "</td>\n";
							if ($InputLine["type"] == "take_profit_limit") {
								if ($InputLine["side"] == "buy") {
									$Retour .= "			<td>&lt;= " . $InputLine[info]["stopPrice"] . "</td>\n";
								} else {
									$Retour .= "			<td>&gt;= " . $InputLine[info]["stopPrice"] . "</td>\n";
								}
							} else {
								$Retour .= "			<td>--</td>\n";
							}						
							$Retour .= "			<td>";
							$Retour .= "<form method=\"post\">";
							$Retour .= "<input type=\"hidden\" name=\"Plateforme\" value=\"$plateforme\">";
							$Retour .= "<input type=\"hidden\" name=\"ApiKey\" value=\"".$WTU_ExchangesParams['ApiKey']."\">";
							$Retour .= "<input type=\"hidden\" name=\"SecretApiKey\" value=\"".$WTU_ExchangesParams['SecretApiKey']."\">";
							$Retour .= "<input type=\"hidden\" name=\"Symbol\" value=\"" . $InputLine["symbol"]."\">";						
							$Retour .= "<input type=\"hidden\" name=\"UserID\" value=\"" . get_current_user_id() ."\">";
							$Retour .= "<input type=\"hidden\" name=\"UserApiKey\" value=\"" .$UserApiKey ."\">";
							$Retour .= "<input type=\"hidden\" name=\"CancelOrderId\" value=\"$InputLine[id]\">";
							$Retour .= "<input type=\"image\" src=\"".WTU_PLUGIN_URL."/images/cancel.png\" style=\"vertical-align: text-top; width: fit-content;\">";
							$Retour .= "</form></td>\n";
							$Retour .= "		</tr>\n";
						}
						//------------------------------
						// Total général
						//------------------------------
						$priceUSDT = $tickers["BTC/USDT"]["last"];
						$priceEUR = json_decode(file_get_contents("https://api.coinmarketcap.com/v1/ticker/bitcoin/?convert=EUR"),true)[0]["price_eur"];
						$Retour .= "		<tr>\n";
						$Retour .= "			<td colspan=10 style=\"text-align : right;\"><b>" . __("Estimated Value at") . " " . date("d/m/Y H:i:s") . "  : " ;
						$Retour .=  number_format($TotalExchangeBTC,8) . " BTC ";
						if ($priceUSDT >0) {
							$Retour .= " / " .number_format($TotalExchangeBTC * $priceUSDT,2) . " USDT ";
						} 
						if ($priceEUR >0) {
							$Retour .= " / " . number_format($TotalExchangeBTC * $priceEUR,2) . " EUR ";
						} 
						$Retour .=  "</b>";
						$Retour .=  "			</td>\n";
						$Retour .=  "		</tr>\n";
						//------------------------------

						$Retour .= "	</table>\n";
					}
					$Retour .= "	<hr>";
				}
			}	
			$Retour .= "</div><br>";
		}
		return $Retour;
	} else {
		return __("You must param your WTU Acount for this Action");
	}
}
?>