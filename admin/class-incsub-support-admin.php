<?php

/**
 * Handles the Admin side of the plugin
 */
class Incsub_Support_Admin {

	public $menus = array();

	public function __construct() {
		$this->includes();
		$this->add_menus();
	}

	/**
	 * Include needed files
	 */
	private function includes() {
		require_once( 'class-abstract-menu.php' );
		require_once( 'class-network-support-menu.php' );
		require_once( 'class-network-ticket-categories-menu.php' );
	}


	/**
	 * Create the menu objects
	 */
	private function add_menus() {
		if ( is_multisite() ) {
			if ( is_network_admin() && is_super_admin() ) {
				$this->menus['network_support_menu'] = new Incsub_Support_Network_Menu( 'ticket-manager-b', true );
				//new Incsub_Support_Network_Menu( 'ticket-manager-b', true );
			}
			else {

			}
		}
	}
}

