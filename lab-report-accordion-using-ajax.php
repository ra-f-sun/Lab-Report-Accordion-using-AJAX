<?php
/**
 * Plugin Name: Lab report accordion using ajax
 * Description: Test plugin for accordion
 * Version: 1.0.0
 * Author: Your Name
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Test that plugin loads
add_action('wp_footer', function() {
    echo '<!-- ACF Product Accordion Plugin Loaded -->';
});
