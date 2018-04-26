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
//************************************************************************************
// * Paramettrage pour le BOT et les WTU (World Trading Unit)
//************************************************************************************
// —————————————–
// Create Form for WTU Fields
// —————————————–

function WTU_Account_Trader_Add( $user ) {
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
//====================================================
// Gestion des traders
//====================================================	
			//   liste des tradeurs 
			global $wpdb;
			$TradersList = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}usermeta WHERE meta_key = 'WTU_Trader' AND meta_value='true'", ARRAY_A );
?>
		<table class="form-table" style="table-layout:initial;">
			<tr>
				<th colspan=7>
					<? _e("Your Traders");?>
				</th>
			</tr>
			<tr>
				<th style="text-align : center; width : 30%;"><?php _e("Trader") ?></th>
				<th style="text-align : center; width : 25%;"><?php _e("Exchange") ?></th>
				<th style="text-align : center; width : 20%;"><?php echo __("Capital") . " USDT" ?></th>
				<th style="text-align : center; width : 6%;"><?php _e("Max trade") ?></th>
				<th style="text-align : center; width : 11%;"><?php _e("%") ?></th>
				<th style="text-align : center; width : 4%;"><?php _e("Auto ?") ?></th>
				<th style="text-align : center; width : 4%;"><?php _e("Del ?") ?></th>
			 </tr>
<?php
			$default	= array(	'Trader' =>  __('--Disabled--'),
							'Exchange' => __('--Disabled--'),
							'Capital' => 0,
							'MaxTrade' => 0,
							'Percent' => 0,
			);
			$crypts = get_user_meta($user->ID, 'WTU_TradersParams',false);
			unset($CapitalTotal);
			foreach($crypts as $KeyOfTradersParams => $crypt)
			{
				$crypts = str_replace(' ','+',$crypt);
				$crypt = base64_decode($crypt);
				$clair = f_decrypt($UserKey,$crypt);
				$clair = str_replace(' ','+',$clair);
				$data = unserialize(base64_decode($clair));
				$WTU_TraderParams = wp_parse_args( $data, $default );
				$CapitalTotal += $WTU_TraderParams['Capital'] ;
				if ($CapitalTotal > WTU_Max_Capital) {
					echo "\t\t\t<tr style='background-color: red;'>"; 
				} else {
					if(strval($WTU_TraderParams['MaxTrade'] * $WTU_TraderParams['Percent']) >100) {
						echo "\t\t\t<tr style='background-color: pink;'>"; 
					} else {
						echo "\t\t\t<tr>";
					}
				}
?>
				<td>					
					<select id="Exchange" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Trader]">
<?php
					foreach ( $TradersList as $result ) {
						$trader = get_user_meta($result[user_id], 'nickname',true);
						$sel="";
						if ($WTU_TraderParams['Trader']==$trader) $sel = " selected";
						echo "<option value='$trader'$sel>$trader</option>";
					}
?>
					</select>
				</td>
				<td>
					<select id="Exchange" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Exchange]">
<?php
					//   Liste les plateformes d'Ã©change
					// Lecture des Exchanges paramettrés
						$crypts = get_user_meta($user->ID, 'WTU_ExchangesParams',false);
						if (is_array($crypts)){
							foreach($crypts as $KeyOfExchangesParams => $crypt)
							{
								$crypt = str_replace(' ','+',$crypt);
								$crypt = base64_decode($crypt);
								$clair = f_decrypt($UserKey,$crypt);
								$clair = str_replace(' ','+',$clair);
								$data = unserialize(base64_decode($clair));
								$Exchange = $data['Exchange'];
								
								$sel="";
								if ($WTU_TraderParams['Exchange']==$Exchange) $sel = " selected";

								echo "<option value='$Exchange' $sel>$Exchange</option>"; 
							 }
						}
?>
					</select>
				</td>
				<td>
					<input type="number" step="100" id="Capital" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Capital]" value="<?php echo $WTU_TraderParams['Capital']; ?>">
				</td>
				<td>
					<input type="number" step="1" min="1" max="100" id="MaxTrade" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][MaxTrade]" value="<?php echo $WTU_TraderParams['MaxTrade']; ?>">
				</td>
				<td>
					<input type="number" step="1" min="5" max="100" id="Percent" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Percent]" value="<?php echo $WTU_TraderParams['Percent']; ?>">
				</td>

				<?php 
				$chk = ""; 
				if ($WTU_TraderParams['Auto'] == "on") $chk=" checked"; 
				?>
				<td style="text-align : center;">
					<input type="checkbox" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Auto]" <?php echo $chk;?> >
				</td>
				<td style="text-align : center;">
					<input type="checkbox" name="WTU_TradersParams[<?php echo $WTU_TraderParams['UniqueRef'];?>][Delete]">
				</td>
			</tr>
<?php
			}
			
			//================================================
			// ADD NEW TRADER
			//===============================================
			if ($CapitalTotal < WTU_Max_Capital ) {
?>
			 <tr>
				<td>
					
								<select id="Exchange" name="WTU_TradersParams[Trader]">
									<option value="<?php  _e('--Disabled--');?>"><?php _e('--Disabled--');?></option>";
<?php
							foreach ( $TradersList as $result ) {
								$trader = get_user_meta($result[user_id], 'nickname',true);
								echo "<option value='$trader'>$trader</option>";
							}
?>
					</select>
				</td>
				<td>
					<select id="Exchange" name="WTU_TradersParams[Exchange]">
						<option value="<?php  _e('--Disabled--');?>"><?php _e('--Disabled--');?></option>";
<?php
					//   Liste les plateformes d'Ã©change
					// Lecture des Exchanges paramettrés
						$crypts = get_user_meta($user->ID, 'WTU_ExchangesParams',false);
						if (is_array($crypts)){
							foreach($crypts as $KeyOfExchangesParams => $crypt)
							{
								$crypt = str_replace(' ','+',$crypt);
								$crypt = base64_decode($crypt);
								$clair = f_decrypt($UserKey,$crypt);
								$clair = str_replace(' ','+',$clair);
								$data = unserialize(base64_decode($clair));
								$Exchange = $data['Exchange'];
								echo "<option value='$Exchange'>$Exchange</option>"; 
							 }
						}
?>
					</select>
				</td>
				<td>
					<input type="number" step="100" 
					<?php 
						if (WTU_Max_Capital > $CapitalTotal) {
							echo "max='" . strval(WTU_Max_Capital - $CapitalTotal) . "'  min='100'";
						} else {
							echo "max='0'  min='0'";
						}
					?>
						id="Capital" name="WTU_TradersParams[Capital]" value="200">
				</td>
				<td>
					<input type="number" step="1" min="1" max="100" id="MaxTrade" name="WTU_TradersParams[MaxTrade]" value="1">
				</td>
				<td>
					<input type="number" step="1" min="5" max="100" id="Percent" name="WTU_TradersParams[Percent]" value="5">
				</td>
				<td style="text-align : center;">
					<input type="checkbox" name="WTU_TradersParams[Auto]" checked >
				</td>
				<td style="text-align : center;">
					&nbsp;
				</td>

			</tr>
<?php
			} else {
?>
			<tr>
				<td colspan=6><font color=red><?php echo __('Capital Limit') . " " . WTU_Max_Capital . " USDT";?></font></td>
			</tr>
<?php
			}
		}
?>
		</table>
<?php
	}
}
add_action( 'show_user_profile', 'WTU_Account_Trader_Add' );
add_action( 'edit_user_profile', 'WTU_Account_Trader_Add' );

// —————————————–
// Save additional profile fields.
// —————————————–
function WTU_Account_Trader_Save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}
	// —————————————–
	// Lecture Balance WTU et UserKey du compte client
	// —————————————–
	$UserApiKey = get_user_meta($user_id, 'WTU_UserApiKey',True);
	$user = get_userdata($user_id);
	if ($UserApiKey != "") {	
		$action['Action']= "LoadUserAccount";
		$action['UserId']= $user_id;
		$action['UserApiKey']= $UserApiKey;
		$reponse = WTU_Send($action,$user);
		$UserKey = $reponse['Userkey'];
		$Balance = $reponse['Balance'];
	//==================================================
	// Traders parameters
	//==================================================
		// Delete Traders marked 'WTU_EraseTrader' and clean database
		if (is_array($_POST['WTU_TradersParams'])) {
			$crypts = get_user_meta($user_id, 'WTU_TradersParams',false);
			unset($CapitalTotal);
			foreach($crypts as $KeyOfTradersParams => $crypt)
			{
				$decode = str_replace(' ','+',$crypt);
				$decode = base64_decode($decode);
				$clair = f_decrypt($UserKey,$decode);
				$clair = str_replace(' ','+',$clair);
				$data = unserialize(base64_decode($clair));
				$CapitalTotal += $data['Capital'] ;
				if(	    $_POST['WTU_TradersParams'][$data['UniqueRef']]['Delete'] == "on"
					|| $data['Trader'] == __('--Disabled--') 
					|| $data['Exchange'] == __('--Disabled--') 
					|| empty( $data['Trader'] )
					|| empty( $data['Capital'] ) || $data['Capital'] < 200
					|| empty( $data['MaxTrade'] ) || $data['MaxTrade'] <1
					|| empty( $data['Percent'] ) || $data['Percent'] <5 || $data['Percent'] > 100
//					|| $data['Percent'] * $data['MaxTrade'] > 100
//					|| $CapitalTotal > WTU_Max_Capital
					) {
						delete_user_meta( $user_id, 'WTU_TradersParams', $crypt );
				} else {
					$_POST['WTU_TradersParams'][$data['UniqueRef']]['UniqueRef'] = $data['UniqueRef'];

					$clair = base64_encode(serialize($_POST['WTU_TradersParams'][$data['UniqueRef']]));			
					$newcrypt=base64_encode(f_crypt($UserKey,$clair));			
					update_user_meta( $user_id, 'WTU_TradersParams', $newcrypt, $crypt );
				}
			}
		}
		// save New Traders params crypted
		if ( !empty( $_POST['WTU_TradersParams']['Trader'] ) ) {
			$futurcapital = $CapitalTotal + strval($_POST['WTU_TradersParams']['Capital']);
			if (
				     $_POST['WTU_TradersParams']['Trader'] != __('--Disabled--')
				&& $_POST['WTU_TradersParams']['Exchange'] != __('--Disabled--')
				&& !empty( $_POST['WTU_TradersParams']['Trader'] )
				&& !empty( $_POST['WTU_TradersParams']['Capital'] ) && $_POST['WTU_TradersParams']['Capital'] >= 200
				&& !empty( $_POST['WTU_TradersParams']['MaxTrade'] ) && $_POST['WTU_TradersParams']['MaxTrade'] >= 1
				&& !empty( $_POST['WTU_TradersParams']['Percent'] ) && $_POST['WTU_TradersParams']['Percent'] >= 5 && $_POST['WTU_TradersParams']['Percent'] <= 100
				&& $_POST['WTU_TradersParams']['Percent'] * $_POST['WTU_TradersParams']['MaxTrade'] <= 100
				&& $futurcapital <= WTU_Max_Capital
			) {
				$_POST['WTU_TradersParams']['UniqueRef'] = time();
				$clair = base64_encode(serialize($_POST['WTU_TradersParams']));			
				$crypt=base64_encode(f_crypt($UserKey,$clair));			
				add_user_meta( $user_id, 'WTU_TradersParams', $crypt, false );
			}
		}
	}
}
add_action( 'personal_options_update', 'WTU_Account_Trader_Save' );
add_action( 'edit_user_profile_update', 'WTU_Account_Trader_Save' );

?>
