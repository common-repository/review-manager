<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

require_once(MRYRM_PLUGIN_DIR . 'activate.php' );
mryrm_create_setting_tables();
mryrm_create_reviews_tables();
mryrm_update_tables();

global $wpdb;
$mryrm_error_msg = "";
$mryrm_msg = "";

function mryrm_check_key_format($mryrm_key) {

    if (!strpos($mryrm_key, '-')) {
        return FALSE;
    }

    $arr = explode('-', $mryrm_key);
    if (strlen($arr[1]) != 10) {
        return FALSE;
    }

    return TRUE;
}

function insert_mrm_setting_table($mryrm_setting, $mryrm_url_type, $mryrm_key) {

    global $wpdb;

    $data_array = array(
        'mryrm_url_type' => $mryrm_url_type,
        'mryrm_key' => $mryrm_key,
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

if (isset($_REQUEST['submit']) && is_user_logged_in()) {

    // update setting 
    if ($_REQUEST['mryrm_setting_id']) {
        $mryrm_update_sql = "UPDATE {$wpdb->prefix}mryrm_setting
        SET mryrm_key = '" . $_REQUEST['mryrm_key'] . "'
        WHERE id = '" . $_REQUEST['mryrm_setting_id'] . "'";
        $wpdb->query($mryrm_update_sql);
    }

    $mryrm_nonce = $_REQUEST['_wpnonce'];

    if (wp_verify_nonce($mryrm_nonce, 'submit_mryrm')) {

        $mryrm_key = sanitize_text_field(sanitize_key($_REQUEST['mryrm_key']));
        $mryrm_url_type = $_REQUEST['mryrm_url_type'];

        if (!$mryrm_key) {

            $mryrm_error_msg = "Review Manager key should not be empty.";
        } else if (!preg_match("/^([0-9-])+$/i", $mryrm_key)) {

            $mryrm_error_msg = "Review Manager key should be valid key.";
        } else if (!mryrm_check_key_format($mryrm_key)) {

            $mryrm_error_msg = "Review Manager key should be valid format.";
        } else {

            // check Review Manager Key remote vaklidity
            if ($mryrm_url_type == 'wp_remote_get') {
                $mryrm_response = @wp_remote_get('https://reviewmanager.app/plugin/authdev/' . $mryrm_key);
                $mryrm_setting = json_decode($mryrm_response['body']);
            } else {
                $mryrm_response = @file_get_contents('https://reviewmanager.app/plugin/authdev/' . $mryrm_key);
                $mryrm_setting = json_decode($mryrm_response);
            }

            if (empty($mryrm_setting)) {
                $mryrm_error_msg = '<p>You have provide incorrect Review Manager Key. Please <a href="https://www.mrmarketingres.com/review-manager/" target="_blank">Contact</a> for correct Review Manager Key</p>';
            } else {

                // process to create tables 
                $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mryrm_setting";
                $wpdb->query($delete_sql);

                // Now insert setting data
                insert_mrm_setting_table($mryrm_setting, $mryrm_url_type, $mryrm_key);
                $mryrm_msg = "Review Manager key saved successfully.";

                // now will fetch all reviews from RM app  
                if ($mryrm_url_type == 'wp_remote_get') {
                    $mryrm_review_response = @wp_remote_get('https://reviewmanager.app/plugin/fetchdev/' . $mryrm_key);
                    $mryrm_reviews = json_decode($mryrm_review_response['body']);
                } else {
                    $mryrm_review_response = @file_get_contents('https://reviewmanager.app/plugin/fetchdev/' . $mryrm_key);
                    $mryrm_reviews = json_decode($mryrm_review_response);
                }

                if (isset($mryrm_reviews) && !empty($mryrm_reviews)) {

                    $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mryrm_reviews";
                    $wpdb->query($delete_sql);

                    foreach ($mryrm_reviews as $obj) {

                        $location = '';
                        if (isset($obj->location) && strlen($obj->location) > 4) {
                            $arr = explode(',', $obj->location);
                            $location = $arr[0];
                        }

                        $data_arr = array(
                            'review_id' => $obj->review_id,
                            'source' => stripcslashes(esc_sql($obj->source)),
                            'location' => stripcslashes(esc_sql($location)), 
                            'review_group' => stripcslashes(esc_sql($obj->review_group)),
                            'author' => stripcslashes(esc_sql($obj->author)),
                            'rating' => $obj->rating,
                            'review' => stripcslashes(esc_sql($obj->review)),
                            'keyword' => $obj->keyword,
                            'city' => $obj->city,
                            'state' => $obj->state,
                            'review_type' => $obj->review_type,
                            'designation' => $obj->designation,
                            'is_publish' => $obj->is_publish,
                            'created_at' => $obj->created_at,
                            'updated_at' => $obj->updated_at
                        );

                        $mryrm_table_name = $wpdb->prefix . "mryrm_reviews";
                        $wpdb->insert($mryrm_table_name, $data_arr);
                    }
                }
            }
        }
    }
}

// for view
$mryrm_setting_sql = "SELECT * FROM {$wpdb->prefix}mryrm_setting";
$mryrm_setting = $wpdb->get_row($mryrm_setting_sql);

if (empty($mryrm_setting)) {

    $data_array = array(
        'mryrm_url_type' => 'wp_remote_get',
        'created_at' => date('Y-m-d'),
        'updated_at' => date('Y-m-d')
    );

    $mryrm_table_name = $wpdb->prefix . "mryrm_setting";
    $wpdb->insert($mryrm_table_name, $data_array);
}
?>

<style type="text/css">
    .alternate, .striped>tbody>:nth-child(odd), ul.striped>:nth-child(odd){background: none;width: 80%; }
    .large-text{border:1px solid #e0e0e0 !important; padding: 3px !important; margin: 25px 0px 10px 0px;width:90%;}
    .title-icon{width:20px;padding-right: 4px;}
</style>
<div class="wrap">   
    <?php if ($mryrm_msg) { ?>
        <h4 style="color: green;"><?php echo $mryrm_msg; ?></h4>
    <?php } ?>
    <?php if ($mryrm_error_msg) { ?>
        <h4 style="color: red;"><?php echo $mryrm_error_msg; ?></h4>
    <?php } ?>

    <h2><img style="width:20px;padding-right: 4px;" src="<?php echo plugins_url('review-manager/assets/images/star.svg'); ?>" alt="" /> <?php echo __('Review Manager Setting', 'mryrm'); ?></h2>
    <p><hr/></p>

<form name="post" action="" method="post" id="post">
    <table class="alternate">
        <tbody>

            <?php if (isset($mryrm_setting) && !empty($mryrm_setting->mryrm_key)) { ?>
                <tr class="alternate">
                    <td class="import-system row-title"><a>[mryrm_review_slider]</a></td>          
                    <td class="desc"><?php echo __('This shortcode contains reviews from your social profile. Just copy this shortcode and paste into your expected page/ post where you want to see your review slider', 'mryrm'); ?></td>
                </tr>                
                <tr class="alternate">
                    <td class="import-system row-title"><a>[mryrm_review_slider type="1" city="City Name" group="Group Name" location="Location Name"]</a></td>          
                    <td class="desc"><?php echo __('If we need Type wise Reviews then we can use Type, If we need City wise Reviews then we can use City, If we need Group wise Reviews then we can use Group and If we need Location wise Reviews the we can use Location in the shortcode attribute. You can use one attribute at a time', 'mryrm'); ?></td>
                </tr>                
            <?php } ?> 


            <tr class="alternate">
                <td width="35%" class="import-system row-title"><?php _e('Please Choose URL Type for Data Collection', 'mrylm') ?></td>          
                <td class="desc">
                    <select name="mryrm_url_type" id="mrylm_url_type" class="large-text">                       
                        <option value="file_get_content" <?php
                        if (isset($mryrm_setting) && $mryrm_setting->mryrm_url_type == 'file_get_content') {
                            echo 'selected="selected"';
                        }
                        ?>>File Get Content</option>
                        <option value="wp_remote_get" <?php
                        if (isset($mryrm_setting) && $mryrm_setting->mryrm_url_type == 'wp_remote_get') {
                            echo 'selected="selected"';
                        }
                        ?>>WP Remote Get</option>
                    </select>
                </td>
            </tr>   
            <tr class="alternate">
                <td width="35%" class="import-system row-title"><?php _e('Please enter your Review Manager Key', 'mryrm') ?></td>          
                <td class="desc">
                    <input  type="text" class="large-text" name="mryrm_key" id="mryrm_key" value="<?php echo isset($mryrm_setting) ? esc_html($mryrm_setting->mryrm_key) : ''; ?>" autocomplete="off"/>
                </td>
            </tr>                  
            <tr class="alternate">
                <td class="import-system row-title"></td>          
                <td class="desc">
                    For Review Manager Key please <a href="https://www.mrmarketingres.com/review-manager/" target="_blank">Contact</a>
                </td>
            </tr>  
            <tr class="alternate">
                <td colspan="2" class=""></td> 
            </tr> 
            <tr class="alternate">
                <td class="import-system row-title"></td>          
                <td class="desc">
                    <input type="hidden" name="mryrm_setting_id" id="mryrm_setting_id" value="<?php echo isset($mryrm_setting) ? esc_html($mryrm_setting->id) : ''; ?>" />
                    <input type="submit" name="submit" value="<?php _e('Pull Review', 'mryrm') ?>"  class="button button-primary" />
                    <?php wp_nonce_field('submit_mryrm'); ?>
                </td>
            </tr>        
        </tbody>
    </table>
</form>  
</div>