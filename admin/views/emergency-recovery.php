<div class="wrap aida-admin-wrap">
    <h1>🆘 بازیابی اضطراری و حالت ایمن</h1>
    
    <div class="aida-card">
        <h2>اگر سایت شما به دلیل خطا بالا نمی‌آید:</h2>
        <p>دستیار هوشمند به گونه‌ای طراحی شده است که حتی در زمان بروز خطاهای بحرانی (Fatal Errors) که باعث سفید شدن صفحه یا عدم دسترسی به پیشخوان می‌شوند، به شما کمک کند.</p>
        
        <div style="background: #fff8f8; border-right: 4px solid #d63638; padding: 15px; margin: 20px 0;">
            <h3>۱. شناسایی خودکار خطا</h3>
            <p>اگر افزونه‌ای باعث ایجاد خطا شود، دستیار هوشمند به صورت خودکار یک صفحه بازیابی به شما نشان می‌دهد که در آن می‌توانید با یک کلیک آن افزونه را غیرفعال کنید.</p>
        </div>

        <div style="background: #f0f6fb; border-right: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
            <h3>۲. ورود دستی به حالت ایمن</h3>
            <p>اگر به هر دلیلی صفحه بازیابی خودکار ظاهر نشد، می‌توانید با افزودن عبارت زیر به انتهای آدرس سایت خود، وارد حالت ایمن شوید:</p>
            <code><?php echo home_url( '/?aida_recovery=1' ); ?></code>
            <p style="margin-top: 10px;">
                <a href="<?php echo home_url( '/?aida_recovery=1' ); ?>" target="_blank" class="button">تست ورود به حالت ایمن</a>
            </p>
        </div>

        <div style="background: #f0fff0; border-right: 4px solid #46b450; padding: 15px; margin: 20px 0;">
            <h3>نکات مهم:</h3>
            <ul>
                <li>فقط مدیران سایت (Administrator) به این صفحات دسترسی دارند.</li>
                <li>در حالت ایمن، می‌توانید لیست افزونه‌های فعال را ببینید و موارد مشکوک را غیرفعال کنید.</li>
                <li>پس از رفع مشکل، می‌توانید دوباره به پیشخوان وردپرس برگردید.</li>
            </ul>
        </div>
    </div>
</div>
