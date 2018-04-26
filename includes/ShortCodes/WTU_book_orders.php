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

function BookOrders() {
	$symbol="BTC/USDT";
	if(isset($_GET['symbol'])) $symbol=$_GET['symbol'];
	$Retour = "<div>";
	$user = get_userdata(get_current_user_id());
	// —————————————–
	// Lecture BookOrders WTU et UserKey du compte client
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
					&& $exchange->has['fetchOrders']
				) {

					$exchange->apiKey = $WTU_ExchangesParams['ApiKey'];
					$exchange->secret = $WTU_ExchangesParams['SecretApiKey'];
					$exchange->verbose = false;
					$exchange->timeout = 30000;
					// liste des assets de cette plateforme
					$ccxt = WTU_load_ccxt($plateforme);
					$markets =  $ccxt[markets];
					$Retour .= "<form name=\"choiceofsymbol\">";
					$Retour .= "\t\t<select name=\"symbol\" onchange=\"javascript:document.choiceofsymbol.submit()\">\n";
					$Retour .= "\t\t\t<option value=\"Vide\">" . __('Chose your symbol') . "</option>\n";
					foreach ($markets as $key => $value) {
						// initialiser la variable $symbol à vide si on n'a pas validé le formulaire.
						$selected =  ($symbol === $key) ? ' selected="selected"' : "";
						$Retour .= "\t\t\t<option value=\"". $key."\"$selected>$key</option>\n";
					}
					$Retour .= "\t\t</select></form><br><br><br>\n";

					//==============================================================
					// Page de la balance du compte utilisateur de la plateforme choisie
					//==============================================================	
					$Retour .= "<img alt=\"" . 	$plateforme . "\" src=\"" . WTU_PLUGIN_URL . "images/exchanges/" . $plateforme . ".jpg\">\n";
							
					//  Lecture de la balance sur l'API de la plateforme choisie
					$err = "";
					$erreur=false;
					$errmess = "";
					//$symbol="BTC/USDT";
					//$delay = 1000000; // microseconds = seconds * 1000000
					//foreach ($exchange->markets as $symbol => $market) {
						try	{
			//				$InputLines = $exchange->fetchOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) ;
							$InputLines = $exchange->fetchOrders($symbol);
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
							$Retour .= "			<th style=\"text-align : center;\">" .__('Date') 		. "<th style=\"text-align : center;\">" .__('Symbol') 	. "</th><th style=\"text-align : center;\">" .__('Type') 		. "</th><th style=\"text-align : center;\">" .__('Side')		. "</th><th style=\"text-align : center;\">" .__('Price')		. "</th><th style=\"text-align : center;\">" .__('Amount') 	. "</th><th style=\"text-align : center;\">" .__('Cost USDT') 	. "</th><th style=\"text-align : center;\">" .__('Filled %') 	. "</th><th style=\"text-align : center;\">". __('Remaining') 		. "</th><th style=\"text-align : center;\">". __('Status')	. "</th>\n"; //__('Fee') 	. "</th>\n";
							$Retour .= "		</tr>\n";
			//				$tickers = $exchange->fetch_tickers();
			//				$TotalExchangeBTC = 0;
							foreach ($InputLines as $key => $InputLine) {				
			/*
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
										$price = 1 / $tickers["BTC/" . $InputLine["asset"] ]["last"];
										// si ce n'est toujours pas bon merde !
										if ($price === INF) {
											$price = 0;
										}
									}
								}
								$amountBTC = $InputLine["cost"] * $price;
								$TotalExchangeBTC += $amountBTC;
			*/
							if ($InputLine["status"]=="canceled") {
								$Retour .= "		<tr style=\"background-color: MistyRose \">\n";
								$status = __("Canceled");
							} else {
								$status = __("Closed");					
								$Retour .= "		<tr>\n";
							}
							$Retour .= "			<td>" . str_replace("T"," ",substr ($InputLine["datetime"],0,19)) . " </td>\n";
							$Retour .= "			<td>" . $InputLine["symbol"] . " </td>\n";
							$Retour .= "			<td>" . $InputLine["type"] . " </td>\n";
							if ($InputLine["side"] == "buy") {
								$side = "<font color=green>" . __("buy") . "</font>";
							} else {
								$side = "<font color=red>" . __("sell") . "</font>";
							}
							$Retour .= "			<td>" . $side . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . $InputLine["price"] . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . $InputLine["amount"] . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . $InputLine["cost"]  . " </td>\n";
							$Retour .= "			<td>" . $InputLine["filled"] . " </td>\n";
							$Retour .= "			<td style=\"text-align : right;\">" . $InputLine["remaining"] . " </td>\n";
							$Retour .= "			<td>" . $status . " </td>\n";
							//$Retour .= "			<td>" . $InputLine["fee"] . " </td>\n";
							//$Retour .= "			<td>" . $InputLine["id"] . " </td>\n";
							$Retour .= "		</tr>\n";
							}
		/*
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
		*/
						}				
		//				usleep ($delay); // rate limit
		//			}			
					$Retour .= "	</table>\n";
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