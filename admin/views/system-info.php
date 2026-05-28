<?php
$info = \AIDA\Includes\Context_Collector::collect();
?>
<div class="wrap aida-admin-wrap">
    <h1>اطلاعات سیستم</h1>
    
    <div class="aida-card">
        <table class="aida-system-info-table">
            <tr>
                <th>پارامتر</th>
                <th>مقدار</th>
            </tr>
            <tr>
                <td>نسخه وردپرس</td>
                <td><?php echo esc_html( $info['wp_version'] ); ?></td>
            </tr>
            <tr>
                <td>نسخه PHP</td>
                <td>
                    <?php echo esc_html( $info['php_version'] ); ?>
                    <?php if ( version_compare( $info['php_version'], '8.0', '<' ) ) : ?>
                        <span class="aida-warning">هشدار: نسخه PHP قدیمی است.</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>محدودیت حافظه</td>
                <td>
                    <?php echo esc_html( $info['memory_limit'] ); ?>
                    <?php 
                    $mem = (int) $info['memory_limit'];
                    if ( $mem < 128 ) : ?>
                        <span class="aida-warning">هشدار: حافظه کم است (حداقل 128MB توصیه می‌شود).</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>نرم‌افزار سرور</td>
                <td><?php echo esc_html( $info['server_software'] ); ?></td>
            </tr>
            <tr>
                <td>نسخه دیتابیس</td>
                <td><?php echo esc_html( $info['mysql_version'] ); ?></td>
            </tr>
            <tr>
                <td>وضعیت دیباگ (WP_DEBUG)</td>
                <td>
                    <?php echo $info['debug_mode'] ? 'فعال' : 'غیرفعال'; ?>
                    <?php if ( ! $info['debug_mode'] ) : ?>
                        <span class="aida-warning">توصیه می‌شود برای عیب‌یابی فعال شود.</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>وضعیت REST API</td>
                <td><?php echo esc_html( $info['rest_api_status'] ); ?></td>
            </tr>
        </table>
    </div>
</div>
