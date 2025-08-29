<?php
class Chat_AJAX {

    public static function init() {
        add_action('wp_ajax_send_message', [__CLASS__, 'handle_send_message']);
        add_action('wp_ajax_load_chat_history', [__CLASS__, 'load_chat_history']);
        add_action('wp_ajax_user_typing', [__CLASS__, 'handle_typing']);
        add_action('wp_ajax_check_typing', [__CLASS__, 'check_typing']);
        add_action('wp_ajax_get_users_by_role', [__CLASS__, 'get_users_by_role']);
    }

    public static function get_users_by_role() {
    check_ajax_referer('workcity_chat_nonce', 'nonce');

    if (!current_user_can('read')) {
        wp_send_json_error('Unauthorized');
    }

    $role = sanitize_text_field($_POST['role']);
    if (!$role) {
        wp_send_json_error('Missing role');
    }

    $users = get_users(['role' => $role]);
    $result = [];

    foreach ($users as $user) {
        $result[] = [
            'id' => $user->ID,
            'name' => $user->display_name
        ];
    }

    wp_send_json_success($result);
}


    /**
     * Handle sending a chat message via AJAX
     */
    public static function handle_send_message() {
         error_log("SEND_MESSAGE START"); // TOP
        check_ajax_referer('workcity_chat_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        $user = wp_get_current_user();
        $message = sanitize_text_field($_POST['message']);
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $recipient_role = isset($_POST['recipient_role']) ? sanitize_text_field($_POST['recipient_role']) : '';

        // Find a recipient with the selected role
        $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
if (!$recipient_id || $recipient_id === $user->ID) {
    wp_send_json_error('Invalid recipient.');
}

        // Load Chat Session Handler (assumes you have this class)
        require_once plugin_dir_path(__FILE__) . '/class-chat-session.php';
        $user_1 = min($user->ID, $recipient_id);
        $user_2 = max($user->ID, $recipient_id);
        $chat_post_id = Chat_Session::get_or_create_session($user_1, $user_2, $product_id);

        // Handle file upload if exists
        $attachment_url = '';
        if (!empty($_FILES['chat_file']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $file = $_FILES['chat_file'];
            $upload_overrides = ['test_form' => false];

            $movefile = wp_handle_upload($file, $upload_overrides);
            if ($movefile && !isset($movefile['error'])) {
                // Insert into media library
                $attachment = [
                    'post_mime_type' => $movefile['type'],
                    'post_title'     => sanitize_file_name($file['name']),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ];

                $attach_id = wp_insert_attachment($attachment, $movefile['file']);
                if (!is_wp_error($attach_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                    wp_update_attachment_metadata($attach_id, $attach_data);

                    $attachment_url = wp_get_attachment_url($attach_id);
                    // Append file link to message
                    $message .= "\nFile: <a href='" . esc_url($attachment_url) . "' target='_blank'>Download</a>";
                }
            }
        }

        // Save the message as a comment on the chat session post
        wp_insert_comment([
            'comment_post_ID' => $chat_post_id,
            'comment_content' => $message,
            'user_id' => $user->ID,
            'comment_approved' => 1,
        ]);

        // Store product reference if any
        if ($product_id) {
            update_post_meta($chat_post_id, 'product_id', $product_id);
        }

        // Email notification (optional)
        $to = get_userdata($recipient_id)->user_email;
        $subject = 'New Chat Message from ' . $user->display_name;
        $product = $product_id ? wc_get_product($product_id) : null;
        $product_info = $product ? "\nProduct: " . $product->get_name() : '';
        $body = "Message:\n$message\n\nFrom: {$user->display_name} ({$user->user_email})$product_info";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        wp_mail($to, $subject, $body, $headers);

        wp_send_json_success(['message' => $message]);
    }


    /**
     * Load chat message history
     */
    public static function load_chat_history() {
        check_ajax_referer('workcity_chat_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        $user = wp_get_current_user();
        $recipient_role = isset($_POST['recipient_role']) ? sanitize_text_field($_POST['recipient_role']) : '';
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

        // Find recipient user by role
        $users = get_users([
            'role' => $recipient_role,
            'number' => 1
        ]);
        $recipient_id = isset($users[0]) ? $users[0]->ID : 1; // fallback to admin

        // Load Chat Session Handler
        require_once plugin_dir_path(__FILE__) . '/class-chat-session.php';
        $user_1 = min($user->ID, $recipient_id);
        $user_2 = max($user->ID, $recipient_id);
        $chat_post_id = Chat_Session::get_or_create_session($user_1, $user_2, $product_id);

        // Fetch comments (chat messages)
        $comments = get_comments([
            'post_id' => $chat_post_id,
            'status' => 'approve',
            'order' => 'ASC',
        ]);

        $messages = [];
        foreach ($comments as $comment) {
            $messages[] = [
                'content' => wp_kses_post($comment->comment_content),
                'user_id' => $comment->user_id,
                'date' => $comment->comment_date,
            ];
        }

        wp_send_json_success(['messages' => $messages]);
    }

    /**
     * Handle typing indicator — store transient
     */
    public static function handle_typing() {
        check_ajax_referer('workcity_chat_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        $is_typing = isset($_POST['is_typing']) && $_POST['is_typing'] === 'true';
        $user_id = get_current_user_id();

        set_transient("chat_typing_{$user_id}", $is_typing, 5);

        wp_send_json_success();
    }

    /**
     * Check if the other user is typing
     */
    public static function check_typing() {
        check_ajax_referer('workcity_chat_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Unauthorized');
        }

        // For demo, get the first user in the recipient role, or admin fallback
        $current_user = wp_get_current_user();

        // This is a placeholder. You might want to check who the recipient is based on session
        $other_user_id = 1; // Admin user ID or other user

        $typing = get_transient("chat_typing_{$other_user_id}");

        wp_send_json_success(['typing' => (bool) $typing]);
    }
}

// Initialize AJAX handlers
Chat_AJAX::init();
