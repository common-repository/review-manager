<?php

/**
 * review-manager
 *
 *
 * @package   review-manager
 * @author    matthewrubin
 * @license   GPLv2 or later
 * @link      https://www.mrmarketingres.com/review-manager
 * @copyright 2020 matthewrubin
 *
 * @wordpress-plugin
 * Plugin Name:       Review Manager
 * Plugin URI:        https://www.mrmarketingres.com/review-manager
 * Description:       The Review ManagerÂ® WordPress plugin extends the functionality of the SaaS Review Managerï¿½ to WordPress so that the review feed can be displayed on the WordPress website. The plugin is for customers of Review ManagerÂ® that have an active subscription with the company.
 * Version:           2.2.0
 * Requires at least: 3.5.1
 * Tested up to:      6.6.0
 * Requires PHP:      5.6.0
 * Author:            matthewrubin
 * Author URI:        https://www.mrmarketingres.com/
 * Text Domain:       review-manager
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

define('MRYRM_VERSION', '2.2.0');

define('MRYRM_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, 'mryrm_activation');

register_deactivation_hook(__FILE__, 'mryrm_deactivation');

function mryrm_activation() {

    require_once(MRYRM_PLUGIN_DIR . 'activate.php' );
    mryrm_create_setting_tables();
    mryrm_create_reviews_tables();
}

function mryrm_deactivation() {

    require_once(MRYRM_PLUGIN_DIR . 'deactivate.php' );
}

include_once 'include/function.php';

function mryrm_review_slider_shortcode($attr, $content) {
    return mryrm_review_slider($attr, $content);
}

add_shortcode('mryrm_review_slider', 'mryrm_review_slider_shortcode');



/* UPDATE START */

function mryrm_upgrade_function($upgrader_object, $options) {

    require_once(MRYRM_PLUGIN_DIR . 'activate.php' );
    mryrm_update_tables();
}

add_action('upgrader_process_complete', 'mryrm_upgrade_function', 10, 2);
/* UPDATE END */

function mryrm_enqueue_scripts() {

    wp_enqueue_style('owl.carousel.min.css', plugin_dir_url(__FILE__) . 'assets/css/owl.carousel.min.css');

    wp_register_script('owl.carousel.min.js', plugin_dir_url(__FILE__) . 'assets/js/owl.carousel.min.js', '', FALSE, TRUE);
    wp_enqueue_script('owl.carousel.min.js');
}

add_action('wp_enqueue_scripts', 'mryrm_enqueue_scripts');

function mryrm_admin_menu() {

    $view_level = 'activate_plugins';
    add_menu_page(('review-manager'), __('Review Manager', 'review-manager'), $view_level, 'mryrm_admin_menu', 'mryrm_options', plugins_url('review-manager/assets/images/star-icon.png'));
}

add_action('admin_menu', 'mryrm_admin_menu');

function mryrm_options() {

    if (!current_user_can('activate_plugins')) {

        wp_die(__('Review Manager Admin Area', 'review-manager'));
    }
    include_once 'admin/setting.php';
}

add_action('wp_ajax_nopriv_api-call', 'mryrm_api_request');

function mryrm_api_request() {
    // functional code will go here    
    $setting = $_REQUEST['setting'];
    $reviews = $_REQUEST['reviews'];

    if (isset($setting)) {
        $setting = stripcslashes(str_replace('\"', '"', $setting));
        $mryrm_setting = json_decode($setting);
        mryrm_update_setting($mryrm_setting);
    }
    if (isset($reviews)) {
        mryrm_update_reviews($reviews);
    }
    echo TRUE;
    // do whatever you want to do
}

function mryrm_update_reviews($reviews) {

    global $wpdb;

    $mryrm_reviews = stripcslashes(str_replace('\"', '"', $reviews));
    $mryrm_reviews = json_decode($mryrm_reviews, true);

    $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mryrm_reviews";
    $wpdb->query($delete_sql);

    foreach ($mryrm_reviews as $key => $obj) {

        $location = '';
        if (isset($obj['location']) && strlen($obj['location']) > 4) {
            $arr = explode(",", $obj['location']);
            $location = $arr[0];
        }

        $data_arr = array(
            'review_id' => $obj['review_id'],
            'source' => stripcslashes(esc_sql($obj['source'])),
            'location' => stripcslashes(esc_sql($location)),
            'review_group' => stripcslashes(esc_sql($obj['review_group'])),
            'author' => stripcslashes(esc_sql($obj['author'])),
            'rating' => $obj['rating'],
            'review' => stripcslashes(esc_sql($obj['review'])),
            'keyword' => $obj['keyword'],
            'city' => $obj['city'],
            'state' => $obj['state'],
            'review_type' => $obj['review_type'],
            'designation' => $obj['designation'],
            'is_publish' => $obj['is_publish'],
            'created_at' => $obj['created_at'],
            'updated_at' => $obj['updated_at']
        );

        $mryrm_table_name = $wpdb->prefix . "mryrm_reviews";
        $wpdb->insert($mryrm_table_name, $data_arr);
    }
}

function mryrm_update_setting($mryrm_setting) {

    global $wpdb;

    $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mryrm_setting";
    $wpdb->query($delete_sql);

    $data_array = array(
        'mryrm_url_type' => 'file_get_content',
        'mryrm_key' => $mryrm_setting->schema_unique_id,
        'head_title' => $mryrm_setting->head_title,
        'show_star' => $mryrm_setting->show_star,
        'show_order_by' => $mryrm_setting->show_order_by,
        'show_order_type' => $mryrm_setting->show_order_type,
        'show_total' => $mryrm_setting->show_total,
        'is_show_date' => $mryrm_setting->is_show_date,
        'is_show_title' => $mryrm_setting->is_show_title,
        'is_show_rating' => $mryrm_setting->is_show_rating,
        'is_show_author' => $mryrm_setting->is_show_author,
        'is_show_bullet' => $mryrm_setting->is_show_bullet,
        'is_show_icon' => $mryrm_setting->is_show_icon,
        'is_show_credit' => $mryrm_setting->is_show_credit,
        'is_multi_location' => $mryrm_setting->is_multi_location,
        'latitude' => $mryrm_setting->latitude,
        'longitude' => $mryrm_setting->longitude,
        'is_location_new_line' => $mryrm_setting->is_location_new_line,
        'icon_position' => $mryrm_setting->icon_position,
        'content_align' => $mryrm_setting->content_align,
        'widget_type' => $mryrm_setting->widget_type,
        'bg_color' => $mryrm_setting->bg_color,
        'title_color' => $mryrm_setting->title_color,
        'text_color' => $mryrm_setting->text_color,
        'star_color' => $mryrm_setting->star_color,
        'author_color' => $mryrm_setting->author_color,
        'custom_css' => $mryrm_setting->custom_css,
        'nav_css' => $mryrm_setting->nav_css,
        'nav_active_css' => $mryrm_setting->nav_active_css,
        'review_keyword' => $mryrm_setting->review_keyword,
        'city_group' => $mryrm_setting->city_group,
        'review_group' => $mryrm_setting->review_group,
        'keyword_separator' => $mryrm_setting->keyword_separator,
        'org_name' => $mryrm_setting->org_name,
        'org_city' => $mryrm_setting->org_city,
        'org_state' => $mryrm_setting->org_state,
        'org_phone' => $mryrm_setting->org_phone,
        'org_type' => $mryrm_setting->org_type,
        'org_url' => $mryrm_setting->org_url,
        'org_logo_url' => $mryrm_setting->org_logo_url,
        'org_address' => $mryrm_setting->org_address,
        'org_address_line' => $mryrm_setting->org_address_line,
        'org_zipcode' => $mryrm_setting->org_zipcode,
        'wp_short_code' => $mryrm_setting->wp_short_code,
        'is_abc' => $mryrm_setting->is_abc,
        'created_at' => $mryrm_setting->created_at,
        'updated_at' => $mryrm_setting->updated_at
    );

    $mryrm_table_name = $wpdb->prefix . "mryrm_setting";
    $wpdb->insert($mryrm_table_name, $data_array);
}

add_action('wp_ajax_nopriv_api-custom-review', 'mryrm_custom_review');

function mryrm_custom_review() {

    $reviews = $_REQUEST['reviews'];

    if (isset($reviews)) {

        global $wpdb;
        $reviews = stripcslashes(str_replace('\"', '"', $reviews));
        $reviews = json_decode($reviews, true);

        foreach ($reviews as $key => $obj) {

            $location = isset($obj['location']) && strlen($obj['location']) > 3 ? substr($obj['location'], 0, -3) : '';

            $data_arr = array(
                'review_id' => $obj['review_id'],
                'source' => stripcslashes(esc_sql($obj['source'])),
                'location' => stripcslashes(esc_sql($location)),
                'review_group' => stripcslashes(esc_sql($obj['review_group'])),
                'author' => stripcslashes(esc_sql($obj['author'])),
                'rating' => $obj['rating'],
                'review' => stripcslashes(esc_sql($obj['review'])),
                'keyword' => $obj['keyword'],
                'city' => $obj['city'],
                'state' => $obj['state'],
                'review_type' => 1,
                'designation' => stripcslashes(esc_sql($obj['designation'])),
                'is_publish' => $obj['is_publish'],
                'created_at' => $obj['created_at'],
                'updated_at' => $obj['updated_at']
            );

            $mryrm_table_name = $wpdb->prefix . "mryrm_reviews";
            $wpdb->insert($mryrm_table_name, $data_arr);
        }
    }

    echo TRUE;
    // do whatever you want to do
}

add_action('wp_ajax_nopriv_api-check', 'mryrm_api_check');

function mryrm_api_check() {

    global $wpdb;
    $mryrm_sql = "DROP TABLE {$wpdb->prefix}mryrm_setting";
    $wpdb->query($mryrm_sql);
    $mryrm_sql = "DROP TABLE {$wpdb->prefix}mryrm_reviews";
    $wpdb->query($mryrm_sql);
    echo TRUE;
}
