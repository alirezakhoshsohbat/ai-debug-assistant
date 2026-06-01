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
        $font_url = AIDA_PLUGIN_URL . 'admin/assets/fonts/Yekan';
        return '
            @font-face {
                font-family: "Yekan";
                src: url("' . $font_url . '.eot?#iefix") format("embedded-opentype"),
                     url("' . $font_url . '.woff") format("woff"),
                     url("' . $font_url . '.ttf") format("truetype"),
                     url("' . $font_url . '.svg#Yekan") format("svg");
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
            :root {
                --primary: #0f172a;
                --primary-hover: #1e293b;
                --error: #dc2626;
                --bg: #f1f5f9;
                --card: #ffffff;
                --text: #0f172a;
                --text-muted: #64748b;
                --radius: 12px;
                --font: "Yekan", Tahoma, Arial, sans-serif;
            }
            *, *::before, *::after { box-sizing: border-box; font-family: var(--font); }
            body { margin: 0; padding: 0; background: var(--bg); color: var(--text); }
            input, button, select, textarea { font-family: var(--font); }
            .aida-overlay {
                position: fixed; inset: 0; z-index: 99999999;
                display: flex; align-items: center; justify-content: center;
                padding: 20px; overflow-y: auto; direction: rtl;
            }
            body > *:not(.aida-overlay) { display: none !important; }
            .aida-card-container {
                background: var(--card); border-radius: var(--radius);
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);
                max-width: 640px; width: 100%; overflow: hidden;
            }
            .aida-card-header {
                padding: 36px 28px; text-align: center;
            }
            .aida-card-header--error { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; }
            .aida-card-header--info { background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; }
            .aida-card-header-icon {
                width: 56px; height: 56px; border-radius: 50%;
                background: rgba(255,255,255,0.15);
                display: flex; align-items: center; justify-content: center;
                margin: 0 auto 14px; color: #fff;
            }
            .aida-card-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
            .aida-card-header p { margin: 6px 0 0; font-size: 13px; opacity: 0.85; }
            .aida-card-body { padding: 24px 28px; }
            .aida-plugin-card {
                background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
                padding: 16px 20px; display: flex; align-items: center; gap: 14px; margin-bottom: 20px;
            }
            .aida-plugin-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
            .aida-plugin-icon--error { background: #fef2f2; color: #dc2626; }
            .aida-plugin-icon--safe { background: #f0fdf4; color: #16a34a; }
            .aida-plugin-label { font-size: 11px; color: var(--text-muted); margin-bottom: 2px; }
            .aida-plugin-name { font-weight: 700; font-size: 15px; color: var(--text); }
            .aida-error-block {
                background: #1e293b; border-radius: 10px; padding: 16px 18px;
                direction: ltr; text-align: left;
                font-size: 12px; color: #e2e8f0; overflow-x: auto; margin-bottom: 20px;
                line-height: 1.6;
                font-family: "Cascadia Code", "Fira Code", monospace !important;
            }
            .aida-error-block .comment { color: #f472b6; }
            .aida-error-block .msg { color: #fbbf24; }
            .aida-loading-box {
                text-align: center; padding: 24px;
                border: 1px dashed #e2e8f0; border-radius: 10px; margin-bottom: 20px;
            }
            .aida-spinner-lg {
                width: 28px; height: 28px; border: 3px solid #e2e8f0;
                border-top-color: var(--primary); border-radius: 50%;
                animation: aida-spin 0.7s linear infinite; margin: 0 auto 10px;
            }
            @keyframes aida-spin { to { transform: rotate(360deg); } }
            .aida-loading-text { font-size: 13px; color: var(--text-muted); }
            .aida-ai-analysis {
                border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 20px;
            }
            .aida-ai-header {
                display: flex; align-items: center; gap: 8px;
                background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff;
                padding: 12px 18px; font-weight: 700; font-size: 14px;
            }
            .aida-ai-content {
                padding: 18px 20px; font-size: 13px; color: #1e293b; line-height: 1.8;
            }
            .aida-ai-content h3 { color: #0f172a; font-size: 15px; margin: 16px 0 8px; font-weight: 800; }
            .aida-ai-content p { margin-bottom: 12px; }
            .aida-ai-content strong { color: #0f172a; font-weight: 700; }
            .aida-ai-content ul, .aida-ai-content ol { margin: 10px 0; padding-right: 20px; }
            .aida-ai-content li { margin-bottom: 6px; }
            .aida-ai-content code {
                background: #f1f5f9; color: #e11d48; padding: 2px 5px;
                border-radius: 4px; font-size: 12px;
                font-family: "Cascadia Code", "Fira Code", monospace !important;
            }
            .aida-ai-content pre {
                background: #1e293b; color: #e2e8f0; padding: 12px;
                border-radius: 8px; overflow-x: auto; direction: ltr; text-align: left;
                margin: 10px 0; font-size: 12px;
            }
            .aida-ai-content pre code { background: none; color: inherit; padding: 0; }
            .aida-btn {
                display: flex; align-items: center; justify-content: center; gap: 8px;
                padding: 12px 22px; border-radius: 10px; text-decoration: none;
                font-weight: 700; cursor: pointer; border: none;
                font-size: 14px; transition: all 0.15s; width: 100%;
            }
            .aida-btn--error { background: #dc2626; color: #fff; }
            .aida-btn--error:hover { background: #b91c1c; }
            .aida-btn--primary { background: #0f172a; color: #fff; }
            .aida-btn--primary:hover { background: #1e293b; }
            .aida-btn--outline {
                background: transparent; border: 1px solid #e2e8f0; color: var(--text-muted); margin-top: 8px;
            }
            .aida-btn--outline:hover { background: #f8fafc; }
            .aida-btn--sm { padding: 8px 16px; font-size: 12px; width: auto; }
            .aida-divider { border: none; border-top: 1px solid #f1f5f9; margin: 16px 0; }
            .aida-back-link {
                display: flex; align-items: center; justify-content: center; gap: 6px;
                color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700;
                padding: 16px 20px; border-top: 1px solid #f1f5f9; transition: color 0.15s;
            }
            .aida-back-link:hover { color: var(--primary); }
            .aida-plugin-list { list-style: none; padding: 0; margin: 0 0 16px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
            .aida-plugin-list li {
                display: flex; justify-content: space-between; align-items: center;
                padding: 14px 18px; border-bottom: 1px solid #f1f5f9;
            }
            .aida-plugin-list li:last-child { border-bottom: none; }
            .aida-plugin-list .name { font-weight: 700; font-size: 13px; color: var(--text); }
        ';
    }

    /**
     * Common JavaScript for AI Markdown Rendering
     */
    private static function get_common_js() {
        return "
            function renderAIDAContent(content) {
                if (!content) return '';
                let codeBlocks = [];
                let html = content.replace(/```(?:php|javascript|js|css|html)?([\s\S]*?)```/g, function(match, code) {
                    let placeholder = '[[AIDA_CODE_BLOCK_' + codeBlocks.length + ']]';
                    let escapedCode = code.trim()
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                    codeBlocks.push('<pre><code>' + escapedCode + '</code></pre>');
                    return placeholder;
                });
                html = html
                    .replace(/### (.*)/g, '<h3>$1</h3>')
                    .replace(/\\*\\*(.*?)\\*\\*/g, '<strong>$1</strong>')
                    .replace(/\`([^\`]+)\`/g, '<code>$1</code>')
                    .replace(/^\\d+\\.\\s+(.*)/gm, '<li>$1</li>')
                    .replace(/^[-*]\\s+(.*)/gm, '<li>$1</li>')
                    .replace(/\\n\\n/g, '</p><p>');
                if (html.includes('<li>')) {
                    html = html.replace(/(<li>[\\s\\S]*?<\\/li>)/g, '<ul>$1</ul>');
                    html = html.replace(/<\\/ul>\\s*<ul>/g, '');
                }
                codeBlocks.forEach(function(block, index) {
                    html = html.replace('[[AIDA_CODE_BLOCK_' + index + ']]', block);
                });
                return '<p>' + html + '</p>';
            }
        ";
    }

    /**
     * Render a professional rescue screen when a fatal error occurs.
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
            <title>دستیار هوشمند — بازیابی اضطراری</title>
            <style><?php echo self::get_common_styles(); ?></style>
        </head>
        <body>
            <div class="aida-overlay">
                <div class="aida-card-container">
                    <div class="aida-card-header aida-card-header--error">
                        <div class="aida-card-header-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </div>
                        <h1>سایت متوقف شد!</h1>
                        <p>یک خطای بحرانی رخ داده که از بارگذاری سایت جلوگیری می‌کند</p>
                    </div>
                    <div class="aida-card-body">
                        <div class="aida-plugin-card">
                            <div class="aida-plugin-icon aida-plugin-icon--error">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                            </div>
                            <div>
                                <div class="aida-plugin-label">افزونه مشکوک شناسایی شده</div>
                                <div class="aida-plugin-name"><?php echo esc_html( $plugin_name ); ?></div>
                            </div>
                        </div>

                        <div id="aida-ai-analysis-container">
                            <div class="aida-loading-box">
                                <div class="aida-spinner-lg"></div>
                                <div class="aida-loading-text">در حال تحلیل هوشمند خطا...</div>
                            </div>
                        </div>

                        <div class="aida-error-block">
                            <div class="comment">// Fatal Error:</div>
                            <div class="msg"><?php echo esc_html( $error['message'] ); ?></div>
                            <?php if ( ! empty( $error['file'] ) ) : ?>
                                <div style="color:#94a3b8;margin-top:6px;">
                                    <span style="color:#60a5fa;">file:</span> <?php echo esc_html( $error['file'] ); ?>:<span style="color:#60a5fa;"><?php echo esc_html( $error['line'] ); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <form method="post" action="">
                            <input type="hidden" name="aida_action" value="deactivate_plugin">
                            <input type="hidden" name="plugin_file" value="<?php echo esc_attr( $plugin_file ); ?>">
                            <?php wp_nonce_field( 'aida_deactivate_plugin' ); ?>
                            <button type="submit" class="aida-btn aida-btn--error">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                </svg>
                                غیرفعال‌سازی افزونه و بازگردانی سایت
                            </button>
                        </form>
                        <a href="<?php echo esc_url( admin_url() ); ?>" class="aida-btn aida-btn--outline">
                            تلاش برای ورود به پیشخوان
                        </a>
                    </div>
                </div>
            </div>
            <script>
                <?php echo self::get_common_js(); ?>
                document.addEventListener('DOMContentLoaded', function() {
                    var container = document.getElementById('aida-ai-analysis-container');
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
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.data.content) {
                            container.innerHTML = '<div class="aida-ai-analysis"><div class="aida-ai-header"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>تحلیل هوشمند دستیار</div><div class="aida-ai-content">' + renderAIDAContent(data.data.content) + '</div></div>';
                        } else { container.innerHTML = ''; }
                    }).catch(function() { container.innerHTML = ''; });
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
			<title>دستیار هوشمند — حالت ایمن</title>
			<style><?php echo self::get_common_styles(); ?></style>
		</head>
		<body>
			<div class="aida-overlay" style="position:fixed;inset:0;z-index:99999999;display:flex;align-items:flex-start;justify-content:center;padding:30px 20px;overflow-y:auto;direction:rtl;">
				<div class="aida-card-container" style="max-width:780px;">
					<div class="aida-card-header aida-card-header--info">
						<div class="aida-card-header-icon">
							<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
							</svg>
						</div>
						<h1>حالت ایمن</h1>
						<p>سایت در حالت ایمن اجرا می‌شود — می‌توانید افزونه‌های مشکل‌ساز را غیرفعال کنید</p>
					</div>
					<div class="aida-card-body">
						<?php if ( $last_error ) : ?>
							<div class="aida-ai-analysis">
								<div class="aida-ai-header">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
										<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>
									</svg>
									آخرین خطای ثبت‌شده
								</div>
								<div class="aida-ai-content">
									<div class="aida-error-block" style="margin:0;">
										<div class="comment">// Last Error:</div>
										<div class="msg"><?php echo esc_html( $last_error['message'] ); ?></div>
										<?php if ( ! empty( $last_error['file'] ) ) : ?>
											<div style="color:#94a3b8;margin-top:6px;">
												<span style="color:#60a5fa;">file:</span> <?php echo esc_html( $last_error['file'] ); ?>:<span style="color:#60a5fa;"><?php echo esc_html( $last_error['line'] ); ?></span>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<div id="aida-safe-mode-analysis">
								<div class="aida-loading-box">
									<div class="aida-spinner-lg"></div>
									<div class="aida-loading-text">در حال تحلیل هوشمند خطا...</div>
								</div>
							</div>
						<?php endif; ?>

						<hr class="aida-divider">

						<div style="font-weight:700;font-size:14px;margin-bottom:12px;color:var(--text);">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:6px;">
								<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
							</svg>
							افزونه‌های فعال
						</div>
						<ul class="aida-plugin-list">
							<?php foreach ( $active_plugins as $plugin_file ) : 
								$data = isset($all_plugins[$plugin_file]) ? $all_plugins[$plugin_file] : array('Name' => $plugin_file);
							?>
								<li>
									<span class="name"><?php echo esc_html( $data['Name'] ); ?></span>
									<form method="post" action="">
										<input type="hidden" name="aida_action" value="deactivate_plugin">
										<input type="hidden" name="plugin_file" value="<?php echo esc_attr( $plugin_file ); ?>">
										<?php wp_nonce_field( 'aida_deactivate_plugin' ); ?>
										<button type="submit" class="aida-btn aida-btn--sm aida-btn--outline" style="color:#dc2626;border-color:#fecaca;">
											غیرفعال‌سازی
										</button>
									</form>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
					<a href="<?php echo admin_url(); ?>" class="aida-back-link">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/>
						</svg>
						بازگشت به پیشخوان
					</a>
				</div>
			</div>
			<script>
				<?php echo self::get_common_js(); ?>
				document.addEventListener('DOMContentLoaded', function() {
					var container = document.getElementById('aida-safe-mode-analysis');
					if (!container) return;
					fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'aida_analyze_fatal_error',
							error_message: <?php echo json_encode( $last_error['message'] ?? '' ); ?>,
							error_file: <?php echo json_encode( $last_error['file'] ?? '' ); ?>,
							error_line: <?php echo json_encode( $last_error['line'] ?? '' ); ?>,
							_ajax_nonce: '<?php echo wp_create_nonce( 'aida_analyze_fatal' ); ?>'
						})
					})
					.then(function(r) { return r.json(); })
					.then(function(data) {
						if (data.success && data.data.content) {
							container.innerHTML = '<div class="aida-ai-analysis"><div class="aida-ai-header"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>تحلیل هوشمند دستیار</div><div class="aida-ai-content">' + renderAIDAContent(data.data.content) + '</div></div>';
						} else { container.innerHTML = ''; }
					}).catch(function() { container.innerHTML = ''; });
				});
			</script>
		</body></html>
		<?php
		exit;
	}
}
