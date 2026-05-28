<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recovery Manager Class
 * Handles fatal errors and provides an emergency recovery interface.
 */
class Recovery_Manager {

	/**
	 * Initialize the recovery manager.
	 */
	public static function init() {
		// Register shutdown function to catch fatal errors
		register_shutdown_function( array( __CLASS__, 'handle_fatal_error' ) );

		// Hook into init to handle recovery page and deactivation
		add_action( 'init', array( __CLASS__, 'maybe_handle_recovery' ) );

		// Register AJAX for AI analysis in recovery screen
		add_action( 'wp_ajax_aida_analyze_fatal_error', array( __CLASS__, 'ajax_analyze_fatal_error' ) );
		add_action( 'wp_ajax_nopriv_aida_analyze_fatal_error', array( __CLASS__, 'ajax_analyze_fatal_error' ) );
	}

	/**
	 * AJAX handler for AI fatal error analysis.
	 */
	public static function ajax_analyze_fatal_error() {
		check_ajax_referer( 'aida_analyze_fatal' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Forbidden' );
		}

		$error_message = isset( $_POST['error_message'] ) ? sanitize_text_field( $_POST['error_message'] ) : '';
		$error_file    = isset( $_POST['error_file'] ) ? sanitize_text_field( $_POST['error_file'] ) : '';
		$error_line    = isset( $_POST['error_line'] ) ? intval( $_POST['error_line'] ) : 0;
		$plugin_file   = isset( $_POST['plugin_file'] ) ? sanitize_text_field( $_POST['plugin_file'] ) : '';

		$context = array(
			'error' => array(
				'message' => $error_message,
				'file'    => $error_file,
				'line'    => $error_line,
			),
			'plugin' => $plugin_file,
			'wp_version' => get_bloginfo('version'),
			'php_version' => PHP_VERSION,
		);

		$result = AI_Client::get_diagnosis( $context, "این یک خطای Fatal است که باعث از کار افتادن سایت شده است. لطفاً دقیقاً بررسی کنید چرا این اتفاق افتاده و راه حل سریع ارائه دهید." );

		if ( isset( $result['success'] ) ) {
			wp_send_json_success( array( 'content' => $result['content'] ) );
		} else {
			wp_send_json_error( $result['error'] );
		}
	}

	/**
	 * Check if we should render recovery page or handle deactivation.
	 */
	public static function maybe_handle_recovery() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle deactivation request
		if ( isset( $_POST['aida_action'] ) && $_POST['aida_action'] === 'deactivate_plugin' && isset( $_POST['plugin_file'] ) ) {
			self::handle_deactivation();
		}

		// Render recovery page
		if ( isset( $_GET['aida_recovery'] ) ) {
			self::render_recovery_page();
		}
	}

	/**
	 * Handle fatal errors.
	 */
	public static function handle_fatal_error() {
		$error = error_get_last();

		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			// Check if the error is within a plugin
			$plugin_path = self::get_plugin_from_error( $error['file'] );

			if ( $plugin_path ) {
				self::render_rescue_screen( $error, $plugin_path );
			}
		}
	}

	/**
	 * Identify which plugin caused the error.
	 */
	private static function get_plugin_from_error( $file_path ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins_dir = wp_normalize_path( WP_PLUGIN_DIR );
		$file_path   = wp_normalize_path( $file_path );

		if ( strpos( $file_path, $plugins_dir ) === 0 ) {
			$relative_path = str_replace( $plugins_dir . '/', '', $file_path );
			$path_parts    = explode( '/', $relative_path );
			$plugin_slug   = $path_parts[0];

			// Try to find the main plugin file
			$all_plugins = get_plugins();
			foreach ( $all_plugins as $file => $data ) {
				if ( strpos( $file, $plugin_slug . '/' ) === 0 || $file === $plugin_slug ) {
					return $file;
				}
			}
		}

		return false;
	}

    /**
     * Common Styles for Recovery Pages
     */
    private static function get_common_styles() {
        return '
            :root {
                --primary: #2271b1;
                --primary-hover: #135e96;
                --error: #d63638;
                --bg: #f0f2f5;
                --card: #ffffff;
                --text: #1d2327;
                --text-muted: #646970;
                --radius: 16px;
                --wp-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif, "Tahoma";
            }
            .ai-analysis {
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 16px !important;
                margin: 25px 0 !important;
                text-align: right !important;
                overflow: hidden !important;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
            }
            .ai-header {
                background: linear-gradient(135deg, #2271b1 0%, #135e96 100%) !important;
                color: #fff !important;
                padding: 15px 20px !important;
                display: flex !important;
                align-items: center !important;
                gap: 10px !important;
                font-weight: 700 !important;
                font-size: 15px !important;
            }
            .ai-header svg { width: 20px; height: 20px; fill: currentColor; }
            .ai-content { 
                padding: 20px !important;
                font-size: 14px !important; 
                color: #334155 !important; 
                line-height: 1.8 !important; 
                font-family: var(--wp-font) !important;
            }
            .ai-content h3 { 
                color: var(--primary) !important; 
                font-size: 17px !important; 
                margin: 20px 0 10px !important; 
                padding-bottom: 8px !important;
                border-bottom: 2px solid #f1f5f9 !important;
                font-weight: 800 !important;
            }
            .ai-content p { margin-bottom: 15px !important; }
            .ai-content strong { color: #0f172a !important; font-weight: 700 !important; }
            .ai-content ul, .ai-content ol { margin: 15px 0 !important; padding-right: 25px !important; }
            .ai-content li { margin-bottom: 8px !important; }
            .ai-content code { 
                background: #f1f5f9 !important; 
                color: #e11d48 !important; 
                padding: 2px 6px !important; 
                border-radius: 4px !important; 
                font-family: monospace !important; 
            }
            .ai-content pre {
                background: #1e293b !important;
                color: #f8fafc !important;
                padding: 15px !important;
                border-radius: 8px !important;
                overflow-x: auto !important;
                direction: ltr !important;
                margin: 15px 0 !important;
            }
            .ai-content pre code { background: transparent !important; color: inherit !important; padding: 0 !important; }
            .spinner {
                width: 24px;
                height: 24px;
                border: 3px solid rgba(34, 113, 177, 0.2);
                border-top-color: var(--primary);
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
                margin: 0 auto 10px;
            }
            @keyframes spin { to { transform: rotate(360deg); } }
        ';
    }

    /**
     * Common JavaScript for AI Markdown Rendering
     */
    private static function get_common_js() {
        return "
            function renderAIDAContent(content) {
                if (!content) return '';
                
                // 1. Handle Code Blocks FIRST to protect their content
                let codeBlocks = [];
                let html = content.replace(/```(?:php|javascript|js|css|html)?([\s\S]*?)```/g, function(match, code) {
                    let placeholder = '[[AIDA_CODE_BLOCK_' + codeBlocks.length + ']]';
                    let escapedCode = code.trim()
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    codeBlocks.push('<pre class=\"aida-code-block\"><code>' + escapedCode + '</code></pre>');
                    return placeholder;
                });

                // 2. Convert Markdown-like syntax to HTML
                html = html
                    .replace(/### (.*)/g, '<h3>$1</h3>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/`([^`]+)`/g, '<code>$1</code>')
                    .replace(/^\d+\.\s+(.*)/gm, '<li>$1</li>')
                    .replace(/^[-*]\s+(.*)/gm, '<li>$1</li>')
                    .replace(/\n\n/g, '</p><p>');
                
                // 3. Wrap li tags in ul
                if (html.includes('<li>')) {
                    html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
                }

                // 4. Restore Code Blocks
                codeBlocks.forEach(function(block, index) {
                    html = html.replace('[[AIDA_CODE_BLOCK_' + index + ']]', block);
                });

                return '<p>' + html + '</p>';
            }
        ";
    }

	/**
	 * Render a minimal rescue screen when a fatal error occurs.
	 */
	private static function render_rescue_screen( $error, $plugin_file ) {
		if ( ! function_exists( 'current_user_can' ) ) {
			require_once ABSPATH . WPINC . '/pluggable.php';
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		while ( ob_get_level() ) {
			ob_end_clean();
		}

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );
		$plugin_name = ! empty( $plugin_data['Name'] ) ? $plugin_data['Name'] : $plugin_file;

		?>
		<!DOCTYPE html>
		<html dir="rtl" lang="fa-IR">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>دستیار هوشمند - بازیابی اضطراری</title>
			<style>
				<?php echo self::get_common_styles(); ?>
				#aida-recovery-screen {
					position: fixed; top: 0; left: 0; right: 0; bottom: 0;
					background: var(--bg); z-index: 99999999;
					display: flex; justify-content: center; align-items: center;
					padding: 20px; overflow-y: auto; font-family: var(--wp-font); direction: rtl;
				}
				body > *:not(#aida-recovery-screen) { display: none !important; }
				.container { 
					background: var(--card); border-radius: var(--radius); 
					box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
					max-width: 600px; width: 100%; overflow: hidden;
				}
				.header { background: var(--error); color: #fff; padding: 40px 30px; text-align: center; }
				.header svg { width: 64px; height: 64px; fill: #fff; margin-bottom: 15px; }
				.header h1 { margin: 0; font-size: 26px; font-weight: 800; }
				.content { padding: 30px; }
				.plugin-card {
					background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px;
					padding: 20px; display: flex; align-items: center; gap: 15px; margin-bottom: 25px;
				}
				.plugin-name { font-weight: 700; font-size: 18px; color: var(--text); }
				.error-details {
					background: #1e293b; border-radius: 10px; padding: 20px; margin-bottom: 30px;
					direction: ltr; text-align: left; font-family: monospace; font-size: 13px; color: #e2e8f0; overflow-x: auto;
				}
				.btn { 
					display: flex; align-items: center; justify-content: center;
					padding: 14px 24px; border-radius: 12px; text-decoration: none; 
					font-weight: 700; cursor: pointer; border: none; font-family: var(--wp-font); gap: 10px;
				}
				.btn-red { background: var(--error); color: #fff; width: 100%; }
				.btn-outline { background: transparent; border: 2px solid #e2e8f0; color: var(--text-muted); margin-top: 10px; }
			</style>
		</head>
		<body id="aida-recovery-body">
			<div id="aida-recovery-screen">
				<div class="container">
					<div class="header">
						<svg viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.45L20.14 19H3.86L12 5.45zM11 16h2v2h-2v-2zm0-8h2v6h-2V8z"/></svg>
						<h1>توقف موقت سایت!</h1>
					</div>
					<div class="content">
						<div class="plugin-card">
							<div class="plugin-details">
								<div style="font-size: 12px; color: var(--text-muted);">افزونه مخرب شناسایی شده:</div>
								<div class="plugin-name"><?php echo esc_html( $plugin_name ); ?></div>
							</div>
						</div>
						<div id="aida-ai-analysis-container">
							<div style="text-align: center; padding: 20px;">
								<div class="spinner"></div>
								<div style="font-size: 13px; color: var(--primary);">در حال تحلیل هوشمند خطا...</div>
							</div>
						</div>
						<div class="error-details">
							<div style="color: #f472b6; margin-bottom: 5px;">// Fatal Error:</div>
							<div style="color: #fbbf24;"><?php echo esc_html( $error['message'] ); ?></div>
						</div>
						<form method="post" action="">
							<input type="hidden" name="aida_action" value="deactivate_plugin">
							<input type="hidden" name="plugin_file" value="<?php echo esc_attr( $plugin_file ); ?>">
							<?php wp_nonce_field( 'aida_deactivate_plugin' ); ?>
							<button type="submit" class="btn btn-red">غیرفعال‌سازی و بازگردانی سایت</button>
						</form>
						<a href="<?php echo esc_url( admin_url() ); ?>" class="btn btn-outline">تلاش برای ورود به پیشخوان</a>
					</div>
				</div>
			</div>
			<script>
				<?php echo self::get_common_js(); ?>
				document.addEventListener('DOMContentLoaded', function() {
					const container = document.getElementById('aida-ai-analysis-container');
					fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'aida_analyze_fatal_error',
							error_message: <?php echo json_encode( $error['message'] ); ?>,
							error_file: <?php echo json_encode( $error['file'] ); ?>,
							error_line: <?php echo json_encode( $error['line'] ); ?>,
							plugin_file: <?php echo json_encode( $plugin_file ); ?>,
							_ajax_nonce: '<?php echo wp_create_nonce( 'aida_analyze_fatal' ); ?>'
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success && data.data.content) {
							container.innerHTML = `
								<div class="ai-analysis">
									<div class="ai-header">
										<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
										تحلیل هوشمند دستیار
									</div>
									<div class="ai-content">\${renderAIDAContent(data.data.content)}</div>
								</div>`;
						} else { container.innerHTML = ''; }
					}).catch(() => { container.innerHTML = ''; });
				});
			</script>
		</body></html>
		<?php
		exit;
	}

    /**
     * Handle the deactivation request.
     */
    private static function handle_deactivation() {
        if ( ! check_admin_referer( 'aida_deactivate_plugin' ) ) {
            return;
        }
        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        deactivate_plugins( $_POST['plugin_file'] );
        wp_redirect( admin_url() );
        exit;
    }

	/**
	 * Render a standalone recovery page for manual access.
	 */
	public static function render_recovery_page() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins' );
		$last_errors = Error_Scanner::scan( array( 'limit' => 1 ) );
		$last_error = ! empty( $last_errors['items'] ) ? $last_errors['items'][0] : null;

		?>
		<!DOCTYPE html>
		<html dir="rtl" lang="fa-IR">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>دستیار هوشمند - حالت ایمن</title>
			<style>
				<?php echo self::get_common_styles(); ?>
				#aida-safe-mode-screen {
					position: fixed; top: 0; left: 0; right: 0; bottom: 0;
					background: var(--bg); z-index: 99999999; padding: 40px 20px;
					overflow-y: auto; font-family: var(--wp-font); direction: rtl;
				}
				body > *:not(#aida-safe-mode-screen) { display: none !important; }
				.container { 
					background: var(--card); border-radius: var(--radius); 
					box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
					max-width: 800px; margin: 0 auto; overflow: hidden;
				}
				.header { background: var(--primary); color: #fff; padding: 40px 30px; text-align: center; }
				.header h1 { margin: 0; font-size: 24px; font-weight: 800; }
				.content { padding: 30px; }
				.error-notice { background: #fff8f8; border-right: 4px solid var(--error); padding: 15px; margin-bottom: 25px; border-radius: 8px; font-size: 14px; }
				.plugin-list { list-style: none; padding: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
				.plugin-item { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; }
				.plugin-name { font-weight: 700; font-size: 15px; color: var(--text); }
				.btn-deactivate { padding: 8px 15px; border-radius: 8px; cursor: pointer; border: 1px solid #fee2e2; background: #fef2f2; color: var(--error); font-family: var(--wp-font); }
			</style>
		</head>
		<body>
			<div id="aida-safe-mode-screen">
				<div class="container">
					<div class="header"><h1>حالت ایمن دستیار هوشمند</h1></div>
					<div class="content">
						<?php if ( $last_error ) : ?>
							<div class="error-notice"><strong>آخرین خطا:</strong> <code><?php echo esc_html( $last_error['message'] ); ?></code></div>
							<div id="aida-safe-mode-analysis">
								<div style="text-align: center; padding: 20px;"><div class="spinner"></div><div>در حال تحلیل خطا...</div></div>
							</div>
						<?php endif; ?>
						<ul class="plugin-list">
							<?php foreach ( $active_plugins as $plugin_file ) : 
								$data = isset($all_plugins[$plugin_file]) ? $all_plugins[$plugin_file] : array('Name' => $plugin_file);
							?>
								<li class="plugin-item">
									<div class="plugin-info"><span class="plugin-name"><?php echo esc_html( $data['Name'] ); ?></span></div>
									<form method="post" action="">
										<input type="hidden" name="aida_action" value="deactivate_plugin">
										<input type="hidden" name="plugin_file" value="<?php echo esc_attr( $plugin_file ); ?>">
										<?php wp_nonce_field( 'aida_deactivate_plugin' ); ?>
										<button type="submit" class="btn-deactivate">غیرفعال کردن</button>
									</form>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<div style="text-align: center; padding: 20px; border-top: 1px solid #eee;">
						<a href="<?php echo admin_url(); ?>" style="color: var(--primary); text-decoration: none; font-weight: 700;">← بازگشت به پیشخوان</a>
					</div>
				</div>
			</div>
			<script>
				<?php echo self::get_common_js(); ?>
				document.addEventListener('DOMContentLoaded', function() {
					const container = document.getElementById('aida-safe-mode-analysis');
					if (!container) return;
					fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'aida_analyze_fatal_error',
							error_message: <?php echo json_encode( $last_error['message'] ?? '' ); ?>,
							_ajax_nonce: '<?php echo wp_create_nonce( 'aida_analyze_fatal' ); ?>'
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success && data.data.content) {
							container.innerHTML = `
								<div class="ai-analysis">
									<div class="ai-header">تحلیل هوشمند آخرین خطا</div>
									<div class="ai-content">\${renderAIDAContent(data.data.content)}</div>
								</div>`;
						} else { container.innerHTML = ''; }
					}).catch(() => { container.innerHTML = ''; });
				});
			</script>
		</body></html>
		<?php
		exit;
	}
}
