jQuery(document).ready(function($) {
    // Run Diagnosis
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
            data: {
                action: 'aida_run_diagnosis',
                nonce: aida_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var html = '';

                    if (data.source === 'rule_engine') {
                        html += '<div class="aida-result-header">تشخیص هوشمند (محلی)</div>';
                        html += '<div class="aida-ai-content">';
                        html += '<p><strong>تشخیص:</strong> ' + data.diagnosis + '</p>';
                        html += '<p><strong>علت:</strong> ' + data.cause + '</p>';
                        html += '<p><strong>راه حل:</strong> ' + data.solution + '</p>';
                        html += '</div>';
                    } else {
                        html += '<div class="aida-result-header">تشخیص هوش مصنوعی</div>';
                        html += '<div class="aida-ai-content">' + formatAIResponse(data.content) + '</div>';
                    }

                    $result.html(html).fadeIn();
                } else {
                    alert('خطا: ' + response.data);
                }
            },
            error: function() {
                alert('خطا در برقراری ارتباط با سرور.');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loader.hide();
            }
        });
    });

    // Ask AI
    $('#aida-ask-ai-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('button');
        var $loader = $('#aida-ask-loader');
        var $result = $('#aida-ask-result');
        var problem = $('#aida-problem-desc').val();

        if (!problem) return;

        $btn.prop('disabled', true);
        $loader.show();
        $result.hide();

        $.ajax({
            url: aida_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'aida_ask_ai',
                nonce: aida_ajax.nonce,
                problem: problem
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="aida-result-header">پاسخ دستیار هوشمند</div>';
                    html += '<div class="aida-ai-content">' + formatAIResponse(response.data) + '</div>';
                    $result.html(html).fadeIn();
                } else {
                    alert('خطا: ' + response.data);
                }
            },
            error: function() {
                alert('خطا در برقراری ارتباط با سرور.');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $loader.hide();
            }
        });
    });

    function formatAIResponse(content) {
        if (!content) return '';
        
        // 1. Handle Code Blocks (Triple backticks) FIRST to protect their content
        var codeBlocks = [];
        var html = content.replace(/```(?:php|javascript|js|css|html)?([\s\S]*?)```/g, function(match, code) {
            var placeholder = '[[AIDA_CODE_BLOCK_' + codeBlocks.length + ']]';
            // Escape HTML tags inside code
            var escapedCode = code.trim()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;");
            codeBlocks.push('<pre class="aida-code-block"><code>' + escapedCode + '</code></pre>');
            return placeholder;
        });

        // 2. Convert Markdown-like syntax to HTML
        html = html
            .replace(/### (.*)/g, '<h3>$1</h3>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/`([^`]+)`/g, '<code>$1</code>') // Inline code
            .replace(/^\d+\.\s+(.*)/gm, '<li>$1</li>')
            .replace(/^[-*]\s+(.*)/gm, '<li>$1</li>')
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>');

        // 3. Wrap lists
        if (html.includes('<li>')) {
            html = html.replace(/(<li>[\s\S]*?<\/li>)/g, '<ul>$1</ul>');
            html = html.replace(/<\/ul>\s*<ul>/g, '');
        }

        // 4. Restore Code Blocks
        codeBlocks.forEach(function(block, index) {
            html = html.replace('[[AIDA_CODE_BLOCK_' + index + ']]', block);
        });

        return '<p>' + html + '</p>';
    }

    // Tab Switching for Conflict Detector
    $('.aida-tab-link').on('click', function(e) {
        e.preventDefault();
        var tabId = $(this).data('tab');

        $('.aida-tab-link').removeClass('active');
        $(this).addClass('active');

        $('.aida-tab-content').removeClass('active');
        $('#tab-' + tabId).addClass('active');
    });

    // Toggle API Key Visibility
    $('#aida-toggle-api-key').on('click', function() {
        var $input = $('#aida_api_key');
        var type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('aida-input-toggle--visible');
    });

    // Test API Connection
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
            data: {
                action: 'aida_test_api_connection',
                nonce: aida_ajax.nonce,
                api_key: apiKey,
                base_url: baseUrl,
                model: model
            },
            success: function(response) {
                if (response.success) {
                    $dot.removeClass('aida-status-dot--loading aida-status-dot--error aida-status-dot--inactive').addClass('aida-status-dot--success');
                    $text.text('اتصال برقرار شد');
                    $status.addClass('aida-connection-status--success');

                    $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--error aida-status-dot--loading').addClass('aida-status-dot--success');
                    $globalText.text('متصل');
                } else {
                    $dot.removeClass('aida-status-dot--loading aida-status-dot--success aida-status-dot--inactive').addClass('aida-status-dot--error');
                    $text.text('خطا: ' + response.data);
                    $status.addClass('aida-connection-status--error');

                    $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--success aida-status-dot--loading').addClass('aida-status-dot--error');
                    $globalText.text('خطا');
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'خطا در برقراری ارتباط با سرور.';

                if (xhr.responseText) {
                    try {
                        var json = JSON.parse(xhr.responseText);
                        if (json.data) {
                            errorMsg = json.data;
                        } else {
                            errorMsg = 'پاسخ نامعتبر از سرور: ' + xhr.responseText.substring(0, 100);
                        }
                    } catch(e) {
                        errorMsg = 'خطای سرور (' + xhr.status + '): ' + (error || status);
                        if (xhr.responseText && xhr.status === 500) {
                            errorMsg += ' - لطفا لاگ‌های سرور را بررسی کنید.';
                        }
                    }
                } else if (status === 'timeout') {
                    errorMsg = 'زمان درخواست به پایان رسید (Timeout).';
                }

                $dot.removeClass('aida-status-dot--loading aida-status-dot--success aida-status-dot--inactive').addClass('aida-status-dot--error');
                $text.text(errorMsg);
                $status.addClass('aida-connection-status--error');

                $globalDot.removeClass('aida-status-dot--inactive aida-status-dot--success aida-status-dot--loading').addClass('aida-status-dot--error');
                $globalText.text('خطا');
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btn.html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>بررسی اتصال به API</span>');
            }
        });
    });
});
