<?php
class Chat_Session {

    /**
     * Get or create a chat session post between two users (and optional product context)
     * Returns the post ID of the chat session
     */
    public static function get_or_create_session($user_1_id, $user_2_id, $product_id = 0) {
        // Create a unique session key based on user IDs and product
        $session_key = 'chat_session_' . min($user_1_id, $user_2_id) . '_' . max($user_1_id, $user_2_id) . '_' . intval($product_id);

        // Try to find an existing post with this meta key
        $args = [
            'post_type'  => 'chat_session',
            'meta_query' => [
                [
                    'key'     => 'chat_session_key',
                    'value'   => $session_key,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        ];

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return $query->posts[0]->ID;
        }

        // If not found, create new chat session post
        $post_data = [
            'post_title'  => 'Chat between User ' . $user_1_id . ' and User ' . $user_2_id,
            'post_type'   => 'chat_session',
            'post_status' => 'publish',
        ];

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            // Save session key and user IDs & product ID as meta
            update_post_meta($post_id, 'chat_session_key', $session_key);
            update_post_meta($post_id, 'user_1_id', $user_1_id);
            update_post_meta($post_id, 'user_2_id', $user_2_id);
            update_post_meta($post_id, 'product_id', $product_id);
        }

        return $post_id;
    }

    /**
     * Register custom post type 'chat_session'
     */
    public static function register_post_type() {
        $labels = [
            'name'               => __('Chat Sessions'),
            'singular_name'      => __('Chat Session'),
            'menu_name'          => __('Chat Sessions'),
            'name_admin_bar'     => __('Chat Session'),
            'add_new'            => __('Add New'),
            'add_new_item'       => __('Add New Chat Session'),
            'new_item'           => __('New Chat Session'),
            'edit_item'          => __('Edit Chat Session'),
            'view_item'          => __('View Chat Session'),
            'all_items'          => __('All Chat Sessions'),
            'search_items'       => __('Search Chat Sessions'),
            'not_found'          => __('No chat sessions found.'),
            'not_found_in_trash' => __('No chat sessions found in Trash.'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'capability_type'    => 'post',
            'supports'           => ['comments', 'custom-fields'],
            'exclude_from_search'=> true,
            'publicly_queryable' => false,
            'has_archive'        => false,
        ];

        register_post_type('chat_session', $args);
    }

    /**
     * Initialize the class (called from plugin main file)
     */
    public static function init() {
        // Currently only the post type registration
        add_action('init', [__CLASS__, 'register_post_type']);
    }
}

// Call init directly here to ensure the post type is registered early
Chat_Session::init();
