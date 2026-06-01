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
?>
<div class="wrap aida-admin-wrap">
  <div class="aida-page-header">
    <div class="aida-page-header-content">
      <div class="aida-page-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
          <line x1="12" y1="9" x2="12" y2="13"/>
          <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
      </div>
      <div>
        <h1>اسکن خطاهای سایت</h1>
        <p class="aida-page-subtitle">مشاهده و بررسی خطاها و هشدارهای ثبت‌شده در سایت</p>
      </div>
    </div>
    <div class="aida-stat-badge">
      <span class="aida-stat-number"><?php echo number_format_i18n( $total_items ); ?></span>
      <span class="aida-stat-label">خطا یافت شد</span>
    </div>
  </div>

  <div class="aida-toolbar aida-toolbar--modern">
    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="aida-filter-form">
      <input type="hidden" name="page" value="aida-error-scanner" />
      
      <div class="aida-filter-group">
        <select name="severity" class="aida-select">
          <option value="">همه سطوح</option>
          <option value="بحرانی" <?php selected( $severity, 'بحرانی' ); ?>>بحرانی</option>
          <option value="متوسط" <?php selected( $severity, 'متوسط' ); ?>>متوسط</option>
          <option value="کم" <?php selected( $severity, 'کم' ); ?>>کم</option>
        </select>

        <select name="orderby" class="aida-select">
          <option value="timestamp" <?php selected( $orderby, 'timestamp' ); ?>>بر اساس تاریخ</option>
          <option value="severity" <?php selected( $orderby, 'severity' ); ?>>بر اساس اهمیت</option>
        </select>

        <select name="order" class="aida-select">
          <option value="DESC" <?php selected( $order, 'DESC' ); ?>>نزولی</option>
          <option value="ASC" <?php selected( $order, 'ASC' ); ?>>صعودی</option>
        </select>

        <button type="submit" class="aida-btn aida-btn--outline aida-btn--sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 22 7 14 11 14 21 10 17 10 11 2 7 2 3 22 3"/>
          </svg>
          <span>فیلتر</span>
        </button>
      </div>
    </form>

    <?php if ( $total_pages > 1 ) : ?>
      <div class="aida-pagination aida-pagination--modern">
        <a class="aida-page-btn <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo $paged > 1 ? add_query_arg( 'paged', $paged - 1 ) : '#'; ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <?php
        $max_visible = 7;
        $half = floor(($max_visible - 1) / 2);
        $start = max(1, $paged - $half);
        $end = min($total_pages, $start + $max_visible - 1);
        if ($end - $start < $max_visible - 1) $start = max(1, $end - $max_visible + 1);
        if ($start > 1) echo '<a class="aida-page-btn disabled">...</a>';
        for ( $i = $start; $i <= $end; $i++ ) : ?>
          <a class="aida-page-btn <?php echo $i === $paged ? 'current' : ''; ?>" href="<?php echo add_query_arg( 'paged', $i ); ?>"><?php echo $i; ?></a>
        <?php endfor;
        if ($end < $total_pages) echo '<a class="aida-page-btn disabled">...</a>'; ?>
        <a class="aida-page-btn <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo $paged < $total_pages ? add_query_arg( 'paged', $paged + 1 ) : '#'; ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( empty( $errors ) ) : ?>
    <div class="aida-card aida-card--empty">
      <div class="aida-card-body-center">
        <div class="aida-empty-icon">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <p>هیچ خطایی با این مشخصات یافت نشد.</p>
      </div>
    </div>
  <?php else : ?>
    <div class="aida-errors-list">
      <?php foreach ( $errors as $error ) : ?>
        <div class="aida-error-card severity-<?php echo esc_attr( $error['severity'] ); ?>">
          <div class="aida-error-card-header">
            <div class="aida-error-card-meta">
              <span class="aida-badge aida-badge--<?php echo esc_attr( $error['severity'] ); ?>">
                <?php echo esc_html( $error['type'] ); ?>
              </span>
              <span class="aida-error-time">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <?php echo esc_html( $error['timestamp'] ); ?>
              </span>
            </div>
            <span class="aida-severity-label aida-severity-label--<?php echo esc_attr( $error['severity'] ); ?>">
              <?php echo esc_html( $error['severity'] ); ?>
            </span>
          </div>
          <div class="aida-error-card-body">
            <p class="aida-error-message"><?php echo esc_html( $error['message'] ); ?></p>
            <?php if ( ! empty( $error['file'] ) ) : ?>
              <div class="aida-error-location">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span><strong>فایل:</strong> <?php echo esc_html( $error['file'] ); ?> <strong>خط:</strong> <?php echo esc_html( $error['line'] ); ?></span>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ( $total_pages > 1 ) : ?>
      <div class="aida-pagination aida-pagination--modern aida-pagination--centered">
        <a class="aida-page-btn <?php echo $paged <= 1 ? 'disabled' : ''; ?>" href="<?php echo $paged > 1 ? add_query_arg( 'paged', $paged - 1 ) : '#'; ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <?php
        $max_visible = 7;
        $half = floor(($max_visible - 1) / 2);
        $start = max(1, $paged - $half);
        $end = min($total_pages, $start + $max_visible - 1);
        if ($end - $start < $max_visible - 1) $start = max(1, $end - $max_visible + 1);
        if ($start > 1) echo '<span class="aida-page-btn disabled">...</span>';
        for ( $i = $start; $i <= $end; $i++ ) : ?>
          <a class="aida-page-btn <?php echo $i === $paged ? 'current' : ''; ?>" href="<?php echo add_query_arg( 'paged', $i ); ?>"><?php echo $i; ?></a>
        <?php endfor;
        if ($end < $total_pages) echo '<span class="aida-page-btn disabled">...</span>'; ?>
        <a class="aida-page-btn <?php echo $paged >= $total_pages ? 'disabled' : ''; ?>" href="<?php echo $paged < $total_pages ? add_query_arg( 'paged', $paged + 1 ) : '#'; ?>">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>