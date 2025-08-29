<?php if (is_user_logged_in()) : ?>
    <?php
    $current_user = wp_get_current_user();
    $product_id = isset($GLOBALS['workcity_chat_product_id']) ? intval($GLOBALS['workcity_chat_product_id']) : 0;
    $product = $product_id ? wc_get_product($product_id) : null;
    ?>

    <div class="workcity-chat-box">

        <!-- Product context (if exists) -->
        <?php if ($product): ?>
            <div class="chat-product-context">
                <strong>Product:</strong> <?= esc_html($product->get_name()); ?><br>
                <img src="<?= esc_url($product->get_image()); ?>" width="60" alt="Product image">
            </div>
        <?php endif; ?>

        <!-- Chat Header -->
        <div class="workcity-chat-header">
            <span>Live Chat</span>
            <button id="toggle-mode" title="Toggle Dark/Light Mode">🌓</button>
        </div>

        <!-- Role Selection -->
        <div class="chat-recipient">
            <label for="recipient_role">Chat With:</label>
            <select id="recipient_role" name="recipient_role" required>
                <option value="">Select Role</option>
                <option value="merchant">Merchant</option>
                <option value="designer">Designer</option>
                <option value="agent">Support Agent</option>
            </select>
        </div>

        <!-- User Selection (dynamically populated) -->
        <div class="chat-user-dropdown">
            <label for="recipient_user_id">Select User:</label>
            <select id="recipient_user_id" name="recipient_user_id" required>
                <option value="">Select a user</option>
            </select>
        </div>

        <!-- Chat Messages -->
        <div class="workcity-chat-messages" id="chat-messages">
            <!-- Messages will be loaded via AJAX -->
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator"></div>

        <!-- Chat Form -->
        <form id="workcity-chat-form" enctype="multipart/form-data">
            <input type="text" id="chat-input" placeholder="Type your message..." autocomplete="off" required>
            <input type="file" id="chat-file" name="chat_file">
            <button type="submit">Send</button>
        </form>

        <!-- Hidden Inputs for Security and Context -->
        <input type="hidden" id="chat_nonce" value="<?= esc_attr(wp_create_nonce('workcity_chat_nonce')); ?>">
        <input type="hidden" id="chat_product_id" value="<?= esc_attr($product_id); ?>">

    </div>
<?php else: ?>
    <p>You need to <a href="<?= esc_url(wp_login_url()); ?>">log in</a> to use the chat.</p>
<?php endif; ?>
