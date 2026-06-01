<div class="wrap aida-admin-wrap">
  <div class="aida-page-header">
    <div class="aida-page-header-content">
      <div class="aida-page-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          <line x1="12" y1="8" x2="12" y2="14"/>
          <line x1="9" y1="11" x2="15" y2="11"/>
        </svg>
      </div>
      <div>
        <h1>پرسش از هوش مصنوعی</h1>
        <p class="aida-page-subtitle">مشکل خود را مطرح کنید تا با تحلیل اطلاعات فنی سایت، راهکار دریافت کنید</p>
      </div>
    </div>
    <div class="aida-chat-header-actions">
      <button type="button" id="aida-chat-clear" class="aida-btn aida-btn--outline aida-btn--sm" title="پاک کردن تاریخچه">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </svg>
        <span>پاک کردن تاریخچه</span>
      </button>
    </div>
  </div>

  <div class="aida-chat-container">
    <div class="aida-chat-messages" id="aida-chat-messages">
      <div class="aida-chat-welcome">
        <div class="aida-chat-welcome-icon">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/>
          </svg>
        </div>
        <h3>دستیار هوشمند عیب‌یابی</h3>
        <p>مشکل فنی سایت خود را در ادامه بنویسید. سیستم به طور خودکار اطلاعات فنی سرور و خطاهای اخیر را برای تحلیل دقیق‌تر ضمیمه می‌کند.</p>
        <div class="aida-chat-suggestions">
          <button class="aida-chat-suggestion" data-prompt="خطای 500 در صفحه اصلی ظاهر می‌شود">
            خطای 500 در صفحه اصلی
          </button>
          <button class="aida-chat-suggestion" data-prompt="افزونه‌ها با هم تداخل دارند و سایت کند شده">
            کندی سایت و تداخل افزونه‌ها
          </button>
          <button class="aida-chat-suggestion" data-prompt="خطای حافظه در وردپرس دارم">
            خطای حافظه (Memory Exhausted)
          </button>
          <button class="aida-chat-suggestion" data-prompt="صفحه سفید مرگ (WSOD) نمایش داده می‌شود">
            صفحه سفید مرگ (WSOD)
          </button>
        </div>
      </div>
    </div>

    <div class="aida-chat-input-bar">
      <div class="aida-chat-input-wrapper">
        <textarea id="aida-chat-input" class="aida-chat-input" rows="1" placeholder="مشکل خود را بنویسید ..."></textarea>
        <button type="button" id="aida-chat-send" class="aida-chat-send-btn" disabled aria-label="ارسال">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
        </button>
      </div>
      <p class="aida-chat-footer-note">پاسخ‌ها توسط هوش مصنوعی تولید می‌شوند و ممکن است نیاز به بررسی داشته باشند</p>
    </div>
  </div>
</div>