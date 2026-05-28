<div class="wrap aida-admin-wrap">
  <div class="aida-settings-header">
    <div class="aida-settings-header-content">
      <div class="aida-settings-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/>
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
      </div>
      <div>
        <h1>تنظیمات دستیار هوشمند</h1>
        <p class="aida-settings-subtitle">پیکربندی اتصال به هوش مصنوعی و تنظیمات عیب‌یابی</p>
      </div>
    </div>
    <div class="aida-settings-status" id="aida-global-status">
      <span class="aida-status-dot aida-status-dot--inactive"></span>
      <span>بررسی نشده</span>
    </div>
  </div>

  <form method="post" action="options.php" class="aida-settings-form">
    <?php settings_fields( 'aida_settings_group' ); ?>

    <div class="aida-settings-grid">
      <div class="aida-settings-section">
        <div class="aida-section-header">
          <div class="aida-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
            </svg>
          </div>
          <div>
            <h2>اتصال به هوش مصنوعی</h2>
            <p>تنظیمات مربوط به API و مدل هوش مصنوعی برای تحلیل خطاها</p>
          </div>
        </div>

        <div class="aida-field-group">
          <div class="aida-field">
            <label class="aida-field-label" for="aida_api_key">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              <span>کلید API</span>
            </label>
            <div class="aida-input-wrapper">
              <input type="password" name="aida_api_key" id="aida_api_key" value="<?php echo esc_attr( get_option( 'aida_api_key' ) ); ?>" class="aida-input" placeholder="sk-..." autocomplete="off" />
              <button type="button" class="aida-input-toggle" id="aida-toggle-api-key" tabindex="-1" aria-label="نمایش/مخفی‌سازی کلید API">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            <p class="aida-field-desc">کلید API مورد نیاز برای ارتباط با سرویس هوش مصنوعی</p>
          </div>

          <div class="aida-field">
            <label class="aida-field-label" for="aida_model">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 3 21 3 21 8"/>
                <line x1="4" y1="20" x2="21" y2="3"/>
                <polyline points="21 16 21 21 16 21"/>
                <line x1="15" y1="15" x2="21" y2="21"/>
                <line x1="4" y1="4" x2="9" y2="9"/>
              </svg>
              <span>مدل هوش مصنوعی</span>
            </label>
            <input type="text" name="aida_model" id="aida_model" value="<?php echo esc_attr( get_option( 'aida_model', 'gpt-4-turbo-preview' ) ); ?>" class="aida-input" />
            <p class="aida-field-desc">نام مدل مورد نظر برای تحلیل (مثلاً gpt-4-turbo-preview یا gpt-3.5-turbo)</p>
          </div>

          <div class="aida-field">
            <label class="aida-field-label" for="aida_base_url">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
              <span>آدرس پایه API</span>
            </label>
            <input type="url" name="aida_base_url" id="aida_base_url" value="<?php echo esc_attr( get_option( 'aida_base_url', 'https://api.openai.com/v1' ) ); ?>" class="aida-input" dir="ltr" />
            <p class="aida-field-desc">آدرس پایه سرور API (پیش‌فرض: https://api.openai.com/v1)</p>
          </div>
        </div>

        <div class="aida-section-action">
          <button type="button" id="aida-test-connection" class="aida-btn aida-btn--outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            <span>بررسی اتصال به API</span>
          </button>
          <div id="aida-connection-status" class="aida-connection-status">
            <span class="aida-status-dot aida-status-dot--inactive"></span>
            <span class="aida-status-text">منتظر بررسی...</span>
          </div>
        </div>
      </div>

      <div class="aida-settings-section">
        <div class="aida-section-header">
          <div class="aida-section-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <div>
            <h2>تنظیمات عیب‌یابی</h2>
            <p>کنترل حالت دیباگ و ارسال خطاها به هوش مصنوعی</p>
          </div>
        </div>

        <div class="aida-field-group">
          <?php
          $is_debug = defined('WP_DEBUG') && WP_DEBUG;
          $config_path = \AIDA\Includes\Config_Manager::get_config_path();
          $is_writable = is_writable($config_path);
          $wp_debug_val = get_option( 'aida_wp_debug', $is_debug ? 1 : 0 );
          $logs_enabled = get_option( 'aida_enable_logs' );
          ?>

          <div class="aida-field aida-field--toggle">
            <div class="aida-toggle-row">
              <div>
                <label class="aida-field-label" for="aida_wp_debug">حالت عیب‌یابی وردپرس (WP_DEBUG)</label>
                <p class="aida-field-desc">فعال‌سازی ثبت خطاها و هشدارهای وردپرس در فایل wp-config.php</p>
              </div>
              <label class="aida-toggle" <?php echo ! $is_writable ? 'data-tooltip="فایل wp-config.php قابل ویرایش نیست"' : ''; ?>>
                <input type="checkbox" name="aida_wp_debug" id="aida_wp_debug" value="1" <?php checked( 1, $wp_debug_val, true ); ?> <?php echo ! $is_writable ? 'disabled' : ''; ?> />
                <span class="aida-toggle-slider"></span>
              </label>
            </div>
            <?php if ( ! $is_writable ) : ?>
              <div class="aida-field-alert">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>فایل wp-config.php قابل ویرایش نیست. لطفاً دسترسی‌های فایل را بررسی کنید.</span>
              </div>
            <?php endif; ?>
          </div>

          <div class="aida-field aida-field--toggle">
            <div class="aida-toggle-row">
              <div>
                <label class="aida-field-label" for="aida_enable_logs">فعال‌سازی ارسال لاگ‌ها</label>
                <p class="aida-field-desc">ارسال جزئیات خطاها به هوش مصنوعی برای تحلیل دقیق‌تر و ارائه راهکار</p>
              </div>
              <label class="aida-toggle">
                <input type="checkbox" name="aida_enable_logs" id="aida_enable_logs" value="1" <?php checked( 1, $logs_enabled, true ); ?> />
                <span class="aida-toggle-slider"></span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="aida-settings-footer">
      <button type="submit" class="aida-btn aida-btn--primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        <span>ذخیره تنظیمات</span>
      </button>
    </div>
  </form>
</div>