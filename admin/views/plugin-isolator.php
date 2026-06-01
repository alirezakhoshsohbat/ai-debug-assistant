<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$active_plugins = get_option( 'active_plugins' );
$my_plugin = AIDA_BASENAME;
$active_plugins = array_filter( $active_plugins, function( $p ) use ( $my_plugin ) {
    return $p !== $my_plugin;
} );

$state = get_transient( 'aida_isolation_state' );
?>

<div class="wrap aida-admin-wrap">
  <div class="aida-page-header">
    <div class="aida-page-header-content">
      <div class="aida-page-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <polyline points="17 11 19 13 23 9"/>
        </svg>
      </div>
      <div>
        <h1>جداسازی هوشمند افزونه مشکل‌دار</h1>
        <p class="aida-page-subtitle">با الگوریتم جستجوی باینری، افزونه ایجادکننده خطا را پیدا کنید</p>
      </div>
    </div>
  </div>

  <div class="aida-card">
    <div class="aida-section-header">
      <div class="aida-section-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div>
        <h2>حالت عیب‌یابی</h2>
        <p>این ابزار با غیرفعال‌سازی دسته‌ای افزونه‌ها، مسبب اصلی خطا را ایزوله می‌کند</p>
      </div>
    </div>

    <div class="aida-isolation-body">
      <?php if ( ! $state ) : ?>
        <div class="aida-isolation-start">
          <div class="aida-isolation-info">
            <div class="aida-info-item">
              <span class="aida-info-label">تعداد افزونه‌های فعال</span>
              <span class="aida-info-value aida-info-value--ok"><?php echo count( $active_plugins ); ?></span>
            </div>
          </div>
          <div class="aida-isolation-desc">
            <p>با شروع فرآیند، افزونه‌ها به صورت دودویی نصف می‌شوند تا افزونه مشکل‌دار پیدا شود. در هر مرحله باید بررسی کنید که آیا مشکل هنوز وجود دارد یا خیر.</p>
          </div>
          <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="aida_start_isolation">
            <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
            <button type="submit" class="aida-btn aida-btn--primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="5 3 19 12 5 21 5 3"/>
              </svg>
              <span>شروع فرآیند جداسازی</span>
            </button>
          </form>
        </div>
      <?php else : ?>
        <div class="aida-isolation-process">
          <div class="aida-isolation-step-indicator">
            <span class="aida-step-badge">مرحله جاری</span>
            <span class="aida-step-count"><?php echo count( $state['candidates'] ); ?> افزونه باقی‌مانده</span>
          </div>

          <div class="aida-isolation-question">
            <p>در حال حاضر تعدادی از افزونه‌ها غیرفعال شده‌اند. لطفاً بررسی کنید که آیا مشکل هنوز پابرجاست؟</p>
          </div>

          <div class="aida-isolation-actions">
            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="aida_step_isolation">
              <input type="hidden" name="status" value="persists">
              <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
              <button type="submit" class="aida-btn aida-btn--danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span>بله، مشکل هنوز وجود دارد</span>
              </button>
            </form>

            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="aida_step_isolation">
              <input type="hidden" name="status" value="fixed">
              <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
              <button type="submit" class="aida-btn aida-btn--success">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>خیر، مشکل حل شد</span>
              </button>
            </form>

            <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
              <input type="hidden" name="action" value="aida_reset_isolation">
              <?php wp_nonce_field( 'aida_isolation_nonce' ); ?>
              <button type="submit" class="aida-btn aida-btn--outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                <span>انصراف و بازگردانی همه</span>
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ( isset( $_GET['found'] ) ) : 
    $found_plugin = sanitize_text_field( $_GET['found'] );
    $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $found_plugin );
  ?>
    <div class="aida-card aida-card--found">
      <div class="aida-section-header">
        <div class="aida-section-icon" style="background:#fef2f2;color:#dc2626;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <h2 style="color:#dc2626;">افزونه مشکل‌دار شناسایی شد</h2>
          <p>نتیجه جستجوی باینری برای یافتن مسبب خطا</p>
        </div>
      </div>
      <div class="aida-card-body">
        <div class="aida-found-plugin">
          <div class="aida-found-plugin-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div>
            <strong class="aida-found-plugin-name"><?php echo esc_html( $plugin_data['Name'] ); ?></strong>
            <span class="aida-found-plugin-path"><?php echo esc_html( $found_plugin ); ?></span>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>