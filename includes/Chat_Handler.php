<?php

class Chat_Handler {

    public static function init() {
        // Register AJAX handler for logged-in users
        add_action('wp_ajax_send_message', [__CLASS__, 'send_message']);
    }

    public static function send_message() {
        check_ajax_referer('workcity_chat_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $sender_id    = get_current_user_id();
        $recipient_id = intval($_POST['recipient_id'] ?? 0);
        $message      = sanitize_text_field($_POST['message'] ?? '');
        $product_id   = intval($_POST['product_id'] ?? 0);

        if (!$recipient_id || empty($message)) {
            wp_send_json_error(['message' => 'Recipient or message missing']);
        }

        // Save chat as a custom post type
        $post_id = wp_insert_post([
            'post_type'   => 'chat_session',
            'post_status' => 'publish',
            'post_title'  => "Chat from $sender_id to $recipient_id",
            'meta_input'  => [
                'sender_id'    => $sender_id,
                'recipient_id' => $recipient_id,
                'message'      => $message,
                'product_id'   => $product_id,
                'timestamp'    => current_time('mysql'),
            ]
        ]);

        if (!$post_id) {
            wp_send_json_error(['message' => 'Failed to save chat']);
        }

        // Send email notification
        self::send_notification_email($recipient_id, $message);

        wp_send_json_success(['message' => $message]);
    }

    private static function send_notification_email($recipient_id, $message) {
        $recipient_user = get_user_by('ID', $recipient_id);

        if (!$recipient_user || !is_email($recipient_user->user_email)) {
            return;
        }

        $subject = '📩 New Message on Workcity Chat';
        $body = sprintf(
            "Hi %s,\n\nYou have received a new message:\n\n\"%s\"\n\nPlease log in to reply.\n\nThanks,\nWorkcity Team",
            $recipient_user->display_name,
            $message
        );

        wp_mail($recipient_user->user_email, $subject, $body);
    }
}
