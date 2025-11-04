<?php
/**
 * Plugin Name: Lab report accordion using ajax
 * Description: Test plugin for accordion
 * Version: 1.0.0
 * Author: Rafsun Jani
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$assets_uri = plugin_dir_url(__FILE__) . '/assets';
// Shortcode Function
function lab_reports_accordion_using_ajax(){

    // Lab report key
    $lab_report_category = 'lab_report_category';
    $display_lab_report = 'display_lab_report';
    $lab_report_media_url = 'lab_report_media_url';
    $display_name_for_lab_report = 'display_name_for_lab_report';
    
    $lab_report_category_field = acf_get_field($lab_report_category);

    if(!$lab_report_category_field){
        return '<div style="color: red;">ERROR: ACF field not found!</div>';
    }
    
    $lab_report_category_field_choices = $lab_report_category_field['choices'];
    
    if(!$lab_report_category_field_choices){
        return '<div style="color: red;">ERROR: No choices found in ACF field!</div>';
    }
    
    global $assets_uri;
    ob_start();
    ?>
        <div class="lab-reports-wrapper">
            <div class="lab-accordion-container">
                <!-- Category level -->
                <?php foreach($lab_report_category_field_choices as $category_value => $category_label) : ?>
                    <div class="lab-accordion-item category-item" data-category="<?php echo esc_attr($category_value); ?>">
                        <div class="lab-accordion-header category-header">
                            <span><?php echo esc_html($category_label); ?></span>
                            <span class="accordion-icon">
                                <img src="<?php echo esc_url($assets_uri . '/images/chevron-down.png'); ?>" alt="Toggle" width="9" height="16" />
                            </span>
                        </div>
                        <!-- Product level -->
                        <div class="lab-accordion-content category-content">
                            <div class="lab-accordion-inner">
                                <p>Content for <?php echo esc_html($category_label); ?> will load here via AJAX</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    return ob_get_clean();
}
add_shortcode('lab_reports_accordion_using_ajax', 'lab_reports_accordion_using_ajax');

// Register assets
function lab_reports_accordion_enqueue_assets() {
    global $post;
    global $assets_uri;
    $version = '1.0.0';
    
    
    // Only enqueue if shortcode was actually used on this page
     if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lab_reports_accordion_using_ajax')) {
        wp_enqueue_style(
            'lab-reports-accordion-styles',
            $assets_uri . '/css/lab-reports-accordion.css',
            array(),
            $version, 
            'all'
        );

        wp_enqueue_script(
            'lab-reports-accordion-functionality',
            $assets_uri . '/js/accordion.js',
            array(),
            $version,
            true
        );

        wp_enqueue_script(
            'lab-reports-accordion-ajax',
            $assets_uri . '/js/ajax.js',
            array('jquery'),
            $version,
            true
        );

        wp_localize_script('lab-reports-accordion-ajax', 'lab_reports_ajax',array(
            'ajax_url' => admin_url('admin-ajax.php'),  
            'nonce'   => wp_create_nonce('lab_reports_nonce'), 
        ));
    }
    
}
add_action('wp_enqueue_scripts', 'lab_reports_accordion_enqueue_assets');


// Test AJAX handler
add_action('wp_ajax_test_ajax', function() {
    wp_send_json_success(array('message' => 'AJAX IS WORKING!'));
});

add_action('wp_ajax_nopriv_test_ajax', function() {
    wp_send_json_success(array('message' => 'AJAX IS WORKING!'));
});
