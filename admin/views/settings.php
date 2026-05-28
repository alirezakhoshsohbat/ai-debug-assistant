<div class="wrap aida-admin-wrap">
    <h1>تنظیمات دستیار هوشمند</h1>
    
    <form method="post" action="options.php">
        <?php
        settings_fields( 'aida_settings_group' );
        do_settings_sections( 'aida_settings_group' );
        ?>
        <div class="aida-card">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">کلید API هوش مصنوعی</th>
                    <td>
                        <input type="password" name="aida_api_key" value="<?php echo esc_attr( get_option( 'aida_api_key' ) ); ?>" class="regular-text" />
                        <p class="description">این کلید برای برقراری ارتباط با هوش مصنوعی جهت عیب‌یابی استفاده می‌شود.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">مدل هوش مصنوعی</th>
                    <td>
                        <input type="text" name="aida_model" value="<?php echo esc_attr( get_option( 'aida_model', 'gpt-4-turbo-preview' ) ); ?>" class="regular-text" />
                        <p class="description">نام مدل مورد نظر (مثلاً gpt-4-turbo-preview یا gpt-3.5-turbo).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">آدرس پایه API (Base URL)</th>
                    <td>
                        <input type="text" name="aida_base_url" value="<?php echo esc_attr( get_option( 'aida_base_url', 'https://api.openai.com/v1' ) ); ?>" class="regular-text" />
                        <p class="description">آدرس پایه برای درخواست‌های API (پیش‌فرض: https://api.openai.com/v1).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">تست اتصال</th>
                    <td>
                        <button type="button" id="aida-test-connection" class="button button-secondary">بررسی اتصال به API</button>
                        <span id="aida-connection-status" style="margin-right: 10px; font-weight: bold;"></span>
                        <p class="description">برای اطمینان از صحت تنظیمات، دکمه بالا را کلیک کنید.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">حالت عیب‌یابی وردپرس (WP_DEBUG)</th>
                    <td>
                        <?php 
                        $is_debug = defined('WP_DEBUG') && WP_DEBUG;
                        $config_path = \AIDA\Includes\Config_Manager::get_config_path();
                        $is_writable = is_writable($config_path);
                        ?>
                        <input type="checkbox" name="aida_wp_debug" value="1" <?php checked( 1, get_option( 'aida_wp_debug', $is_debug ? 1 : 0 ), true ); ?> <?php echo ! $is_writable ? 'disabled' : ''; ?> />
                        <label>فعال‌سازی حالت دیباگ وردپرس (تغییر در wp-config.php)</label>
                        <?php if ( ! $is_writable ) : ?>
                            <p class="description" style="color: #d63638;">فایل wp-config.php قابل ویرایش نیست. لطفاً دسترسی‌های فایل را بررسی کنید.</p>
                        <?php else : ?>
                            <p class="description">با فعال‌سازی این گزینه، خطاها و هشدارهای وردپرس ثبت می‌شوند.</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">فعال‌سازی ارسال لاگ‌ها</th>
                    <td>
                        <input type="checkbox" name="aida_enable_logs" value="1" <?php checked( 1, get_option( 'aida_enable_logs' ), true ); ?> />
                        <label>ارسال جزئیات خطاها به هوش مصنوعی برای تحلیل دقیق‌تر</label>
                    </td>
                </tr>
            </table>
            <?php submit_button( 'ذخیره تنظیمات', 'aida-button' ); ?>
        </div>
    </form>
</div>
