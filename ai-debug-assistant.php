<?php
/**
 * Plugin Name:  نجات - دستیار هوشمند عیب‌یاب وردپرس
 * Plugin URI: https://alirezakhoshsohbat.ir/Nejat
 * Description: یک دستیار هوشمند برای عیب‌یابی و رفع مشکلات فنی سایت‌های وردپرسی با استفاده از هوش مصنوعی.
 * Version: 1.0.0
 * Author: Alireza Khoshsohbat
 * Author URI: https://alirezakhoshsohbat.ir
 * Text Domain: ai-debug-assistant
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants
define( 'AIDA_VERSION', '1.0.0' );
define( 'AIDA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIDA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIDA_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader or manual inclusion of core classes
require_once AIDA_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Main instance of the plugin.
 */
function aida_init() {
	return \AIDA\Includes\Plugin::get_instance();
}

// Kick off the plugin
aida_init();

add_action( 'admin_notices', function() {
    if ( current_user_can( 'manage_options' ) ) {
        echo '<div class="notice notice-info is-dismissible"><p>افزونه دستیار هوشمند فعال است. اگر منو را نمی‌بینید، لطفاً یک بار صفحه را رفرش کنید.</p></div>';
    }
});
