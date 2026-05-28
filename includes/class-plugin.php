<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class Plugin {

	/**
	 * Instance of this class.
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		require_once AIDA_PLUGIN_DIR . 'includes/class-context-collector.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-error-scanner.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-conflict-detector.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-plugin-isolator.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-rule-engine.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-ai-client.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-config-manager.php';
		require_once AIDA_PLUGIN_DIR . 'includes/class-recovery-manager.php';
		require_once AIDA_PLUGIN_DIR . 'admin/class-admin-ui.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		Recovery_Manager::init();
		
		// Initialize Admin UI for both admin and AJAX using Singleton
		if ( is_admin() ) {
			\AIDA\Admin\Admin_UI::get_instance();
		}
		
		add_action( 'admin_menu', function() {
			if ( class_exists( '\AIDA\Admin\Admin_UI' ) ) {
				\AIDA\Admin\Admin_UI::get_instance()->add_menu_pages();
			}
		} );
	}

	/**
	 * Register admin menu
	 */
	public function register_admin_menu() {
		$admin_ui = \AIDA\Admin\Admin_UI::get_instance();
		$admin_ui->add_menu_pages();
	}
}
