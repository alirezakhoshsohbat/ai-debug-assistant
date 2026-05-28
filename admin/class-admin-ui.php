<?php

namespace AIDA\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin UI Class
 */
class Admin_UI {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_aida_run_diagnosis', array( $this, 'ajax_run_diagnosis' ) );
		add_action( 'wp_ajax_aida_ask_ai', array( $this, 'ajax_ask_ai' ) );
		add_action( 'wp_ajax_aida_test_api_connection', array( $this, 'ajax_test_api_connection' ) );
		
		add_action( 'admin_post_aida_start_isolation', array( $this, 'handle_start_isolation' ) );
		add_action( 'admin_post_aida_step_isolation', array( $this, 'handle_step_isolation' ) );
		add_action( 'admin_post_aida_reset_isolation', array( $this, 'handle_reset_isolation' ) );
		
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_aida_wp_debug', array( $this, 'handle_wp_debug_update' ), 10, 2 );
	}

	public function add_menu_pages() {
		add_menu_page(
			'عیب‌یاب هوشمند',
			'عیب‌یاب هوشمند',
			'manage_options',
			'ai-debug-assistant',
			array( $this, 'render_main_page' ),
			'dashicons-visibility',
			65
		);

		add_submenu_page(
			'ai-debug-assistant',
			'تحلیل کامل سایت',
			'تحلیل کامل سایت',
			'manage_options',
			'ai-debug-assistant',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			'ai-debug-assistant',
			'پرسش از هوش مصنوعی',
			'پرسش از هوش مصنوعی',
			'manage_options',
			'aida-ask-ai',
			array( $this, 'render_ask_ai_page' )
		);

		add_submenu_page(
			'ai-debug-assistant',
			'اسکن خطاهای سایت',
			'اسکن خطاهای سایت',
			'manage_options',
			'aida-error-scanner',
			array( $this, 'render_error_scanner_page' )
		);

		add_submenu_page(
			'ai-debug-assistant',
			'تحلیل تداخل افزونه‌ها',
			'تحلیل تداخل افزونه‌ها',
			'manage_options',
			'aida-conflict-detector',
			array( $this, 'render_conflict_detector_page' )
		);

		add_submenu_page(
			'ai-debug-assistant',
			'اطلاعات سیستم',
			'اطلاعات سیستم',
			'manage_options',
			'aida-system-info',
			array( $this, 'render_system_info_page' )
		);

/*
		add_submenu_page(
			'ai-debug-assistant',
			'جداسازی افزونه مشکل‌دار',
			'جداسازی افزونه مشکل‌دار',
			'manage_options',
			'aida-plugin-isolator',
			array( $this, 'render_plugin_isolator_page' )
		);
*/

		add_submenu_page(
			'ai-debug-assistant',
			'تنظیمات',
			'تنظیمات',
			'manage_options',
			'aida-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'ai-debug-assistant',
			'بازیابی اضطراری',
			'بازیابی اضطراری',
			'manage_options',
			'aida-emergency-recovery',
			array( $this, 'render_emergency_recovery_page' )
		);
	}

	public function render_emergency_recovery_page() {
		include AIDA_PLUGIN_DIR . 'admin/views/emergency-recovery.php';
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'ai-debug-assistant' ) === false && strpos( $hook, 'aida-' ) === false ) {
			return;
		}

		wp_enqueue_style( 'aida-admin-css', AIDA_PLUGIN_URL . 'admin/assets/css/admin.css', array(), AIDA_VERSION );
		wp_enqueue_script( 'aida-admin-js', AIDA_PLUGIN_URL . 'admin/assets/js/admin.js', array( 'jquery' ), AIDA_VERSION, true );
		
		wp_localize_script( 'aida-admin-js', 'aida_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'aida_nonce' ),
		) );
	}

	public function handle_wp_debug_update( $old_value, $new_value ) {
		\AIDA\Includes\Config_Manager::update_debug_mode( (bool) $new_value );
	}

	public function register_settings() {
		register_setting( 'aida_settings_group', 'aida_api_key' );
		register_setting( 'aida_settings_group', 'aida_model' );
		register_setting( 'aida_settings_group', 'aida_base_url' );
		register_setting( 'aida_settings_group', 'aida_wp_debug' );
		register_setting( 'aida_settings_group', 'aida_enable_logs' );
	}

	// Render Methods
	public function render_main_page() { include AIDA_PLUGIN_DIR . 'admin/views/main-page.php'; }
	public function render_ask_ai_page() { include AIDA_PLUGIN_DIR . 'admin/views/ask-ai.php'; }
	public function render_error_scanner_page() { include AIDA_PLUGIN_DIR . 'admin/views/error-scanner.php'; }
	public function render_conflict_detector_page() { include AIDA_PLUGIN_DIR . 'admin/views/conflict-detector.php'; }
	public function render_system_info_page() { include AIDA_PLUGIN_DIR . 'admin/views/system-info.php'; }
	public function render_plugin_isolator_page() { include AIDA_PLUGIN_DIR . 'admin/views/plugin-isolator.php'; }
	public function render_settings_page() { include AIDA_PLUGIN_DIR . 'admin/views/settings.php'; }

	// AJAX Handlers
	public function ajax_test_api_connection() {
		check_ajax_referer( 'aida_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'دسترسی غیرمجاز.' );
		}

		$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : null;
		$base_url = isset( $_POST['base_url'] ) ? esc_url_raw( $_POST['base_url'] ) : null;
		$model    = isset( $_POST['model'] ) ? sanitize_text_field( $_POST['model'] ) : null;

		$result = \AIDA\Includes\AI_Client::test_connection( $api_key, $base_url, $model );

		if ( isset( $result['success'] ) ) {
			wp_send_json_success( 'اتصال با موفقیت به آدرس زیر برقرار شد: ' . $result['url'] );
		} else {
			wp_send_json_error( $result['error'] );
		}
	}

	public function ajax_run_diagnosis() {
		check_ajax_referer( 'aida_nonce', 'nonce' );
		
		$context = \AIDA\Includes\Context_Collector::collect();
		$error_scan = \AIDA\Includes\Error_Scanner::scan();
		$errors = $error_scan['items'];
		$conflicts = \AIDA\Includes\Conflict_Detector::detect();
		
		$full_context = array(
			'system' => $context,
			'errors' => array_slice( $errors, 0, 15 ), // Send last 15 errors
			'conflicts' => $conflicts,
		);

		// Try Rule Engine first
		$rule_diagnosis = null;
		if ( ! empty( $errors ) ) {
			$rule_diagnosis = \AIDA\Includes\Rule_Engine::analyze( $errors[0]['message'] );
		}

		if ( $rule_diagnosis ) {
			wp_send_json_success( array(
				'source' => 'rule_engine',
				'diagnosis' => $rule_diagnosis['diagnosis'],
				'cause' => $rule_diagnosis['cause'],
				'solution' => $rule_diagnosis['solution'],
			) );
		}

		// Fallback to AI
		$ai_response = \AIDA\Includes\AI_Client::get_diagnosis( $full_context );
		
		if ( isset( $ai_response['success'] ) ) {
			wp_send_json_success( array(
				'source' => 'ai',
				'content' => $ai_response['content'],
			) );
		} else {
			wp_send_json_error( $ai_response['error'] );
		}
	}

	public function ajax_ask_ai() {
		check_ajax_referer( 'aida_nonce', 'nonce' );
		
		$problem = sanitize_textarea_field( $_POST['problem'] );
		$context = \AIDA\Includes\Context_Collector::collect();
		$errors = array();
		
		if ( get_option( 'aida_enable_logs' ) ) {
			$error_scan = \AIDA\Includes\Error_Scanner::scan();
			$errors = $error_scan['items'];
		}
		
		$full_context = array(
			'system' => $context,
			'errors' => array_slice( $errors, 0, 15 ), // Send last 15 errors
		);

		$ai_response = \AIDA\Includes\AI_Client::get_diagnosis( $full_context, $problem );
		
		if ( isset( $ai_response['success'] ) ) {
			wp_send_json_success( $ai_response['content'] );
		} else {
			wp_send_json_error( $ai_response['error'] );
		}
	}

	public function handle_start_isolation() {
		check_admin_referer( 'aida_isolation_nonce' );
		
		$active_plugins = get_option( 'active_plugins' );
		$my_plugin = AIDA_BASENAME;
		$candidates = array_filter( $active_plugins, function( $p ) use ( $my_plugin ) {
			return $p !== $my_plugin;
		} );

		$state = array(
			'original_active' => $active_plugins,
			'candidates' => array_values( $candidates ),
			'step' => 1
		);

		set_transient( 'aida_isolation_state', $state, HOUR_IN_SECONDS );
		$this->apply_isolation_step( $state );
	}

	public function handle_step_isolation() {
		check_admin_referer( 'aida_isolation_nonce' );
		$status = sanitize_text_field( $_POST['status'] );
		$state = get_transient( 'aida_isolation_state' );

		if ( ! $state ) {
			wp_redirect( admin_url( 'admin.php?page=aida-plugin-isolator' ) );
			exit;
		}

		$candidates = $state['candidates'];
		$count = count( $candidates );

		if ( $count <= 1 ) {
			$found = $count === 1 ? $candidates[0] : 'unknown';
			$this->handle_reset_isolation( false );
			wp_redirect( admin_url( 'admin.php?page=aida-plugin-isolator&found=' . $found ) );
			exit;
		}

		$mid = ceil( $count / 2 );
		$first_half = array_slice( $candidates, 0, $mid );
		$second_half = array_slice( $candidates, $mid );

		if ( $status === 'persists' ) {
			// Error still there, problem is in the currently active half
			$state['candidates'] = $first_half;
		} else {
			// Error gone, problem was in the deactivated half
			$state['candidates'] = $second_half;
		}

		if ( count( $state['candidates'] ) === 1 ) {
			$found = $state['candidates'][0];
			$this->handle_reset_isolation( false );
			wp_redirect( admin_url( 'admin.php?page=aida-plugin-isolator&found=' . $found ) );
			exit;
		}

		set_transient( 'aida_isolation_state', $state, HOUR_IN_SECONDS );
		$this->apply_isolation_step( $state );
	}

	public function handle_reset_isolation( $redirect = true ) {
		$state = get_transient( 'aida_isolation_state' );
		if ( $state ) {
			update_option( 'active_plugins', $state['original_active'] );
			delete_transient( 'aida_isolation_state' );
		}
		if ( $redirect ) {
			wp_redirect( admin_url( 'admin.php?page=aida-plugin-isolator' ) );
			exit;
		}
	}

	private function apply_isolation_step( $state ) {
		$candidates = $state['candidates'];
		$mid = ceil( count( $candidates ) / 2 );
		$to_keep_active = array_slice( $candidates, 0, $mid );
		
		// Always keep our plugin active
		$to_keep_active[] = AIDA_BASENAME;

		update_option( 'active_plugins', $to_keep_active );
		wp_redirect( admin_url( 'admin.php?page=aida-plugin-isolator' ) );
		exit;
	}
}
