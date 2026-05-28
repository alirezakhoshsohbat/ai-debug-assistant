<?php
$paged = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'timestamp';
$order = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
$severity = isset( $_GET['severity'] ) ? sanitize_text_field( $_GET['severity'] ) : '';

$limit = 10;
$offset = ( $paged - 1 ) * $limit;

$scan_results = \AIDA\Includes\Error_Scanner::scan( array(
    'limit'           => $limit,
    'offset'          => $offset,
    'orderby'         => $orderby,
    'order'           => $order,
    'filter_severity' => $severity,
) );

$errors = $scan_results['items'];
$total_items = $scan_results['total'];
$total_pages = ceil( $total_items / $limit );

$base_url = admin_url( 'admin.php?page=aida-error-scanner' );
?>
<div class="wrap aida-admin-wrap">
    <h1>اسکن خطاهای سایت</h1>
    
    <div class="aida-toolbar">
        <div class="aida-filters">
            <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="page" value="aida-error-scanner" />
                
                <select name="severity">
                    <option value=""><?php _e( 'همه سطوح اهمیت', 'ai-debug-assistant' ); ?></option>
                    <option value="بحرانی" <?php selected( $severity, 'بحرانی' ); ?>><?php _e( 'بحرانی', 'ai-debug-assistant' ); ?></option>
                    <option value="متوسط" <?php selected( $severity, 'متوسط' ); ?>><?php _e( 'متوسط', 'ai-debug-assistant' ); ?></option>
                    <option value="کم" <?php selected( $severity, 'کم' ); ?>><?php _e( 'کم', 'ai-debug-assistant' ); ?></option>
                </select>

                <select name="orderby">
                    <option value="timestamp" <?php selected( $orderby, 'timestamp' ); ?>><?php _e( 'تاریخ', 'ai-debug-assistant' ); ?></option>
                    <option value="severity" <?php selected( $orderby, 'severity' ); ?>><?php _e( 'سطح اهمیت', 'ai-debug-assistant' ); ?></option>
                </select>

                <select name="order">
                    <option value="DESC" <?php selected( $order, 'DESC' ); ?>><?php _e( 'نزولی', 'ai-debug-assistant' ); ?></option>
                    <option value="ASC" <?php selected( $order, 'ASC' ); ?>><?php _e( 'صعودی', 'ai-debug-assistant' ); ?></option>
                </select>

                <button type="submit" class="button"><?php _e( 'فیلتر', 'ai-debug-assistant' ); ?></button>
            </form>
        </div>

        <div class="aida-pagination">
            <span class="displaying-num"><?php printf( '%s مورد', number_format_i18n( $total_items ) ); ?></span>
            <?php if ( $total_pages > 1 ) : ?>
                <a class="aida-page-link <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo $paged > 1 ? add_query_arg( 'paged', $paged - 1 ) : '#'; ?>">&laquo;</a>
                <?php for ( $i = 1; $i <= $total_pages; $i++ ) : ?>
                    <a class="aida-page-link <?php echo $i === $paged ? 'current' : ''; ?>" href="<?php echo add_query_arg( 'paged', $i ); ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a class="aida-page-link <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo $paged < $total_pages ? add_query_arg( 'paged', $paged + 1 ) : '#'; ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="aida-errors-container">
        <?php if ( empty( $errors ) ) : ?>
            <div class="aida-card" style="text-align: center;">
                <p><?php _e( 'هیچ خطایی با این مشخصات یافت نشد.', 'ai-debug-assistant' ); ?></p>
            </div>
        <?php else : ?>
            <?php foreach ( $errors as $error ) : ?>
                <div class="aida-error-item severity-<?php echo esc_attr( $error['severity'] ); ?>">
                    <div class="aida-error-header">
                        <div class="aida-error-meta">
                            <span class="aida-error-badge badge-<?php echo esc_attr( $error['severity'] ); ?>">
                                <?php echo esc_html( $error['type'] ); ?>
                            </span>
                            <span class="aida-timestamp">
                                <span class="dashicons dashicons-clock" style="font-size: 16px; margin-top: 2px;"></span>
                                <?php echo esc_html( $error['timestamp'] ); ?>
                            </span>
                        </div>
                        <div class="aida-severity-text">
                            اهمیت: <?php echo esc_html( $error['severity'] ); ?>
                        </div>
                    </div>
                    <div class="aida-error-body">
                        <span class="aida-error-message"><?php echo esc_html( $error['message'] ); ?></span>
                        <?php if ( ! empty( $error['file'] ) ) : ?>
                            <div class="aida-error-location">
                                <strong>فایل:</strong> <?php echo esc_html( $error['file'] ); ?> 
                                <strong>خط:</strong> <?php echo esc_html( $error['line'] ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
