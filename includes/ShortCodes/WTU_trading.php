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

function Trading() {
/*
if ( !empty( $_POST)){
	WTU_Display_Array($_POST);
	die();
}
*/
//echo exec("crontab -l");
//die();
	//============================
	// Lecture cle API de l'utilisateur
	//============================
	$user = get_userdata(get_current_user_id());
	$UserApiKey = get_user_meta($user->ID, 'WTU_UserApiKey',True);	
	if ($UserApiKey != "") {
		//============================
		// Lecture Balance WTU et UserKey du compte client
		//============================
		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user->ID;
		$action['UserApiKey']= $UserApiKey;
		$reponse = WTU_Send($action,$user);
		$UserKey = $reponse['Userkey'];
		$Balance = $reponse['Balance'];
		//============================
		// save New Tradings crypted
		//============================
		if ( !empty( $_POST['WTU_NewTradings']) ) {
			$_POST['WTU_NewTradings']['UniqueRef'] = time();
			$TraderApiKey = $UserApiKey;
			$_POST['WTU_NewTradings']['TraderApiKey'] = $TraderApiKey;
			$reponse = WTU_add_user_meta( $user->ID, 'WTU_Tradings', json_encode($_POST['WTU_NewTradings']), false );
			//$reponse = WTU_add_user_meta( $user->ID, 'WTU_Tradings', 'test', false );
			$MetaId =  $reponse['MetaId'];
			WTU_Create_Orders($_POST['WTU_NewTradings'],$TraderApiKey,$MetaId);
		}
		
		$Retour = "<div id=\"D0\" style=\"display: none;\"><img src=\"".WTU_PLUGIN_URL."/images/WTU_wait.gif\" style=\"width: 100%;\"></div>";
		$Retour .= "<div id=\"D1\">";
		//==============================================================
		//  Saisie d'un nouveau trade
		//==============================================================	
		$Retour .= @"<script>
		function verify() {
			document.getElementById('D1').style.display='none';
			document.getElementById('D0').style.display='block';
		}
		</script>
		";
		$Retour .= "<form name=\"F0\" method=\"POST\" onsubmit=\"javascript:verify()\">\n";
		$Retour .= "<table id=\"T0\" style=\"margin-bottom: 0;width:100%;\">\n";
		$Retour .= '<tr><th colspan=5>' .  __("New Tradings") .'</th></tr>';
		$Retour .= @'<tr>
				<th style="width:20%">'. __("Name").'</th>
				<th style="width:20%">'. __("Symbol").'</th>
				<th style="width:20%">'. __("Buy").'</th>
				<th style="width:20%">'. __("Sell").'</th>
				<th style="width:20%">'. __("Action").'</th>
			</tr>';

		$Retour .= "<tr>\n";
		$Retour .= "<td style=\"width:20%\"><div><span>\n";
		$Retour .= "<select id=\"Exchange\" name=\"WTU_NewTradings[Exchange]\" onchange=\"javascript:DisplayMarket()\">";
		//$Retour .= "<option value='" .  __('--Disabled--') . "'>" .  __('Chose your exchange') . "</option>";
		$crypts = get_user_meta($user->ID, 'WTU_ExchangesParams',false);
		$Retour2 = "<script>\n listMarket=new Array();\n tikers=new Array();\n";
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
				$exchange->verbose = false;
				$exchange->timeout = 30000;

				// liste des assets en USDT de cette plateforme
				$ccxt = WTU_load_ccxt($plateforme);
				foreach ($ccxt[markets] as $k=>$reste) {
					if (strpos($k,"/USDT") !== false) {
						$markets[$k]=$ccxt[markets][$k];
						// prix
						$tickers[$k]=$exchange->fetch_ticker($k);
					}
				}
				ksort($markets);

				$Retour2 .="listMarket[\"$plateforme\"]=" . json_encode($markets).";\n";				
				$Retour2 .="tikers[\"$plateforme\"]=" . json_encode($tickers).";\n";				
				$sel="";
//				if ($WTU_Tradings['Exchange']==$Exchange) $sel = " selected";
				$Retour .= "<option value='$plateforme' $sel>$plateforme</option>\n"; 
			}
		}
		$Retour .= "</select>\n";
		$Retour .= "</span></div></td>\n";
		$Retour .= "<td style=\"width:20%\">\n";
		$Retour .= "<div id = 'SelectMarket'></div>\n";
		$Retour .= "</td>\n";
		$Retour .= "<td style=\"width:20%\">\n";
		$Retour .= "<div id = 'BuyPrice'></div>\n";
		$Retour .= "</td>\n";
		$Retour .= "<td style=\"width:20%\">\n";
		$Retour .= "<div id = 'SellPrice'></div>\n";
		$Retour .= "</td>\n";

		$Retour .= "<td style=\"width:20%\"><div><span><input type=\"submit\" name=\"S0\" value=\"".__("Create")."\" ></span></div></td>";
		$Retour .= "</tr>\n";
		$Retour .= "</table>\n";
		$Retour .= "</form>\n";
		$Retour .= "<script type=\"text/javascript\" src=\"https://s3.tradingview.com/tv.js\"></script>";
		$Retour .= "<div id='Chart'></div>";
		//============================
		// lecture et affichage des trades dèjà passés
		//============================
		$trades = WTU_get_user_meta($user->ID, 'WTU_Tradings',false);
		if (is_array($trades)) {
			$Retour .= @'
			<table style="margin-bottom: 0;width:100%;">
			<tr>
				<th colspan=5>' .  __("Recorded Tradings") .'</th>
			</tr>
			<tr>
				<th style="width:20%">'. __("Name").'</th>
				<th style="width:20%">'. __("Symbol").'</th>
				<th style="width:20%">'. __("Buy").'</th>
				<th style="width:20%">'. __("Sell").'</th>
				<th style="width:20%">'. __("Action").'</th>
			</tr>
			</table>';
			foreach($trades as $KeyOfTradings => $trade)
			{
				$oldvalue = $trade;
				$trade = str_replace(' ','+',$trade);
				$WTU_Tradings =json_decode($trade,true);
				$Display=true;
				//============================
				// Modify Tradings
				//============================
				if ( !empty( $_POST['WTU_Tradings']) && isset($_POST['WTU_Tradings'][$WTU_Tradings['UniqueRef']]) ) {
					if (!empty( $_POST['WTU_Tradings'][$WTU_Tradings['UniqueRef']]['BuyPrice'])) $WTU_Tradings['BuyPrice']=$_POST['WTU_Tradings'][$WTU_Tradings['UniqueRef']]['BuyPrice'];
					if (!empty( $_POST['WTU_Tradings'][$WTU_Tradings['UniqueRef']]['SellPrice'])) $WTU_Tradings['SellPrice']=$_POST['WTU_Tradings'][$WTU_Tradings['UniqueRef']]['SellPrice'];
					if(!empty( $_POST['WTU_Tradings']['Modify'])){
						WTU_update_user_meta( $user->ID, 'WTU_Tradings', json_encode($WTU_Tradings), $oldvalue );
					} elseif (!empty( $_POST['WTU_Tradings']['Delete'])){
						$Display=false;
						WTU_delete_user_meta( $user->ID, 'WTU_Tradings', json_encode($WTU_Tradings), $oldvalue );
					}
				}			
				if ($Display) {
					$i++;
					$Retour .= "<form method=\"POST\" name=\"F$i\" onsubmit=\"javascript:verify()\">\n";
					$Retour .=  "<table style=\"margin-bottom: 0;width:100%;\">";
					$Retour .=  "<tr>";			
					$Retour .= "<td style=\"width:20%\"><div><span>".$WTU_Tradings['Exchange']."<br><font style=\"font-size:xx-small;\">".date("d/m/Y H:i:s",$WTU_Tradings['UniqueRef'])."</font></span></div></td>\n";
					$Retour .=  "<td style=\"width:20%\"><div><span>" . $WTU_Tradings['Market'] . "</span></div></td>\n";
					$modif=0;
					if (isset($WTU_Tradings['BPDate'])) {
						$Retour .=  "<td style=\"width:20%\"><div><span>" . $WTU_Tradings['BuyPrice'] . "<br><font style=\"font-size:xx-small;\">".date("d/m/Y H:i:s",$WTU_Tradings['BPDate'])."</font></span></div></td>\n";
					} else {
						$modif++;
						$Retour .=  "<td style=\"width:20%\"><div><span><input type=\"text\" name=\"WTU_Tradings[" . $WTU_Tradings['UniqueRef'] . "][BuyPrice]\" value=\"". $WTU_Tradings['BuyPrice'] . "\"></span></div></td>\n";
					}
					if (isset($WTU_Tradings['SPDate'])) {
						$Retour .=  "<td style=\"width:20%\"><div><span>" . $WTU_Tradings['SellPrice'] . "<br><font style=\"font-size:xx-small;\">".date("d/m/Y H:i:s",$WTU_Tradings['SPDate'])."</font></span></div></td>\n";
					} else {
						$modif++;
						$Retour .=  "<td style=\"width:20%\"><div><span><input type=\"text\" name=\"WTU_Tradings[" . $WTU_Tradings['UniqueRef'] . "][SellPrice]\" value=\"". $WTU_Tradings['SellPrice'] . "\"></span></div></td>\n";
					}
					$Retour .= "<td style=\"width:20%\"><div><span>";
					if ($modif==2) {
						$Retour .= "<input type=\"submit\" name=\"WTU_Tradings[Modify]\" value=\"".__("Modify")."\">";
						$Retour .= "<input type=\"submit\" name=\"WTU_Tradings[Delete]\" value=\"".__("Delete")."\">";
					}elseif ($modif==1) {
						$Retour .= "<input type=\"submit\" name=\"Modify\" value=\"".__("Modify")."\">";
					}else {
						$Retour .= "&nbsp;";				
					}
					$Retour .= "</span></div></td>";
					$Retour .= "</tr>\n</table>\n</form>\n";
				}
			}
		}
		//============================
		// Lecture des Exchanges paramettrés
		//============================

		$Retour .= $Retour2;
		$Retour .= @"
function DisplayMarket() {
	try {
		var e = document.getElementById('Exchange');
		var Exchange = e.options[e.selectedIndex].text;
		var conteneur = document.getElementById('SelectMarket');
		conteneur.innerHTML = '';
		var ligne = document.createElement('span');
		var selection = document.createElement('select');
		selection.setAttribute('id','Markets');
		selection.setAttribute('name','WTU_NewTradings[Market]');
		selection.setAttribute('style','width:100%;');
		selection.setAttribute('onchange','javascript:DisplayPrice();');
		for(var Market in listMarket[Exchange]) {
			var element = document.createElement('option');
			element.setAttribute('value',Market);
			var text = document.createTextNode(Market); 
			element.appendChild(text);    
			selection.appendChild(element);
			ligne.appendChild(selection);
			conteneur.appendChild(ligne);
		}
	}
	catch(e) {
		alert(e);
	}
}
function DisplayPrice() {
	try {
		var e = document.getElementById('Exchange');
		var Exchange = e.options[e.selectedIndex].text;	
		var m = document.getElementById('Markets');
		var Market = m.options[m.selectedIndex].text;

		var conteneurBP = document.getElementById('BuyPrice');
		conteneurBP.innerHTML = '';
		var ligneBP = document.createElement('span');
		var inputBP = document.createElement('input');
		inputBP.setAttribute('name','WTU_NewTradings[BuyPrice]');
		inputBP.setAttribute('style','width:100%;');	
		var price = roundDecimal(tikers[Exchange][Market]['last'],listMarket[Exchange][Market]['precision']['price']);
		var decimales = listMarket[Exchange][Market]['precision']['price'];
		inputBP.setAttribute('value',parseFloat(price).toFixed(decimales));
		var text = document.createTextNode('BuyPrice'); 
		inputBP.appendChild(text);    
		ligneBP.appendChild(inputBP);
		conteneurBP.appendChild(ligneBP);

		var conteneurSP = document.getElementById('SellPrice');
		conteneurSP.innerHTML = '';
		var ligneSP = document.createElement('span');
		var inputSP = document.createElement('input');
		inputSP.setAttribute('name','WTU_NewTradings[SellPrice]');
		inputSP.setAttribute('value',parseFloat(price*1.01).toFixed(decimales));
		inputSP.setAttribute('style','width:100%;');
		var text = document.createTextNode('SellPrice'); 
		inputSP.appendChild(text);    
		ligneSP.appendChild(inputSP);
		conteneurSP.appendChild(ligneSP);

		var conteneurChart = document.getElementById('Chart');
		var Symbol = listMarket[Exchange][Market]['info']['symbol'];
		conteneurChart.innerHTML = WTU_chart(Exchange.toUpperCase(),listMarket[Exchange][Market]['info']['symbol'].toUpperCase());
		new TradingView.widget(
			{
				'width': 980,
				'height': 610,
				'symbol': Exchange.toUpperCase()+':'+Symbol.toUpperCase(),
				'interval': '60',
				'timezone': 'Etc/UTC',
				'theme': 'Light',
				'style': '1',
				'locale': 'fr',
				'toolbar_bg': '#f1f3f6',
				'enable_publishing': false,
				'withdateranges': true,
				'hide_side_toolbar': true,
				'allow_symbol_change': true,
				'details': true,
				'hideideas': true,
				'studies': [
					'BB@tv-basicstudies',
					'PSAR@tv-basicstudies'
				],
				'show_popup_button': false,
				'popup_width': '1000',
				'popup_height': '650',
				'referral_id': '8874',
				'container_id': 'tradingview_16b45'
			}
		);
console.log(listMarket[Exchange][Market]);
console.log(tikers[Exchange][Market]);
console.log(listMarket[Exchange][Market]['precision']['price']);
	}
	catch(e) {
		alert(e);
	}
}
// arrondi
function roundDecimal(nombre, precision){
    var precision = precision || 2;
    var tmp = Math.pow(10, precision);
    return Math.round( nombre*tmp )/tmp;
}
function WTU_chart(market,symbol) {
	var retour = \"<!-- TradingView Widget BEGIN -->\";
	retour += \"<div class=\\\"tradingview-widget-container\\\">\";
	retour += \"<div id=\\\"tradingview_16b45\\\"></div>\";
	retour += \"<div class=\\\"tradingview-widget-copyright\\\">\";
	retour += \"<a href=\\\"https://www.tradingview.com/symbols/\"+market+\"-\"+symbol+\"/\\\" rel=\\\"noopener\\\" target=\\\"_blank\\\">\";
	retour += \"<span class=\\\"blue-text\\\">\"+symbol+\"</span> \";
	retour += \"<span class=\\\"blue-text\\\">chart</span> by TradingView</a></div>\";
	retour += \"</div>\";
	retour += \"<!-- TradingView Widget END -->\";
	return retour;
}
DisplayMarket();
DisplayPrice();
</script>
</div>
";
		return $Retour;
	} else {
		return __("You must param your WTU Acount for this Action");
	}
}

function WTU_Create_Orders($Order,$TraderApiKey,$OrderId) {

	//foreach (pour tout les utilisateurs qui ont choisi ce trader en auto) {
		// if (max trade ok max capital ok) {
$ThisUserID=get_current_user_id();
			$UserApiKey = get_user_meta($ThisUserID, 'WTU_UserApiKey',True);	
			if ($UserApiKey != "") {
				//============================
				// Lecture Balance WTU et UserKey du compte client
				//============================
				$action['Action']= "LoadUserAccount";
				$action['UserId']=$ThisUserID;					
				$action['UserApiKey']= $UserApiKey;
				$reponse = WTU_Send($action,$user);
				$UserKey = $reponse['Userkey'];
				$Balance = $reponse['Balance'];

				//Lecture cle API de la plateforme
				$crypts = get_user_meta($ThisUserID, 'WTU_ExchangesParams',false);
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
					if ($plateforme == $Order['Exchange']) {
						$action['Action']= "CreateUserOrder";
						$action['UserId']=$ThisUserID;					
						$action['UserApiKey']= $UserApiKey;
						$action['TraderApiKey'] = $TraderApiKey;					
						$action['Exchange'] = $plateforme;
						$action['Symbol'] = $Order['Market'];
						$action['Quantity'] = .1; // calculer ici le % du capital
						$action['BuyPrice'] = $Order['BuyPrice'];
						$action['SellPrice'] = $Order['SellPrice'];
						$reponse = WTU_Send($action,$user);
					}
				}
			}
		//}
	//}
}
/*
					
					
					
					$exchange = '\\ccxt\\' . $plateforme;			
					$exchange = new $exchange ();
					$exchange->apiKey = $WTU_ExchangesParams['ApiKey'];
					$exchange->secret = $WTU_ExchangesParams['SecretApiKey'];
					$exchange->verbose = false;
					$exchange->timeout = 30000;
					$exchange->adjustForTimeDifference = True;

					//==============================================================
					// Mise en place de l'ordre limit
					//==============================================================	
					$err = "";
					$erreur=false;
					$errmess = "";						
					$symbol = $Order['Market'];
					$type = "limit";
					$side = "buy";
					$amount = .1; // % du capital ...
					$Buyprice = $Order['BuyPrice'];				
					
					try	{
						$Result = $exchange->create_order ($symbol, $type, $side, $amount, $Buyprice);
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
					}
					// ============================
					// ecriture des ordres passés sur cette plateforme dans la BDD
					// ============================
					
					//WTU_Display_Array($Result);
					$meta=json_encode(array('TraderApiKey'=>$TraderApiKey,'TraderOrderId'=>$OrderId,$Result));
					//WTU_add_user_meta( $ThisUserID,'WTU_ORDERS', $meta,false);

*/

?>
