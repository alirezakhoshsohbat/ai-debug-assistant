<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$active_plugins = get_option( 'active_plugins' );
// Filter out our own plugin
$my_plugin = AIDA_BASENAME;
$active_plugins = array_filter( $active_plugins, function( $p ) use ( $my_plugin ) {
    return $p !== $my_plugin;
} );

$state = get_transient( 'aida_isolation_state' );
?>

<div class="wrap aida-admin-wrap">
    <h1>جداسازی هوشمند افزونه مشکل‌دار</h1>
    
    <div class="aida-card">
        <p>این ابزار با استفاده از الگوریتم جستجوی باینری، به شما کمک می‌کند افزونه‌ای که باعث ایجاد خطا شده است را پیدا کنید.</p>
        
        <?php if ( ! $state ) : ?>
            <div class="aida-isolation-start">
                <p>تعداد افزونه‌های فعال جهت بررسی: <strong><?php echo count( $active_plugins ); ?></strong></p>
                <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                    <input type="hidden" name="action" value="aida_start_isolation">
                    <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
                    <button type="submit" class="aida-button">شروع فرآیند جداسازی</button>
                </form>
            </div>
        <?php else : ?>
            <div class="aida-isolation-process">
                <h3>مرحله عیب‌یابی</h3>
                <p>در حال حاضر تعدادی از افزونه‌ها غیرفعال شده‌اند. لطفاً بررسی کنید که آیا مشکل هنوز پابرجاست؟</p>
                
                <div style="margin: 20px 0;">
                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;">
                        <input type="hidden" name="action" value="aida_step_isolation">
                        <input type="hidden" name="status" value="persists">
                        <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
                        <button type="submit" class="aida-button" style="background:#d63638;">بله، مشکل هنوز وجود دارد</button>
                    </form>

                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline;">
                        <input type="hidden" name="action" value="aida_step_isolation">
                        <input type="hidden" name="status" value="fixed">
                        <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
                        <button type="submit" class="aida-button" style="background:#673ab7;">خیر، مشکل حل شد</button>
                    </form>

                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline; margin-right: 20px;">
                        <input type="hidden" name="action" value="aida_reset_isolation">
                        <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
                        <button type="submit" class="aida-button" style="background:#666;">انصراف و بازگردانی همه</button>
                    </form>
                </div>

                <p><small>افزونه‌های تحت بررسی در این مرحله: <?php echo count( $state['candidates'] ); ?> عدد</small></p>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( isset( $_GET['found'] ) ) : 
        $found_plugin = sanitize_text_field( $_GET['found'] );
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $found_plugin );
    ?>
        <div class="notice notice-error is-dismissible" style="margin-top:20px;">
            <p><strong>احتمالاً افزونه مشکل‌دار:</strong> <?php echo esc_html( $plugin_data['Name'] ); ?> (<?php echo esc_html( $found_plugin ); ?>)</p>
        </div>
    <?php endif; ?>
</div>
