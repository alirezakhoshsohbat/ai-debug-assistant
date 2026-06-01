<?php
$info = \AIDA\Includes\Context_Collector::collect();
?>
<div class="wrap aida-admin-wrap">
  <div class="aida-page-header">
    <div class="aida-page-header-content">
      <div class="aida-page-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
      </div>
      <div>
        <h1>اطلاعات سیستم</h1>
        <p class="aida-page-subtitle">مشخصات فنی سرور، وردپرس و تنظیمات فعلی سایت</p>
      </div>
    </div>
  </div>

  <div class="aida-card">
    <div class="aida-section-header">
      <div class="aida-section-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
      </div>
      <div>
        <h2>مشخصات سرور و وردپرس</h2>
        <p>اطلاعات کلی سیستم برای عیب‌یابی دقیق‌تر</p>
      </div>
    </div>

    <div class="aida-info-grid">
      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span>نسخه وردپرس</span>
        </div>
        <span class="aida-info-value"><?php echo esc_html( $info['wp_version'] ); ?></span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>
          <span>نسخه PHP</span>
        </div>
        <span class="aida-info-value <?php echo version_compare( $info['php_version'], '8.0', '<' ) ? 'aida-info-value--warning' : 'aida-info-value--ok'; ?>">
          <?php echo esc_html( $info['php_version'] ); ?>
          <?php if ( version_compare( $info['php_version'], '8.0', '<' ) ) : ?>
            <span class="aida-info-badge aida-info-badge--warn">قدیمی</span>
          <?php endif; ?>
        </span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          <span>محدودیت حافظه</span>
        </div>
        <?php 
        $mem = (int) $info['memory_limit'];
        $mem_ok = $mem >= 128;
        ?>
        <span class="aida-info-value <?php echo $mem_ok ? 'aida-info-value--ok' : 'aida-info-value--warning'; ?>">
          <?php echo esc_html( $info['memory_limit'] ); ?>
          <?php if ( ! $mem_ok ) : ?>
            <span class="aida-info-badge aida-info-badge--warn">کم</span>
          <?php endif; ?>
        </span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
          <span>نرم‌افزار سرور</span>
        </div>
        <span class="aida-info-value"><?php echo esc_html( $info['server_software'] ); ?></span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
          <span>نسخه دیتابیس</span>
        </div>
        <span class="aida-info-value"><?php echo esc_html( $info['mysql_version'] ); ?></span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>وضعیت دیباگ (WP_DEBUG)</span>
        </div>
        <span class="aida-info-value <?php echo $info['debug_mode'] ? 'aida-info-value--ok' : 'aida-info-value--warning'; ?>">
          <?php echo $info['debug_mode'] ? 'فعال' : 'غیرفعال'; ?>
          <?php if ( ! $info['debug_mode'] ) : ?>
            <span class="aida-info-badge aida-info-badge--warn">توصیه می‌شود فعال شود</span>
          <?php endif; ?>
        </span>
      </div>

      <div class="aida-info-item">
        <div class="aida-info-label">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          <span>وضعیت REST API</span>
        </div>
        <span class="aida-info-value"><?php echo esc_html( $info['rest_api_status'] ); ?></span>
      </div>
    </div>
  </div>
</div>