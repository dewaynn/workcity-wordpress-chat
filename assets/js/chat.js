jQuery(document).ready(function ($) {
    // DOM cache
    const $form = $('#workcity-chat-form');
    const $input = $('#chat-input');
    const $fileInput = $('#chat-file');
    const $messages = $('#chat-messages');
    const $typingIndicator = $('.typing-indicator');
    const $roleSelect = $('#recipient_role');
    const $userSelect = $('#recipient_user_id');
    const $chatBox = $('.workcity-chat-box');
    const $toggleMode = $('#toggle-mode');
    const $onlineStatus = $('#user-online-status');
    const nonce = $('#chat_nonce').val();
    const productId = workcity_chat_vars.product_id || 0;

    let typing = false;
    let typingTimeout;

    // Dark/Light Mode: load saved preference
    const savedMode = localStorage.getItem('workcity_chat_theme');
    if (savedMode === 'dark') {
        $chatBox.addClass('dark');
    }

    // Toggle Mode Button Click
    $toggleMode.on('click', function () {
        $chatBox.toggleClass('dark');
        const isDark = $chatBox.hasClass('dark');
        localStorage.setItem('workcity_chat_theme', isDark ? 'dark' : 'light');
    });

    // Load chat messages
    function loadChatHistory() {
        const recipientRole = $roleSelect.val();
        const recipientId = $userSelect.val();

        if (!recipientId) return;

        $.post(workcity_chat_vars.ajax_url, {
            action: 'load_chat_history',
            nonce: nonce,
            recipient_role: recipientRole,
            recipient_id: recipientId,
            product_id: productId
        }, function (response) {
            if (response.success) {
                $messages.empty();
                response.data.messages.forEach(msg => {
                    const isCurrentUser = msg.user_id === workcity_chat_vars.current_user_id;
                    const userClass = isCurrentUser ? 'chat-message user' : 'chat-message admin';
                    $messages.append(`
                        <div class="${userClass}">
                            ${msg.content}
                            <div class="chat-msg-date">${msg.date}</div>
                        </div>
                    `);
                });
                $messages.scrollTop($messages[0].scrollHeight);
            } else {
                $messages.html('<p>Error loading messages.</p>');
            }
        });
    }

    // Fetch users by role
    $roleSelect.on('change', function () {
        const role = $(this).val();

        $.post(workcity_chat_vars.ajax_url, {
            action: 'get_users_by_role',
            role: role,
            nonce: nonce
        }, function (response) {
            if (response.success) {
                $userSelect.empty().append('<option value="">Select a user</option>');
                response.data.forEach(user => {
                    $userSelect.append(`<option value="${user.id}">${user.name}</option>`);
                });
            } else {
                alert('Failed to load users.');
            }
        });

        $messages.empty();
        $onlineStatus.html('');
    });

    // Reload chat when user is selected
    $userSelect.on('change', function () {
        const userId = $(this).val();
        loadChatHistory();
        checkOnlineStatus(userId);
    });

    // Handle form submit (send message)
    $form.on('submit', function (e) {
        e.preventDefault();

        const message = $input.val().trim();
        const recipientRole = $roleSelect.val();
        const recipientId = $userSelect.val();

        if (!recipientId) {
            alert('Please select a user to chat with.');
            return;
        }

        if (!message && $fileInput[0].files.length === 0) return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('nonce', nonce);
        formData.append('message', message);
        formData.append('recipient_role', recipientRole);
        formData.append('recipient_id', recipientId);
        formData.append('product_id', productId);

        if ($fileInput[0].files.length > 0) {
            formData.append('chat_file', $fileInput[0].files[0]);
        }

        $.ajax({
            url: workcity_chat_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $messages.append(`
                        <div class="chat-message user">
                            ${response.data.message}
                            <div class="chat-msg-date">${new Date().toLocaleTimeString()}</div>
                        </div>
                    `);
                    $input.val('');
                    $fileInput.val('');
                    $messages.scrollTop($messages[0].scrollHeight);
                } else {
                    alert('Error sending message.');
                }
            }
        });
    });

    // Typing indicator
    $input.on('input', function () {
        if (!typing) {
            typing = true;
            $.post(workcity_chat_vars.ajax_url, {
                action: 'user_typing',
                nonce: nonce,
                is_typing: 'true',
                recipient_id: $userSelect.val()
            });
        }

        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(function () {
            typing = false;
            $.post(workcity_chat_vars.ajax_url, {
                action: 'user_typing',
                nonce: nonce,
                is_typing: 'false',
                recipient_id: $userSelect.val()
            });
        }, 3000);
    });

    // Poll for typing indicator
    setInterval(function () {
        const recipientId = $userSelect.val();
        if (!recipientId) return;

        $.post(workcity_chat_vars.ajax_url, {
            action: 'check_typing',
            nonce: nonce,
            recipient_id: recipientId
        }, function (response) {
            if (response.success && response.data.typing) {
                $typingIndicator.text(response.data.typing_user + ' is typing...');
            } else {
                $typingIndicator.text('');
            }
        });
    }, 3000);

    // Online status check
    function checkOnlineStatus(userId) {
        if (!userId) {
            $onlineStatus.text('');
            return;
        }

        $.post(workcity_chat_vars.ajax_url, {
            action: 'check_online_status',
            nonce: nonce,
            user_id: userId
        }, function (response) {
            if (response.success) {
                const online = response.data.online;
                const label = online
                    ? '<span style="color:green;">● Online</span>'
                    : '<span style="color:red;">● Offline</span>';
                $onlineStatus.html(label);
            }
        });
    }

    // Check online status every 15 seconds
    setInterval(function () {
        const userId = $userSelect.val();
        if (userId) {
            checkOnlineStatus(userId);
        }
    }, 15000);

    // Initial load
    if ($roleSelect.val()) {
        $roleSelect.trigger('change');
    }
});
