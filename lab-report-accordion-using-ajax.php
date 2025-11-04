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

// Shortcode Function
function lab_reports_accordion_using_ajax(){

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
    
    ob_start();
    $assets_uri = plugin_dir_url(__FILE__) . '/assets/images/';
    ?>
        <div class="lab-reports-wrapper">
            <div class="lab-accordion-container">
                <?php foreach($lab_report_category_field_choices as $category_value => $category_label) : ?>
                    <div class="lab-accordion-item category-item" data-category="<?php echo esc_attr($category_value); ?>">
                        <div class="lab-accordion-header category-header">
                            <span><?php echo esc_html($category_label); ?></span>
                            <span class="accordion-icon">
                                <img src="<?php echo esc_url($assets_uri . 'chevron-down.png'); ?>" alt="Toggle" width="9" height="16" />
                            </span>
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
function lab_reports_accordion_register_assets() {
    $version = '1.0.0';
    $assets_uri = plugin_dir_url(__FILE__) . '/assets';
    
    // Register CSS
    wp_register_style(
        'lab-reports-accordion-styles',
        $assets_uri . '/css/lab-reports-accordion.css',
        array(),
        $version,
        'all'
    );
}
add_action('wp_enqueue_scripts', 'lab_reports_accordion_register_assets');

// Enqueue
function lab_reports_accordion_enqueue_assets() {
    global $post;
    
    // Only enqueue if shortcode was actually used on this page
     if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lab_reports_accordion_using_ajax')) {
        wp_enqueue_style('lab-reports-accordion-styles');  
    }
}
add_action('wp_footer', 'lab_reports_accordion_enqueue_assets');