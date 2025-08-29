<?php
class Chat_API {
    public static function init() {
        add_action('rest_api_init', function () {
            register_rest_route('workcity-chat/v1', '/messages/(?P<session_id>\d+)', [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_messages'],
                'permission_callback' => '__return_true',
            ]);
        });
    }

    public static function get_messages($request) {
        $session_id = $request['session_id'];
        // Fetch and return messages for this session
        return new WP_REST_Response(['messages' => []], 200);
    }
}
