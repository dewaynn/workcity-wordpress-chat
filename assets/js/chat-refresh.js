jQuery(document).ready(function ($) {
    function refreshChatSessions() {
        $.ajax({
            url: chatRefreshData.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'refresh_chat_sessions',
                nonce: chatRefreshData.nonce
            },
            success: function (response) {
                if (response.success && response.data.html) {
                    $('#chat-sessions-container').html(response.data.html);
                }
            }
        });
    }

    // Refresh every 5 seconds
    setInterval(refreshChatSessions, 5000);
});
