<?php
$conflicts_data = \AIDA\Includes\Conflict_Detector::detect();

// Pagination Helper
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

function aida_render_pagination( $paginated, $query_arg, $tab_id ) {
    if ( $paginated['total_pages'] <= 1 ) return;
    echo '<div class="aida-pagination-mini">';
    for ( $i = 1; $i <= $paginated['total_pages']; $i++ ) {
        $url = add_query_arg( array( 'tab' => $tab_id, $query_arg => $i ) );
        $class = ( $i == $paginated['current_page'] ) ? 'current' : '';
        echo '<a href="' . esc_url( $url ) . '" class="aida-page-btn ' . $class . '">' . $i . '</a>';
    }
    echo '</div>';
}
?>
<div class="wrap aida-admin-wrap">
    <h1>تحلیل تداخل افزونه‌ها</h1>
    
    <div class="aida-conflict-tabs">
        <a href="#" class="aida-tab-link <?php echo $active_tab === 'hooks' ? 'active' : ''; ?>" data-tab="hooks">هوک‌ها (<?php echo $paginated_hooks['total_items']; ?>)</a>
        <a href="#" class="aida-tab-link <?php echo $active_tab === 'assets' ? 'active' : ''; ?>" data-tab="assets">اسکریپت و استایل (<?php echo $paginated_scripts['total_items'] + $paginated_styles['total_items']; ?>)</a>
        <a href="#" class="aida-tab-link <?php echo $active_tab === 'jquery' ? 'active' : ''; ?>" data-tab="jquery">تداخل jQuery (<?php echo count($conflicts_data['jquery']); ?>)</a>
        <a href="#" class="aida-tab-link <?php echo $active_tab === 'rest' ? 'active' : ''; ?>" data-tab="rest">مسیرهای REST (<?php echo $paginated_rest['total_items']; ?>)</a>
    </div>

    <!-- Hooks Tab -->
    <div id="tab-hooks" class="aida-tab-content <?php echo $active_tab === 'hooks' ? 'active' : ''; ?>">
        <?php if ( empty( $paginated_hooks['items'] ) ) : ?>
            <div class="aida-card"><p>تداخل مشکوکی در هوک‌های اصلی یافت نشد.</p></div>
        <?php else : ?>
            <?php foreach ( $paginated_hooks['items'] as $item ) : ?>
                <div class="aida-conflict-item">
                    <div class="aida-conflict-meta">
                        <strong>هوک: <?php echo esc_html( $item['hook'] ); ?></strong>
                        <span class="risk-badge risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span>
                    </div>
                    <p><?php echo esc_html( $item['message'] ); ?></p>
                    <div class="aida-sources-list">
                        <?php foreach ( $item['sources'] as $source => $count ) : ?>
                            <span class="aida-source-tag"><?php echo esc_html( $source ); ?> (<?php echo $count; ?>)</span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php aida_render_pagination( $paginated_hooks, 'p_hooks', 'hooks' ); ?>
        <?php endif; ?>
    </div>

    <!-- Assets Tab -->
    <div id="tab-assets" class="aida-tab-content <?php echo $active_tab === 'assets' ? 'active' : ''; ?>">
        <h3>اسکریپت‌ها</h3>
        <?php if ( empty( $paginated_scripts['items'] ) ) : ?>
            <p>اسکریپت تکراری یافت نشد.</p>
        <?php else : ?>
            <table class="aida-compact-table">
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
                            <td><span class="risk-badge risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                            <td><code><?php echo esc_html( basename($item['src']) ); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php aida_render_pagination( $paginated_scripts, 'p_scripts', 'assets' ); ?>
        <?php endif; ?>

        <div style="margin-top: 20px;"></div>
        <h3>استایل‌ها</h3>
        <?php if ( empty( $paginated_styles['items'] ) ) : ?>
            <p>استایل تکراری یافت نشد.</p>
        <?php else : ?>
            <table class="aida-compact-table">
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
                            <td><span class="risk-badge risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                            <td><code><?php echo esc_html( basename($item['src']) ); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php aida_render_pagination( $paginated_styles, 'p_styles', 'assets' ); ?>
        <?php endif; ?>
    </div>

    <!-- jQuery Tab -->
    <div id="tab-jquery" class="aida-tab-content <?php echo $active_tab === 'jquery' ? 'active' : ''; ?>">
        <?php if ( empty( $conflicts_data['jquery'] ) ) : ?>
            <div class="aida-card"><p>تداخل در نسخه‌های jQuery یافت نشد.</p></div>
        <?php else : ?>
            <?php foreach ( $conflicts_data['jquery'] as $item ) : ?>
                <div class="aida-conflict-item">
                    <div class="aida-conflict-meta">
                        <strong>وضعیت jQuery</strong>
                        <span class="risk-badge risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span>
                    </div>
                    <p><?php echo esc_html( $item['message'] ); ?></p>
                    <div class="aida-sources-list">
                        <?php echo implode( ', ', array_map( 'esc_html', $item['handles'] ) ); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- REST Tab -->
    <div id="tab-rest" class="aida-tab-content <?php echo $active_tab === 'rest' ? 'active' : ''; ?>">
        <?php if ( empty( $paginated_rest['items'] ) ) : ?>
            <div class="aida-card"><p>تداخل در مسیرهای REST یافت نشد.</p></div>
        <?php else : ?>
            <table class="aida-compact-table">
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
                            <td><code><?php echo esc_html( $item['route'] ); ?></code></td>
                            <td><span class="risk-badge risk-<?php echo esc_attr( $item['risk'] ); ?>"><?php echo esc_html( $item['risk'] ); ?></span></td>
                            <td><?php echo esc_html( $item['count'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php aida_render_pagination( $paginated_rest, 'p_rest', 'rest' ); ?>
        <?php endif; ?>
    </div>
</div>
