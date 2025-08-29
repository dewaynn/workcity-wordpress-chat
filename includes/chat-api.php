<?php
add_action('rest_api_init', function () {
    register_rest_route('workcity/v1', '/send-message', [
        'methods'             => 'POST',
        'callback'            => 'workcity_rest_send_message',
        'permission_callback' => function () {
            return is_user_logged_in();
        },
        'args' => [
            'recipient_id' => ['validate_callback' => 'is_numeric'],
            'message'      => ['required' => true],
            'product_id'   => ['validate_callback' => 'is_numeric', 'default' => 0],
        ],
    ]);
});

function workcity_rest_send_message(WP_REST_Request $request) {
    $data         = $request->get_json_params();
    $sender_id    = get_current_user_id();
    $recipient_id = intval($data['recipient_id']);
    $message      = sanitize_text_field($data['message']);
    $product_id   = intval($data['product_id'] ?? 0);

    if (!$recipient_id || empty($message)) {
        return new WP_Error('invalid_data', 'Missing recipient or message', ['status' => 400]);
    }

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
        ],
    ]);

    if (is_wp_error($post_id)) {
        return new WP_Error('save_failed', 'Failed to save chat', ['status' => 500]);
    }

    // Send email
    $user = get_user_by('ID', $recipient_id);
    if ($user && is_email($user->user_email)) {
        wp_mail($user->user_email, 'New chat message', "You received a message:\n\n{$message}");
    }

    return rest_ensure_response(['success' => true, 'message_id' => $post_id]);
}
