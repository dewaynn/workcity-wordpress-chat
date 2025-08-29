<?php
class Chat_Widget {

    public static function init() {
        add_shortcode('workcity_chat_widget', [__CLASS__, 'render_widget']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets() {
        $product_id = get_the_ID();

        wp_enqueue_style('workcity-chat-css', plugin_dir_url(__FILE__) . '../assets/css/chat.css');
        wp_enqueue_script('workcity-chat-js', plugin_dir_url(__FILE__) . '../assets/js/chat.js', ['jquery'], null, true);

        wp_localize_script('workcity-chat-js', 'workcity_chat_vars', [
            'ajax_url'    => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('workcity_chat_nonce'),
            'product_id'  => $product_id
        ]);
    }

    public static function render_widget($atts) {
        $atts = shortcode_atts([
            'product_id' => 0
        ], $atts);

        // Store globally so the template can access it
        $GLOBALS['workcity_chat_product_id'] = $atts['product_id'];

        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/chat-widget.php';
        return ob_get_clean();
    }
}
