<?php
// chat-cpt.php

function workcity_register_chat_cpt() {
    register_post_type('chat_session', [
        'label' => 'Chat Sessions',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
        'capability_type' => 'post',
        'has_archive' => false,
        'menu_position' => 25,
        'menu_icon' => 'dashicons-format-chat',
    ]);
}
add_action('init', 'workcity_register_chat_cpt');
