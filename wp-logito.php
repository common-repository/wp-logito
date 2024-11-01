<?php
/*
Plugin Name: Wp Logito
Plugin URI: http://logito.ir/
Description: Logito API helper for woocommerce
Version: 1.1.8
Author: Zanyar Abdolahzadeh
Author URI: http://zanyarapps.ir
License:     Logito-Policy
License URI: http://logito.ir/plugins-licence
Text Domain: Logito
Domain Path: /languages
*/

class WPLogito {
	function __construct() {
		add_action( 'plugins_loaded', array( $this, 'loadTextDomain' ) );

		include( "core/LogitoCore.php" );
		new LogitoCore();
	}

	function loadTextDomain() {
		$status = load_plugin_textdomain( 'Logito', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

new WPLogito();
