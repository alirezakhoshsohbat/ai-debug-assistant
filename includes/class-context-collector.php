<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Context Collector Class
 */
class Context_Collector {

	/**
	 * Collect all relevant site context.
	 */
	public static function collect() {
		return array(
			'wp_version'      => get_bloginfo( 'version' ),
			'php_version'     => phpversion(),
			'memory_limit'    => ini_get( 'memory_limit' ),
			'server_software' => $_SERVER['SERVER_SOFTWARE'],
			'active_theme'    => wp_get_theme()->get( 'Name' ),
			'theme_version'   => wp_get_theme()->get( 'Version' ),
			'active_plugins'  => self::get_active_plugins(),
			'rest_api_status' => self::check_rest_api(),
			'ajax_status'     => self::check_ajax_status(),
			'mysql_version'   => self::get_mysql_version(),
			'debug_mode'      => defined( 'WP_DEBUG' ) && WP_DEBUG,
		);
	}

	/**
	 * Get active plugins list.
	 */
	private static function get_active_plugins() {
		$active_plugins = get_option( 'active_plugins' );
		$plugins_data   = array();

		foreach ( $active_plugins as $plugin_path ) {
			$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
			$plugins_data[] = array(
				'name'    => $data['Name'],
				'version' => $data['Version'],
			);
		}

		return $plugins_data;
	}

	/**
	 * Check REST API status.
	 */
	private static function check_rest_api() {
		$response = wp_remote_get( get_rest_url() );
		return ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) ? 'OK' : 'Error';
	}

	/**
	 * Check AJAX status.
	 */
	private static function check_ajax_status() {
		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), array( 'body' => array( 'action' => 'non_existent_action' ) ) );
		return ( ! is_wp_error( $response ) ) ? 'OK' : 'Error';
	}

	/**
	 * Get MySQL version.
	 */
	private static function get_mysql_version() {
		global $wpdb;
		return $wpdb->db_version();
	}
}
