<?php
$conflicts_data = \AIDA\Includes\Conflict_Detector::detect();

function aida_paginate_array( $items, $per_page, $query_arg ) {
    $page = isset( $_GET[$query_arg] ) ? max( 1, intval( $_GET[$query_arg] ) ) : 1;
    $total = count( $items );
    $pages = ceil( $total / $per_page );
    $offset = ( $page - 1 ) * $per_page;
    return array(
        'items' => array_slice( $items, $offset, $per_page ),
        'current_page' => $page,
        'total_pages' => $pages,
        'total_items' => $total
    );
}

$per_page = 10;
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'hooks';

$paginated_hooks   = aida_paginate_array( $conflicts_data['hooks'], $per_page, 'p_hooks' );
$paginated_scripts = aida_paginate_array( $conflicts_data['scripts'], $per_page, 'p_scripts' );
$paginated_styles  = aida_paginate_array( $conflicts_data['styles'], $per_page, 'p_styles' );
$paginated_rest    = aida_paginate_array( $conflicts_data['rest'], $per_page, 'p_rest' );

function aida_render_modern_pagination( $paginated, $query_arg, $tab_id ) {
    if ( $paginated['total_pages'] <= 1 ) return;
    $current = $paginated['current_page'];
    $total = $paginated['total_pages'];
    $max_visible = 7;
    $half = floor(($max_visible - 1) / 2);
    $start = max(1, $current - $half);
    $end = min($total, $start + $max_visible - 1);
    if ($end - $start < $max_visible - 1) $start = max(1, $end - $max_visible + 1);

    echo '<div class="aida-pagination aida-pagination--modern aida-pagination--centered" style="margin-top:16px;">';
    if ($start > 1) echo '<span class="aida-page-btn disabled">...</span>';
    for ( $i = $start; $i <= $end; $i++ ) {
        $url = add_query_arg( array( 'tab' => $tab_id, $query_arg => $i ) );
        $class = ( $i == $current ) ? 'current' : '';
        echo '<a href="' . esc_url( $url ) . '" class="aida-page-btn ' . $class . '">' . $i . '</a>';
    }
    if ($end < $total) echo '<span class="aida-page-btn disabled">...</span>';
    echo '</div>';
}
?>
<div class="wrap aida-admin-wrap">
  <div class="aida-page-header">
    <div class="aida-page-header-content">
      <div class="aida-page-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <div>
        <h1>تحلیل تداخل افزونه‌ها</h1>
        <p class="aida-page-subtitle">بررسی هوک‌ها، اسکریپت‌ها، استایل‌ها و مسیرهای REST برای یافتن تداخلات</p>
      </div>
    </div>
    <div class="aida-stat-badge">
      <span class="aida-stat-number"><?php echo $paginated_hooks['total_items'] + $paginated_scripts['total_items'] + $paginated_styles['total_items'] + $paginated_rest['total_items']; ?></span>
      <span class="aida-stat-label">تداخل احتمالی</span>
    </div>
  </div>

  <div class="aida-tabs-modern">
    <a href="<?php echo add_query_arg( 'tab', 'hooks' ); ?>" class="aida-tab-modern <?php echo $active_tab === 'hooks' ? 'active' : ''; ?>" data-tab="hooks">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>
      </svg>
      <span>هوک‌ها</span>
      <span class="aida-tab-count"><?php echo $paginated_hooks['total_items']; ?></span>
    </a>
    <a href="<?php echo add_query_arg( 'tab', 'assets' ); ?>" class="aida-tab-modern <?php echo $active_tab === 'assets' ? 'active' : ''; ?>" data-tab="assets">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/>
        <polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/>
        <line x1="4" y1="4" x2="9" y2="9"/>
      </svg>
      <span>اسکریپت و استایل</span>
      <span class="aida-tab-count"><?php echo $paginated_scripts['total_items'] + $paginated_styles['total_items']; ?></span>
    </a>
    <a href="<?php echo add_query_arg( 'tab', 'jquery' ); ?>" class="aida-tab-modern <?php echo $active_tab === 'jquery' ? 'active' : ''; ?>" data-tab="jquery">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
      </svg>
      <span>jQuery</span>
      <span class="aida-tab-count"><?php echo count($conflicts_data['jquery']); ?></span>
    </a>
    <a href="<?php echo add_query_arg( 'tab', 'rest' ); ?>" class="aida-tab-modern <?php echo $active_tab === 'rest' ? 'active' : ''; ?>" data-tab="rest">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
      </svg>
      <span>REST</span>
      <span class="aida-tab-count"><?php echo $paginated_rest['total_items']; ?></span>
    </a>
  </div>

  <!-- Hooks Tab -->
  <div id="tab-hooks" class="aida-tab-content <?php echo $active_tab === 'hooks' ? 'active' : ''; ?>">
    <?php if ( empty( $paginated_hooks['items'] ) ) : ?>
      <div class="aida-card aida-card--empty">
        <div class="aida-card-body-center">
          <div class="aida-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
          <p>تداخل مشکوکی در هوک‌های اصلی یافت نشد.</p>
        </div>
      </div>
    <?php else : ?>
      <?php foreach ( $paginated_hooks['items'] as $item ) : ?>
        <div class="aida-conflict-card">
          <div class="aida-conflict-card-top">
            <div class="aida-conflict-card-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
              <span><?php echo esc_html( $item['hook'] ); ?></span>
            </div>
            <span class="aida-badge aida-badge--risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span>
          </div>
          <p class="aida-conflict-card-desc"><?php echo esc_html( $item['message'] ); ?></p>
          <div class="aida-conflict-card-sources">
            <?php foreach ( $item['sources'] as $source => $count ) : ?>
              <span class="aida-source-chip"><?php echo esc_html( $source ); ?> <em><?php echo $count; ?></em></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php aida_render_modern_pagination( $paginated_hooks, 'p_hooks', 'hooks' ); ?>
    <?php endif; ?>
  </div>

  <!-- Assets Tab -->
  <div id="tab-assets" class="aida-tab-content <?php echo $active_tab === 'assets' ? 'active' : ''; ?>">
    <div class="aida-card">
      <div class="aida-section-header">
        <div class="aida-section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/></svg>
        </div>
        <div>
          <h2>اسکریپت‌ها</h2>
          <p>اسکریپت‌های تکراری یا دارای تداخل</p>
        </div>
      </div>
      <?php if ( empty( $paginated_scripts['items'] ) ) : ?>
        <div class="aida-card-body-center"><p>اسکریپت تکراری یافت نشد.</p></div>
      <?php else : ?>
        <table class="aida-table-modern">
          <thead>
            <tr>
              <th>هندل</th>
              <th>وضعیت</th>
              <th>آدرس</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $paginated_scripts['items'] as $item ) : ?>
              <tr>
                <td><strong><?php echo esc_html( $item['handle'] ); ?></strong></td>
                <td><span class="aida-badge aida-badge--risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                <td><code class="aida-code-inline"><?php echo esc_html( basename($item['src']) ); ?></code></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php aida_render_modern_pagination( $paginated_scripts, 'p_scripts', 'assets' ); ?>
      <?php endif; ?>
    </div>

    <div class="aida-card" style="margin-top:20px;">
      <div class="aida-section-header">
        <div class="aida-section-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <div>
          <h2>استایل‌ها</h2>
          <p>استایل‌های تکراری یا دارای تداخل</p>
        </div>
      </div>
      <?php if ( empty( $paginated_styles['items'] ) ) : ?>
        <div class="aida-card-body-center"><p>استایل تکراری یافت نشد.</p></div>
      <?php else : ?>
        <table class="aida-table-modern">
          <thead>
            <tr>
              <th>هندل</th>
              <th>وضعیت</th>
              <th>آدرس</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $paginated_styles['items'] as $item ) : ?>
              <tr>
                <td><strong><?php echo esc_html( $item['handle'] ); ?></strong></td>
                <td><span class="aida-badge aida-badge--risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                <td><code class="aida-code-inline"><?php echo esc_html( basename($item['src']) ); ?></code></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php aida_render_modern_pagination( $paginated_styles, 'p_styles', 'assets' ); ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- jQuery Tab -->
  <div id="tab-jquery" class="aida-tab-content <?php echo $active_tab === 'jquery' ? 'active' : ''; ?>">
    <?php if ( empty( $conflicts_data['jquery'] ) ) : ?>
      <div class="aida-card aida-card--empty">
        <div class="aida-card-body-center">
          <div class="aida-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
          <p>تداخل در نسخه‌های jQuery یافت نشد.</p>
        </div>
      </div>
    <?php else : ?>
      <?php foreach ( $conflicts_data['jquery'] as $item ) : ?>
        <div class="aida-conflict-card">
          <div class="aida-conflict-card-top">
            <div class="aida-conflict-card-title">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
              <span>وضعیت jQuery</span>
            </div>
            <span class="aida-badge aida-badge--risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span>
          </div>
          <p class="aida-conflict-card-desc"><?php echo esc_html( $item['message'] ); ?></p>
          <div class="aida-conflict-card-sources">
            <?php foreach ( $item['handles'] as $handle ) : ?>
              <span class="aida-source-chip"><?php echo esc_html( $handle ); ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- REST Tab -->
  <div id="tab-rest" class="aida-tab-content <?php echo $active_tab === 'rest' ? 'active' : ''; ?>">
    <?php if ( empty( $paginated_rest['items'] ) ) : ?>
      <div class="aida-card aida-card--empty">
        <div class="aida-card-body-center">
          <div class="aida-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
          <p>تداخل در مسیرهای REST یافت نشد.</p>
        </div>
      </div>
    <?php else : ?>
      <div class="aida-card">
        <table class="aida-table-modern">
          <thead>
            <tr>
              <th>مسیر (Route)</th>
              <th>وضعیت</th>
              <th>تعداد هندلر</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $paginated_rest['items'] as $item ) : ?>
              <tr>
                <td><code class="aida-code-inline"><?php echo esc_html( $item['route'] ); ?></code></td>
                <td><span class="aida-badge aida-badge--risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                <td><?php echo esc_html( $item['count'] ); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php aida_render_modern_pagination( $paginated_rest, 'p_rest', 'rest' ); ?>
      </div>
    <?php endif; ?>
  </div>
</div>