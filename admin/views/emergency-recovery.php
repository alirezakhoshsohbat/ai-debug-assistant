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
        <h1>بازیابی اضطراری و حالت ایمن</h1>
        <p class="aida-page-subtitle">ابزارهای نجات برای زمانی که سایت به دلیل خطا از دسترس خارج شده است</p>
      </div>
    </div>
  </div>

  <div class="aida-card">
    <div class="aida-section-header">
      <div class="aida-section-icon" style="background:#fef2f2;color:#dc2626;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <div>
        <h2>دسترسی اضطراری</h2>
        <p>اگر سایت شما به دلیل خطا بالا نمی‌آید، از روش‌های زیر استفاده کنید</p>
      </div>
    </div>

    <div class="aida-recovery-steps">
      <div class="aida-step-card aida-step-card--danger">
        <div class="aida-step-number">۱</div>
        <div class="aida-step-content">
          <h3>شناسایی خودکار خطا</h3>
          <p>اگر افزونه‌ای باعث ایجاد خطا شود، دستیار هوشمند به صورت خودکار یک صفحه بازیابی به شما نشان می‌دهد که در آن می‌توانید با یک کلیک آن افزونه را غیرفعال کنید.</p>
        </div>
      </div>

      <div class="aida-step-card aida-step-card--info">
        <div class="aida-step-number">۲</div>
        <div class="aida-step-content">
          <h3>ورود دستی به حالت ایمن</h3>
          <p>اگر به هر دلیلی صفحه بازیابی خودکار ظاهر نشد، می‌توانید با افزودن عبارت زیر به انتهای آدرس سایت خود، وارد حالت ایمن شوید:</p>
          <div class="aida-recovery-url">
            <code dir="ltr"><?php echo esc_url( home_url( '/?aida_recovery=1' ) ); ?></code>
            <a href="<?php echo esc_url( home_url( '/?aida_recovery=1' ) ); ?>" target="_blank" class="aida-btn aida-btn--outline aida-btn--sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              <span>تست حالت ایمن</span>
            </a>
          </div>
        </div>
      </div>

      <div class="aida-step-card aida-step-card--success">
        <div class="aida-step-number">۳</div>
        <div class="aida-step-content">
          <h3>نکات مهم</h3>
          <ul class="aida-checklist">
            <li>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>فقط مدیران سایت (Administrator) به این صفحات دسترسی دارند.</span>
            </li>
            <li>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>در حالت ایمن، می‌توانید لیست افزونه‌های فعال را ببینید و موارد مشکوک را غیرفعال کنید.</span>
            </li>
            <li>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>پس از رفع مشکل، می‌توانید دوباره به پیشخوان وردپرس برگردید.</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>