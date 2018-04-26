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
class WTU_Lib {
	private $defaults = array(
		'general' => array(
			'WTU_MaxCapital'	=> '12000',
			'WTU_SiteAPIKEY'	=> '',
		),
		'version'							=> '1.0'
	);


	function __construct($mode = 'install'){
		// settings
		$this->options = array(
			'general' => array_merge( $this->defaults['general'], get_option( 'WTU_settings', $this->defaults['general'] ) )
		);

		//-------------------------------------------
		// initialisation de la base de données
		//-------------------------------------------		
		if($mode == "install") {
			//-------------------------------------------
			// initialisation du menu admin
			//-------------------------------------------
			$this->slug = 'WorldTradingUnit';
			
			$this->about_tabs['about'] = 'About';
			$this->about_tabs['start'] = 'Getting Started';

			add_action('admin_menu',array(&$this,'WTU_admin_menu'),0);						
			add_action('admin_init', array($this, 'register_settings'));
//		} else {
//			$this -> nb_caract=get_option('nb_caract');
		}
	}

	/***
	***	@setup admin menu
	***/
	function WTU_admin_menu() {
// liste des icones dispo sur : https://developer.wordpress.org/resource/dashicons/#admin-site
		$this->pagehook = add_menu_page( __('WorldTradingUnit', $this->slug), __('WorldTradingUnit', $this->slug), 'manage_options', $this->slug, array(&$this, 'form_html'), ' dashicons-chart-line', '42.78578');
//		add_action('load-'.$this->pagehook, array(&$this, 'on_load_page'));
		
		//Add entry menu plugins
		$hook = add_submenu_page( $this->slug, __('Dashboard', $this->slug), __('Dashboard', $this->slug), 'manage_options', $this->slug, array(&$this, 'form_html') );
		add_action('load-'.$hook, array($this, 'process_action'));
		
		foreach( $this->about_tabs as $k => $tab ) {
			add_submenu_page( $this->slug, __($tab, $this->slug), __($tab, $this->slug), 'manage_options', "edit.php?action=$k", '', '' );
		}		

	}
	function form_html() {
		echo '<h1>'.get_admin_page_title().'</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields('WTU_settings');
		do_settings_sections('WTU_settings');
		submit_button();
		echo '</form>';	
	}
	public function register_settings()
	{
		
		// jeu de paramettres nommer WTU_settings dans la table options de la BDD
		register_setting('WTU_settings'	, 'WTU_settings');
			// WTU_section1 du setting WTU_settings
			add_settings_section('WTU_section1'	, __('Parameters for WTU ')		, array($this, 'section_html')		, 'WTU_settings');
				// les champs
				add_settings_field(
					'WTU_MaxCapital',
					 __('Max Capital Allowed for each User'),
					array($this, 'WTU_MaxCapital_html'),
					'WTU_settings',
					'WTU_section1'
				);
				add_settings_field(
					'WTU_SiteAPIKEY',
					__('WTU_SiteAPIKEY'),
					array($this, 'WTU_SiteAPIKEY_html'),
					'WTU_settings',
					'WTU_section1'
				);
	}

	public function WTU_MaxCapital_html()
	{
		if ($this->options['general']['WTU_MaxCapital'] < 12000) $this->options['general']['WTU_MaxCapital'] = 12000;
		echo '<input type="number" step="100" min="12000" name="WTU_settings[WTU_MaxCapital]"	value="' . $this->options['general']['WTU_MaxCapital'] . '"/>';
	}
	public function WTU_SiteAPIKEY_html()
	{
		// lecture des infos d'utilisateur courant
		$current_user=wp_get_current_user();
		$User_ID = $current_user->ID;
		
		//echo 'Username: ' . $current_user->user_login . "\n";
		//echo 'User email: ' . $current_user->user_email . "\n";
		//echo 'User level: ' . $current_user->user_level . "\n";
		//echo 'User first name: ' . $current_user->user_firstname . "\n";
		//echo 'User last name: ' . $current_user->user_lastname . "\n";
		//echo 'User display name: ' . $current_user->display_name . "\n";
		//echo 'User ID: ' . $current_user->ID . "\n";		
		$action['UserId']= $User_ID;
		if ($this->options['general']['WTU_SiteAPIKEY'] == "")  {
			// si pas d'APIKEY creation du compte site  et du compte admin sur WTU
			$action['Action']= "CreateSiteAccount";
			$action['Asset'] = "WTU";
			$action['UserMail'] = $current_user->user_email;
			if (session_id()) {
				$action['UserAffiliatedApiKey'] = $_SESSION['AffiliatedApiKey'];
			}
			$reponse = WTU_Send($action,$current_user);
			if ($reponse == "") {
				$action['Action']= "LoadSiteAccount";
				$reponse = WTU_Send($action,$current_user);
			}
			// Maj du SiteApiKey
			$this->options['general']['WTU_SiteAPIKEY'] = $reponse['SiteApiKey'];
			// Maj du UserApiKey qui et la même
			if ($reponse != "") {
				update_user_meta( $User_ID, 'WTU_UserApiKey',  $reponse["SiteApiKey"] );
				update_option( "WTU_settings", $this->options['general']);
			}
		} else {
			// lecture du site pour récup du nb licences
			$action['Action']= "LoadSiteAccount";
			$action['SiteApiKey'] = $this->options['general']['WTU_SiteAPIKEY'];
			$reponse = WTU_Send($action,$current_user);
			$this->options['general']['WTU_SiteAPIKEY'] = $reponse['SiteApiKey'];
		}
		// 
//		echo '<input type="text" disabled name="WTU_settings[WTU_SiteAPIKEY]"	value="' . $this->options['general']['WTU_SiteAPIKEY'] . '"/>   ' . __('Licences utilisateurs restantes : ') . $reponse['Licences'];
		echo '<input type="text" name="WTU_settings[WTU_SiteAPIKEY]" maxlength="42" size="42" value="' . $this->options['general']['WTU_SiteAPIKEY'] . '"/>   ' . __('Licences utilisateurs restantes : ') . $reponse['Licences'];
	}

	
	public function process_action()
	{
	   // ici action apres load de la page paramettre
	}

	public function section_html()
	{
	   _e('Enter general WTU parameters');
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
	 /*
	public function wp_setup_nav_menu_item( $menu_item ) {
		if ( is_admin() )
			return $menu_item;

		if ( 'page' != $menu_item->object )
			return $menu_item;

		// User is not logged in
		if ( ! is_user_logged_in() ) {

			// Hide Profile
			//if ( Theme_My_Login::is_tml_page( 'profile', $menu_item->object_id ) ) {
				$menu_item->_invalid = true;
			//}
		}

		return $menu_item;
	}
	add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' ), 12 );
*/
}
?>