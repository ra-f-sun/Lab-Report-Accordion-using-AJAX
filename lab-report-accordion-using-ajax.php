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

// Define globals
$GLOBALS['assets_uri'] = plugin_dir_url(__FILE__) . 'assets';
$GLOBALS['lab_report_category'] = 'lab_report_category';
$GLOBALS['display_lab_report'] = 'display_lab_report';
$GLOBALS['lab_report_media_url'] = 'lab_report_media_url';
$GLOBALS['display_name_for_lab_report'] = 'display_name_for_lab_report';

// Shortcode Function
function lab_reports_accordion_using_ajax(){
    global $assets_uri;
    global $lab_report_category;
    
    //get acf field to get choice list of custom lab report category
    $lab_report_category_field = acf_get_field($lab_report_category);

    if(!$lab_report_category_field){
        return '<div style="color: red;">ERROR: ACF field not found!</div>';
    }
    
    // get choices
    $lab_report_category_field_choices = $lab_report_category_field['choices'];
    
    if(!$lab_report_category_field_choices){
        return '<div style="color: red;">ERROR: No choices found in ACF field!</div>';
    }
    
    ob_start();
    ?>
        <div class="lab-reports-wrapper">
            <div class="lab-accordion-container">
                <!-- Category level -->
                <?php foreach($lab_report_category_field_choices as $category_value => $category_label) : ?>
                    <div class="lab-accordion-item category-item" data-category-value="<?php echo esc_attr($category_value); ?>">
                        <div class="lab-accordion-header category-header">
                            <span><?php echo esc_html($category_label); ?></span>
                            <span class="accordion-icon">
                                <img src="<?php echo esc_url($assets_uri . '/images/chevron-down.png'); ?>" alt="Toggle" width="9" height="16" />
                            </span>
                        </div>
                        <!-- Product level -->
                        <div class="lab-accordion-content category-content">
                            <div class="lab-accordion-inner">
                                <p class="loading-text">Loading...</p>
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
    $version = '1.0.1';
    
    // Only enqueue if shortcode was actually used on this page
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'lab_reports_accordion_using_ajax')) {
       
        // css
        wp_enqueue_style(
            'lab-reports-accordion-styles',
            $assets_uri . '/css/lab-reports-accordion.css',
            array(),
            $version, 
            'all'
        );

        //download js
         wp_enqueue_script(
            'lab-reports-download',
            $assets_uri . '/js/download.js',
            array(),
            $version,
            true
        );

        // ajax js with jQuery and download js access
        wp_enqueue_script(
            'lab-reports-accordion-ajax',
            $assets_uri . '/js/ajax.js',
            array('jquery','lab-reports-download'),
            $version,
            true
        );

        wp_localize_script('lab-reports-accordion-ajax', 'labReportsAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),  
            'nonce'   => wp_create_nonce('lab_reports_nonce'), 
            'assetsUrl' => $assets_uri
        ));
    }
}
add_action('wp_enqueue_scripts', 'lab_reports_accordion_enqueue_assets');

// AJAX handler
function handle_load_products_by_category(){
    check_ajax_referer('lab_reports_nonce', 'nonce');
    
    global $wpdb;
    global $lab_report_category;
    global $display_lab_report;
    global $lab_report_media_url;
    global $display_name_for_lab_report;
    
    // Get category value from POST coming from ajax.js
    $category_value = isset($_POST['category_value']) ? sanitize_text_field($_POST['category_value']) : '';
    
    if (empty($category_value)) {
        wp_send_json_error(array('message' => 'No category provided'));
        return;
    }

    // filter and fetch all products by the category coming from ajax.js
    $products_by_category = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                p.ID as lab_report_product_id,
                p.post_title as product_title,
                pm_cat.meta_value as lab_report_category,
                pm_display.meta_value as lab_report_display_condition,
                pm_url.meta_value as lab_report_url,
                pm_display_name.meta_value as lab_report_display_name
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_cat 
                ON p.ID = pm_cat.post_id 
                AND pm_cat.meta_key = %s
            INNER JOIN {$wpdb->postmeta} pm_display 
                ON p.ID = pm_display.post_id 
                AND pm_display.meta_key = %s
            INNER JOIN {$wpdb->postmeta} pm_url 
                ON p.ID = pm_url.post_id 
                AND pm_url.meta_key = %s
            LEFT JOIN {$wpdb->postmeta} pm_display_name 
                ON p.ID = pm_display_name.post_id 
                AND pm_display_name.meta_key = %s
            WHERE p.post_type = %s
            AND p.post_status = %s
            AND pm_cat.meta_value = %s
            AND pm_display.meta_value = '1'
            AND pm_url.meta_value != ''
            ORDER BY p.post_title ASC",
            $lab_report_category,
            $display_lab_report,
            $lab_report_media_url,
            $display_name_for_lab_report,
            'product',
            'publish',
            $category_value
        )
    );
    
    // Check if products found
    if (empty($products_by_category)) {
        wp_send_json_success(array(
            'data' => array(),
            'message' => 'No products found for this category',
            'category' => $category_value
        ));
        return;
    }
    
    // Get current site URL once
    $current_site_url = get_site_url();
    
    // Transform URLs for each product 
    foreach ($products_by_category as $product) {
        if (!empty($product->lab_report_url)) {
            // Change http:// to https://
            $product->lab_report_url = str_replace('http://', 'https://', $product->lab_report_url);
            
            // Replace domain with current site URL
            $product->lab_report_url = preg_replace('#^https?://[^/]+#', $current_site_url, $product->lab_report_url);
        }
    }
    
    // send response to ajax
    wp_send_json_success(array(
        'data' => $products_by_category
    ));
}

add_action('wp_ajax_load_products', 'handle_load_products_by_category');
add_action('wp_ajax_nopriv_load_products', 'handle_load_products_by_category');