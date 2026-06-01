jQuery(document).ready(function($) {
  var chatHistory = [];

  // =============================================
  // Diagnosis
  // =============================================
  $('#aida-run-diagnosis').on('click', function() {
    var $btn = $(this);
    var $loader = $('#aida-diagnosis-loader');
    var $result = $('#aida-diagnosis-result');

    $btn.prop('disabled', true);
    $loader.show();
    $result.hide();

    $.ajax({
      url: aida_ajax.ajax_url,
      type: 'POST',
      data: { action: 'aida_run_diagnosis', nonce: aida_ajax.nonce },
      success: function(response) {
        if (response.success) {
          var data = response.data;
          var html = '';
          if (data.source === 'rule_engine') {
            html += '<div class="aida-result-header">تشخیص هوشمند (محلی)</div>';
            html += '<div class="aida-ai-content">';
            html += '<p><strong>تشخیص:</strong> ' + data.diagnosis + '</p>';
            html += '<p><strong>علت:</strong> ' + data.cause + '</p>';
            html += '<p><strong>راه حل:</strong> ' + data.solution + '</p></div>';
          } else {
            html += '<div class="aida-result-header">تشخیص هوش مصنوعی</div>';
            html += '<div class="aida-ai-content">' + formatAIResponse(data.content) + '</div>';
          }
          $result.html(html).fadeIn();
        } else {
          alert('خطا: ' + response.data);
        }
      },
      error: function() { alert('خطا در برقراری ارتباط با سرور.'); },
      complete: function() { $btn.prop('disabled', false); $loader.hide(); }
    });
  });

  // =============================================
  // AI Chat — Full Conversational Interface
  // =============================================

  var $messages = $('#aida-chat-messages');
  var $input = $('#aida-chat-input');
  var $sendBtn = $('#aida-chat-send');
  var $clearBtn = $('#aida-chat-clear');
  var isSending = false;

  // Auto-resize textarea
  $input.on('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    $sendBtn.prop('disabled', !this.value.trim());
  });

  // Send on Enter (Shift+Enter for newline)
  $input.on('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });

  $sendBtn.on('click', sendMessage);

  // Suggestion clicks
  $(document).on('click', '.aida-chat-suggestion', function() {
    $input.val($(this).data('prompt')).trigger('input');
    sendMessage();
  });

  // Clear history
  $clearBtn.on('click', function() {
    if (chatHistory.length === 0) return;
    if (!confirm('آیا از پاک کردن تاریخچه گفتگو اطمینان دارید؟')) return;
    chatHistory = [];
    $messages.html(getWelcomeHTML());
    $input.val('').trigger('input');
  });

  function sendMessage() {
    var text = $input.val().trim();
    if (!text || isSending) return;

    addMessage('user', text);
    chatHistory.push({ role: 'user', content: text });
    $input.val('').trigger('input');
    isSending = true;

    showTyping();
    scrollToBottom();

    $.ajax({
      url: aida_ajax.ajax_url,
      type: 'POST',
      data: {
        action: 'aida_ask_ai',
        nonce: aida_ajax.nonce,
        problem: text,
        history: JSON.stringify(chatHistory)
      },
      success: function(response) {
        removeTyping();
        if (response.success) {
          var content = response.data;
          addMessage('ai', content);
          chatHistory.push({ role: 'assistant', content: content });
        } else {
          addMessage('ai', 'متأسفانه خطایی رخ داد: ' + response.data);
        }
      },
      error: function() {
        removeTyping();
        addMessage('ai', 'خطا در برقراری ارتباط با سرور. لطفاً دوباره تلاش کنید.');
      },
      complete: function() { isSending = false; scrollToBottom(); }
    });
  }

  function addMessage(role, content) {
    removeWelcome();
    var isUser = role === 'user';
    var avatar = isUser
      ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
      : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>';

    var html = '<div class="aida-msg aida-msg--' + role + '">'
      + '<div class="aida-msg-avatar">' + avatar + '</div>'
      + '<div class="aida-msg-bubble">'
      + '<div class="aida-msg-content">' + formatAIResponse(content) + '</div>'
      + '<div class="aida-msg-time">' + getCurrentTime() + '</div>'
      + '</div></div>';

    $messages.append(html);
    scrollToBottom();
  }

  function showTyping() {
    removeWelcome();
    if ($('.aida-typing-indicator').length) return;
    var html = '<div class="aida-msg aida-msg--ai aida-typing-indicator">'
      + '<div class="aida-msg-avatar">'
      + '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>'
      + '</div>'
      + '<div class="aida-msg-bubble"><div class="aida-typing-dots"><span></span><span></span><span></span></div></div>'
      + '</div>';
    $messages.append(html);
  }

  function removeTyping() {
    $('.aida-typing-indicator').remove();
  }

  function removeWelcome() {
    $('.aida-chat-welcome').remove();
  }

  function scrollToBottom() {
    var container = $messages[0];
    if (container) container.scrollTop = container.scrollHeight;
  }

  function getCurrentTime() {
    var now = new Date();
    return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
  }

  function getWelcomeHTML() {
    return '<div class="aida-chat-welcome">'
      + '<div class="aida-chat-welcome-icon">'
      + '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><circle cx="12" cy="8" r="0.5" fill="currentColor"/></svg>'
      + '</div>'
      + '<h3>دستیار هوشمند عیب‌یابی</h3>'
      + '<p>مشکل فنی سایت خود را در ادامه بنویسید. سیستم به طور خودکار اطلاعات فنی سرور و خطاهای اخیر را برای تحلیل دقیق‌تر ضمیمه می‌کند.</p>'
      + '<div class="aida-chat-suggestions">'
      + '<button class="aida-chat-suggestion" data-prompt="خطای 500 در صفحه اصلی ظاهر می‌شود">خطای 500 در صفحه اصلی</button>'
      + '<button class="aida-chat-suggestion" data-prompt="افزونه‌ها با هم تداخل دارند و سایت کند شده">کندی سایت و تداخل افزونه‌ها</button>'
      + '<button class="aida-chat-suggestion" data-prompt="خطای حافظه در وردپرس دارم">خطای حافظه (Memory Exhausted)</button>'
      + '<button class="aida-chat-suggestion" data-prompt="صفحه سفید مرگ (WSOD) نمایش داده می‌شود">صفحه سفید مرگ (WSOD)</button>'
      + '</div></div>';
  }

  // =============================================
  // Helper: Format AI Response with Markdown
  // =============================================
  function formatAIResponse(content) {
    if (!content) return '';
    var codeBlocks = [];
    var html = content.replace(/```(?:php|javascript|js|css|html)?([\s\S]*?)```/g, function(match, code) {
      var placeholder = '[[AIDA_CODE_BLOCK_' + codeBlocks.length + ']]';
      var escapedCode = code.trim()
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
      codeBlocks.push('<pre class="aida-code-block"><code>' + escapedCode + '</code></pre>');
      return placeholder;
    });
    html = html
      .replace(/### (.*)/g, '<h3>$1</h3>')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/`([^`]+)`/g, '<code>$1</code>')
      .replace(/^\d+\.\s+(.*)/gm, '<li>$1</li>')
      .replace(/^[-*]\s+(.*)/gm, '<li>$1</li>')
      .replace(/\n\n/g, '</p><p>')
      .replace(/\n/g, '<br>');
    if (html.includes('<li>')) {
      html = html.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
      html = html.replace(/<\/ul>\s*<ul>/g, '');
    }
    codeBlocks.forEach(function(block, index) {
      html = html.replace('[[AIDA_CODE_BLOCK_' + index + ']]', block);
    });
    return '<p>' + html + '</p>';
  }

  // =============================================
  // Tab Switching
  // =============================================
  $('.aida-tab-link').on('click', function(e) {
    e.preventDefault();
    var tabId = $(this).data('tab');
    $('.aida-tab-link').removeClass('active');
    $(this).addClass('active');
    $('.aida-tab-content').removeClass('active');
    $('#tab-' + tabId).addClass('active');
  });

  // =============================================
  // API Connection Test
  // =============================================
  $('#aida-test-connection').on('click', function() {
    var $btn = $(this);
    var $status = $('#aida-connection-status');
    var $dot = $status.find('.aida-status-dot');
    var $text = $status.find('.aida-status-text');
    var $globalStatus = $('#aida-global-status');
    var $globalDot = $globalStatus.find('.aida-status-dot');
    var $globalText = $globalStatus.find('span:last');
    var apiKey = $('input[name="aida_api_key"]').val();
    var baseUrl = $('input[name="aida_base_url"]').val();
    var model = $('input[name="aida_model"]').val();

    if (!apiKey) {
      $dot.removeClass('aida-status-dot--success aida-status-dot--loading aida-status-dot--inactive').addClass('aida-status-dot--error');
      $text.text('لطفا ابتدا کلید API را وارد کنید.');
      $status.addClass('aida-connection-status--error');
      return;
    }

    $btn.prop('disabled', true);
    $btn.html('<span class="aida-spinner"></span><span>در حال بررسی...</span>');
    $dot.removeClass('aida-status-dot--success aida-status-dot--error aida-status-dot--inactive').addClass('aida-status-dot--loading');
    $text.text('در حال بررسی اتصال...');
    $status.removeClass('aida-connection-status--success aida-connection-status--error');

    $.ajax({
      url: aida_ajax.ajax_url,
      type: 'POST',
      data: { action: 'aida_test_api_connection', nonce: aida_ajax.nonce, api_key: apiKey, base_url: baseUrl, model: model },
      success: function(response) {
        if (response.success) {
          $dot.removeClass('aida-status-dot--loading aida-status-dot--error aida-status-dot--inactive').addClass('aida-status-dot--success');
          $text.text('اتصال برقرار شد'); $status.addClass('aida-connection-status--success');
          $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--error aida-status-dot--loading').addClass('aida-status-dot--success');
          $globalText.text('متصل');
        } else {
          $dot.removeClass('aida-status-dot--loading aida-status-dot--success aida-status-dot--inactive').addClass('aida-status-dot--error');
          $text.text('خطا: ' + response.data); $status.addClass('aida-connection-status--error');
          $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--success aida-status-dot--loading').addClass('aida-status-dot--error');
          $globalText.text('خطا');
        }
      },
      error: function(xhr, status, error) {
        var errorMsg = 'خطا در برقراری ارتباط با سرور.';
        if (xhr.responseText) {
          try { var json = JSON.parse(xhr.responseText); errorMsg = json.data || 'پاسخ نامعتبر از سرور: ' + xhr.responseText.substring(0, 100); }
          catch(e) { errorMsg = 'خطای سرور (' + xhr.status + '): ' + (error || status); }
        } else if (status === 'timeout') { errorMsg = 'زمان درخواست به پایان رسید (Timeout).'; }
        $dot.removeClass('aida-status-dot--loading aida-status-dot--success aida-status-dot--inactive').addClass('aida-status-dot--error');
        $text.text(errorMsg); $status.addClass('aida-connection-status--error');
        $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--success aida-status-dot--loading').addClass('aida-status-dot--error');
        $globalText.text('خطا');
      },
      complete: function() {
        $btn.prop('disabled', false);
        $btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>بررسی اتصال به API</span>');
      }
    });
  });

  // Toggle API Key Visibility
  $('#aida-toggle-api-key').on('click', function() {
    var $input = $('#aida_api_key');
    var type = $input.attr('type') === 'password' ? 'text' : 'password';
    $input.attr('type', type);
  });
});