<?php
/**
 * Plugin Name: IndieAuth
 * Plugin URI: https://github.com/indieweb/wordpress-indieauth/
 * Description: IndieAuth is a way to allow users to use their own domain to sign into other websites and services
 * Version: 3.4.2
 * Author: IndieWebCamp WordPress Outreach Club
 * Author URI: https://indieweb.org/WordPress_Outreach_Club
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: indieauth
 * Domain Path: /languages
 */

class IndieAuth_Plugin {
	public static $indieauth = null; // Loaded instance of authorize class

	public static function plugins_loaded() {
		// Load Core Classes that are always loaded
		self::load(
			array(
				'functions.php', // Global Functions
				'class-oauth-response.php', // OAuth REST Error Class
				'class-token-generic.php', // Token Base Class
				'class-indieauth-scope.php', // Scope Class
				'class-indieauth-scopes.php', // Scopes Class
				'class-indieauth-authorize.php', // IndieAuth Authorization Base Class
				'class-indieauth-admin.php', // Administration Class
			)
		);

		// Classes Required for the Local Endpoint
		$localfiles = array(
			'class-indieauth-client-discovery.php', // Client Discovery
			'class-token-user.php',
			'class-indieauth-token-endpoint.php', // Token Endpoint
			'client-indieauth-authorization-endpoint.php', // Authorization Endpoint
			'class-token-list-table.php', // Token Management UI
			'class-indieauth-token-ui.php',
			'class-indieauth-local-authorize.php',
		);

		// Classes Require for using a Remote Endpoint
		$remotefiles = array(
			'class-indieauth-remote-authorize.php',
			'class-token-transient.php',
			'class-web-signin.php',
		);
		$load        = get_option( 'indieauth_config', 'local' );
		switch ( $load ) {
			case 'remote':
				self::load( $remotefiles );
				static::$indieauth = new IndieAuth_Remote_Authorize();
				break;
			default:
				self::load( $localfiles );
				static::$indieauth = new IndieAuth_Local_Authorize();
				break;
		}

		if ( WP_DEBUG ) {
			self::load( 'class-indieauth-debug.php' );
		}

	}

	// Check that a file exists before loading it and if it does not print to the error log
	public static function load( $files, $dir = 'includes/' ) {
		if ( empty( $files ) ) {
			return;
		}
		$path = plugin_dir_path( __FILE__ ) . $dir;

		if ( is_string( $files ) ) {
			$files = array( $files );
		}
		foreach ( $files as $file ) {
			if ( file_exists( $path . $file ) ) {
				require_once $path . $file;
			} else {
				error_log( // phpcs:ignore
					// translators: 1. Path to file unable to load
					sprintf( __( 'Unable to load: %1s', 'indieauth' ), $path . $file )
				);
			}
		}
	}

}

add_action( 'plugins_loaded', array( 'IndieAuth_Plugin', 'plugins_loaded' ), 9 );
