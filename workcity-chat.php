<?php
/*
Plugin Name: WorkCity Chat System
Description: A real-time, role-based chat plugin with WooCommerce integration.
Author: Godwin Aifuwa
*/

defined('ABSPATH') || exit;

// Include necessary classes
require_once plugin_dir_path(__FILE__) . 'includes/class-chat-session.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-chat-widget.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-chat-ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-chat-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/chat-cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/chat-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/Chat_Handler.php';

// Register Custom Post Type for Chat Session
function register_chat_session_post_type() {
    register_post_type('chat_session', [
        'label' => 'Chat Sessions',
        'public' => false,
        'show_ui' => true,
        'supports' => ['title'],
        'capability_type' => 'post',
        'menu_position' => 25,
        'menu_icon' => 'dashicons-format-chat',
    ]);
}
add_action('init', 'register_chat_session_post_type');



// Register custom roles on plugin activation
function workcity_add_custom_roles() {
    add_role('designer', 'Designer', ['read' => true]);
    add_role('agent', 'Agent', ['read' => true]);
}
register_activation_hook(__FILE__, 'workcity_add_custom_roles');

// Initialize the classes (Chat_Session::init() is called inside class-chat-session.php)
Chat_Widget::init();
Chat_AJAX::init();
Chat_API::init();
Chat_Handler::init();
