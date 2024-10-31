<?php

function mryrm_review_slider($attr = null, $content = null) {
       
    global $wpdb;
    $type = '';
    $location = '';
    $group = '';
    $city = '';
    
    if(!empty($attr)){
        $type = isset($attr['type']) ? $attr['type'] : ''; 
        $location = isset($attr['location']) ? $attr['location'] : ''; 
        $group = isset($attr['group']) ? $attr['group'] : ''; 
        $city = isset($attr['city']) ? $attr['city'] : '';
    }
    
    $mryrm_sql = "SELECT * FROM {$wpdb->prefix}mryrm_setting";
    $mryrm_setting = $wpdb->get_row($mryrm_sql);
      
    if ($mryrm_setting->mryrm_key == '') {
        return '<p>You have missing your Review Manager Key. Please <a href="https://www.mrmarketingres.com/review-manager/" target="_blank">Contact</a> for Review Manager Key</p>';
    }    
 
    if($mryrm_setting->is_abc){
        
        $reviewer = 'Clients';
        if($content == 'caregiver'){
            $reviewer = 'Caregivers';
        }
        
        return mryrm_abc_front_testimonial($mryrm_setting, $type, $location, $group, $city, $content, 'Clients');
        
    }else{
        
       return mryrm_testimonial($mryrm_setting, $type, $location, $group, $city); 
    }    
}

/* NON ABC START */

function mryrm_testimonial($mryrm_setting = null, $type = null, $location = null, $group = null, $city = null){      
           
        $reviews = mryrm_review_feed_data($type, $location, $group, $city);
                    
        $mryrm_data = '';
        $mryrm_str_data = '';   
        $total_review = count($reviews);
                
        if($total_review > 0){ 
            
                // initialized variable
                $mryrm_icon = ''; 
                $list_author = '';
                $widget_type = '';
                $mrm_slides  = '';
                
                // define widget type
                if($mryrm_setting->widget_type == 'list'){                    
                     $list_author = 'float:left;';
                     $mrm_slides = 'border-bottom: 1px solid #eaeaea;';
                     $widget_type = '_';
                }
                
                $mryrm_keywords = explode(',', rtrim($mryrm_setting->review_keyword, ','));
                $mryrm_total_keyword = count($mryrm_keywords);
                $mryrm_counter = 0;

                $is_show_head_title = $mryrm_setting->head_title ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_date = $mryrm_setting->is_show_date ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_title = $mryrm_setting->is_show_title ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_rating = $mryrm_setting->is_show_rating ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_author = $mryrm_setting->is_show_author ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_bullet = $mryrm_setting->is_show_bullet ? 'display:block;' : 'display:none;';
                $is_show_icon = $mryrm_setting->is_show_icon ? 'style="display:block;"' : 'style="display:none;"';
                $is_location_new_line = $mryrm_setting->is_location_new_line > 0 ? '<br/>' : '';                                
                $mryrm_icon_right_of_rating =  'display:block;';
                                  
                 // custom css processing  
                $mryrm_css = '<style type="text/css">' .  
                        '.mrm-container{text-align:'.$mryrm_setting->content_align.';background-color: ' . $mryrm_setting->bg_color . ';}'.
                        '.mrm-icon-right-of-rating{'.$mryrm_icon_right_of_rating.'}'.                       
                        '.owl-dots{'.$is_show_bullet.'}'.
                        '.list-author{'.$list_author.'}'.
                        '.mrm-slides{'.$mrm_slides.'}'.  
                        '.mrm-5star{color: ' . $mryrm_setting->star_color . '; float:left;}' .                        
                        '.mrm-title-sm{color: ' . $mryrm_setting->title_color . ' !important;}' .
                        '.mrm-review-text{color: ' . $mryrm_setting->text_color . ';}' .
                        '.mrm-review-footer{color: ' . $mryrm_setting->author_color . ';}' .
                        'button.owl-dot{' . $mryrm_setting->nav_css . '}' .
                        'button.owl-dot.active{' . $mryrm_setting->nav_active_css . '}' .
                        $mryrm_setting->custom_css.
                        '</style>';
                
                $mryrm_outer_start = '<div class="mrm-container" itemscope="" itemtype="http://schema.org/LocalBusiness">';                  
                    $mryrm_outer_start .= '<div class="mrm-slider-header">';
                        $mryrm_outer_start .= '<h2 class="mrm-header-title mrm-title-lg" '.$is_show_head_title.'>' . $mryrm_setting->head_title . '</h2>';                     
                    $mryrm_outer_start .= '</div>';
                    $mryrm_outer_start .= '<meta itemprop="name" content="' . $mryrm_setting->org_name . '">';
                    $mryrm_outer_start .= '<div itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">';
                        $mryrm_outer_start .= '<meta itemprop="streetAddress" content="' . $mryrm_setting->org_address_line . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressLocality" content="' . $mryrm_setting->org_city . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressRegion" content="' . $mryrm_setting->org_state . '">';
                        $mryrm_outer_start .= '<meta itemprop="postalCode" content="' . $mryrm_setting->org_zipcode . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressCountry" content="US">';
                    $mryrm_outer_start .= '</div>';
                    $mryrm_outer_start .= '<meta itemprop="url" content="' . $mryrm_setting->org_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="logo" content="' . $mryrm_setting->org_logo_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="image" content="' . $mryrm_setting->org_logo_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="priceRange" content="$$$">';
                    $mryrm_outer_start .= '<meta itemprop="telePhone" content="' . $mryrm_setting->org_phone . '">';
                    
                    $mryrm_outer_start .= '<div id="mrm-testimonial-carousel'.$widget_type.'" class="mrm-slider-content owl-carousel'.$widget_type.' owl-theme">';
                     
                    
                foreach($reviews as $obj){ 

                    $mryrm_rating[] = $obj->rating;

                    // keywords manage
                    $mryrm_counter++;
                    if ($mryrm_counter == $mryrm_total_keyword) {
                        $mryrm_counter = 0;
                    }

                    // Rating star color design
                    $mryrm_rRating = '';
                    for ($i = 1; $i <= $obj->rating; $i++) {
                        $mryrm_rRating .= '&#9733;'; // orange star
                    }
                    for ($i = $obj->rating + 1; $i <= 5; $i++) {
                        $mryrm_rRating .= '&#9734;'; // white star
                    }

                    //keyword processing
                    $mryrm_keyword = '';
                    $mryrm_keyword_title = '';
                    if ($obj->keyword != '') {
                        $mryrm_keyword = $obj->keyword ? $obj->keyword : '';
                        $mryrm_keyword_title = $obj->keyword ? $obj->keyword . ' ' . $mryrm_setting->keyword_separator . ' ' . $obj->author : '';
                    } else {
                        $mryrm_keyword = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] : '';
                        $mryrm_keyword_title = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] . ' ' . $mryrm_setting->keyword_separator . ' ' . $obj->author : '';
                    }

                    // state & city processing
                    $mryrm_state_n_city = '';
                    if ($obj->city) {
                        $mryrm_state_n_city .= ' - ' . $obj->city;
                    }
                    if ($obj->state) {
                        $mryrm_state_n_city .= ' ' . $obj->state;
                    }                    
                    $location_new_line = $mryrm_state_n_city != '' ? $is_location_new_line : '';
                                    
                    $mryrm_icon = '<img class="source-icon" src="'. $mryrm_setting->org_url .'/wp-content/plugins/review-manager/assets/images/icon/' . strtolower(str_replace(' ', '_', trim($obj->source))) . '.png" alt="' . $obj->source . '"  title="' . $obj->source . '"  />';

                    // Main review content processing
                    $mryrm_str = '<div class="item mrm-slides" itemprop="Reviews"  itemscope="" itemtype="http://schema.org/Review">' .
                                    '<div class="mrm-review-header">' .
                                        '<h3 class="mrm-title-sm" ' . $is_show_title . '>'. $mryrm_keyword_title .'</h3>'.
                                        '<div class="clear">'.
                                            '<div class="mrm-5star" ' . $is_show_rating . '>' . $mryrm_rRating . '</div>'. 
                                            '<span class="mrm-icon-right-of-rating"  ' . $is_show_icon . '>'.$mryrm_icon.'</span>'.
                                        '</div>'.
                                        '<div class="clear"></div>' .                                        
                                        '<div itemprop="itemReviewed" itemscope="" itemtype="http://schema.org/Service">' . // added
                                            '<div itemprop="name" style="display:none;">' .
                                                '<a href="' . $mryrm_setting->org_url . '"> ' . $mryrm_keyword . ' </a>' .
                                            '</div>' .
                                        '</div>' .
                                        '<span class="mrm-date" ' . $is_show_date . '>' .
                                            '<time datetime="' . $obj->created_at . '">' . date('M j, Y', strtotime($obj->created_at)) . '</time>' .
                                        '</span>' .
                                    '</div>' .
                                    '<div class="mrm-review-text" itemprop="reviewBody">' . $obj->review . '</div>'.                                              
                                   
                                    '<div class="mrm-review-footer" itemprop="author" itemscope="" itemtype="http://schema.org/Person">' .
                                        '<span itemprop="name" style="display: none;">' . $obj->author . '</span>' .
                                        '<div class="mrm-author" '.$is_show_author.'><strong class="list-author">' . $obj->author. ' </strong> '. $location_new_line .'<span class="list-author">'. $mryrm_state_n_city . '</span></div>' .
                                    '</div>' .                            
                                    '<div itemprop="publisher" itemscope="" itemtype="http://schema.org/Organization">' .
                                        '<span itemprop="name" style="display: none;">' . $obj->source . '</span>' .
                                    '</div>' .
                                '</div>';

                    $mryrm_str_data .= $mryrm_str;
                }

                $mryrm_outer_end = '</div>';
                $mryrm_container_end = '</div>';

                $mryrm_score_count = count($mryrm_rating);
                $mryrm_score_sum = array_sum($mryrm_rating);
                $mryrm_avg_rating = $mryrm_score_sum / $mryrm_score_count;

                // Aggregating content processing    
                $mryrm_aggregate = '<div itemprop="AggregateRating" itemscope itemtype="schema.org/AggregateRating">         
                                        <meta itemprop="ratingValue" content="' . $mryrm_avg_rating . '.0">
                                        <meta itemprop="bestRating" content="5.0">
                                        <meta itemprop="worstRating" content="1.0">
                                        <meta itemprop="reviewCount" content="' . $mryrm_score_count . '">                                      
                                        <meta itemprop="name" content="' . $mryrm_setting->org_name . '">                                     
                                    </div>'; 

                $mryrm_data = $mryrm_outer_start . $mryrm_str_data . $mryrm_outer_end . $mryrm_aggregate . $mryrm_container_end . $mryrm_css;
            } else {
                $mryrm_data = "<p>No Review data found.</p>";
            }

        return $mryrm_data;
}

/* NON ABC END */


/* REVIEW DATA START */

function mryrm_review_feed_data($type = null, $location = null, $group = null, $city = null) {
             
    global $wpdb;
    $mryrm_feeds = array();
    
    $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE review_type = 0 ORDER BY created_at DESC"; 
    if($type == 1){
        $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE review_type = 1 ORDER BY created_at DESC";
    }else if($location){
        $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE location = '$location' ORDER BY created_at DESC"; 
    }else if($group != ''){
        $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE review_group = '$group' ORDER BY created_at DESC";
    }else if($city != ''){
        $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE city = '$city' ORDER BY created_at DESC";
    }   
    
    $reviews = $wpdb->get_results($review_sql); 
    
    if (!empty($reviews)) {

        foreach ($reviews AS $item) {
            
            $obj = new StdClass();
            $obj->source = trim($item->source);
            $obj->location = trim($item->location);
            $obj->review_group = trim($item->review_group);
            $obj->author = trim($item->author);
            $obj->rating = trim($item->rating);
            $obj->review = trim($item->review);
            $obj->keyword = $item->keyword;
            $obj->city = $item->city;
            $obj->state = $item->state;
            $obj->review_type = $item->review_type;
            $obj->designation = $item->designation;
            $obj->date_time = trim($item->created_at);
            $obj->review_title = '';
            $mryrm_feeds[] = $obj;
        }
    }
        
    return $mryrm_feeds;   
}

function mryrm_limit_words($mryrm_string, $mryrm_word_limit) {
    $mryrm_words = explode(" ", $mryrm_string);
    return implode(" ", array_splice($mryrm_words, 0, $mryrm_word_limit));
}

/* REVIEW DATA END */




/* ABC NEW FRONT START */

function mryrm_abc_front_testimonial($mryrm_setting, $type = null, $location = null, $group = null, $city = null, $content = null, $reviewer = null){    
    
    // getting review from feed store
    $mryrm_reviews = mryrm_review_feed_data($type, $location, $group, $city);

    $mryrm_rating = array();
    $mryrm_bg_images = array('testimonial-1.jpg', 'testimonial-2.jpg', 'testimonial-3.jpg', 'testimonial-4.jpg', 'testimonial-5.jpg');
    $mryrm_img_counter = 1;
    
    $mryrm_keywords = explode(',', rtrim($mryrm_setting->review_keyword, ','));
    $mryrm_total_keyword = count($mryrm_keywords);
    $mryrm_counter = 0;
    
    if(empty($mryrm_reviews)){
        echo "<p>No Review data found.</p>";
        return;
    }
    
    ?>
    
    <div class="rm-wrap" itemscope="" itemtype="http://schema.org/LocalBusiness"> 
        
       <!-- Slide Section Start -->  
      <div id="rmtop" class="owl-carousel owl-theme rm-content">  
        <?php
        foreach ($mryrm_reviews as $key => $review) {

            $name_arr = explode(' ', $review->author);
            $l_name = isset($name_arr[1]) ? ucfirst(substr($name_arr[1], -1)) : '';
            $reviewer_name = $name_arr[1] . ' '.$l_name;

            $mryrm_rating[] = $review->rating;

            $mryrm_counter++;
            if ($mryrm_counter == $mryrm_total_keyword) {
                $mryrm_counter = 0;
            }        
            $mryrm_keyword = '';
            $mryrm_keyword_title = '';
            if($review->keyword !=''){
                $mryrm_keyword        = $review->keyword ? $review->keyword : '';
                $mryrm_keyword_title  = $review->keyword ? $review->keyword .' for '. $reviewer_name : '';
            }else{
                $mryrm_keyword = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] : '';
                $mryrm_keyword_title = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] . ' for ' . $reviewer_name : '';
            } 

            $mryrm_img_counter++;
            if($mryrm_img_counter == 3){ $mryrm_img_counter = 1;}
            
            // state & city processing
            $mryrm_state_n_city = '';
            if ($obj->city) {
                $mryrm_state_n_city .= ' - ' . $obj->city;
            }
            if ($obj->state) {
                $mryrm_state_n_city .= ' ' . $obj->state;
            }                    
            $location_new_line = $mryrm_state_n_city != '' ? $is_location_new_line : '';
            
            
            
            ?>

                <div class="rm-item" itemprop="Reviews"  itemscope="" itemtype="http://schema.org/Review" style="background-image: url('/wp-content/plugins/review-manager/assets/images/testimonial-<?php echo $mryrm_img_counter; ?>.jpg');">
                     <div class="rm-container rm-py">   
                         <div class="rm-title-sm">Hear from Our <?php echo $reviewer; ?></div>
                         <div class="rm-title-md" itemprop="name"><?php echo $mryrm_keyword_title; ?></div> 

                        <div style="display:none;" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                            <span itemprop="ratingValue"><?php echo $review->rating; ?></span>
                            <span itemprop="bestRating">5</span>
                            <span itemprop="worstRating">1</span>
                        </div>

                        <div style="display:none;"  itemprop="itemReviewed" itemscope itemtype="http://schema.org/<?php echo $mryrm_setting->org_type; ?>">
                            <div class="rr_review_post_id" itemprop="name" >
                                <a href="<?php echo $mryrm_setting->org_url; ?>" itemprop="url"><?php echo $mryrm_keyword; ?></a>
                            </div>
                        </div>

                        <div style="display:none;" itemprop="author" itemscope itemtype="http://schema.org/Person">
                            <span itemprop="name"><?php echo $review->author; ?></span>
                        </div>

                        <div class="rr_date" style="display:none;">
                            <meta itemprop="datePublished" content="<?php echo $review->date_time; ?>">
                            <time datetime="<?php echo $review->date_time; ?>"><?php echo date('M j, Y', strtotime($review->date_time)); ?></time>
                        </div>

                        <div style="display: none;" itemprop="publisher" itemscope="" itemtype="http://schema.org/Organization">
                            <span itemprop="name"><?php echo $review->source ? $review->source : 'ABC'; ?></span>
                        </div>                   

                        <?php
                            $mryrm_review_text = $review->review;
                            $mryrm_review_text = mryrm_limit_words($mryrm_review_text, 15);                       
                        ?> 
                        <p class="rm-quote">“<?php echo $mryrm_review_text; ?>”</p>
                        <div style="display: none;" itemprop="reviewBody">
                            <?php echo $review->review; ?>
                        </div>

                        <div class="rm-onclick" itemid="<?php echo $key; ?>">Read full testimonial</div> 

                    </div>                     
                </div>             

        <?php } ?>          
        </div> 
         <!-- Slide Section End -->
         
        <!-- Thumb Section Start -->
        <div id="rmbottom" class="owl-carousel owl-theme rm-container rm-tab">
            <?php foreach ($mryrm_reviews as $key => $review) { ?>
            <?php
            
            $name_arr = explode(' ', $review->author);
            $l_name = isset($name_arr[1]) ? ucfirst(substr($name_arr[1], -1)) : '';
            $reviewer_name = $name_arr[1] . ' '.$l_name;
            
            // state & city processing
            $mryrm_state_n_city = '';
            if ($obj->city) {
                $mryrm_state_n_city .= ' - ' . $obj->city;
            }
            if ($obj->state) {
                $mryrm_state_n_city .= ' ' . $obj->state;
            }                    
            $location_new_line = $mryrm_state_n_city != '' ? $is_location_new_line : '';
            ?>
                <div class="rm-item">
                    <div class="rm-name"><?php echo $reviewer_name; ?></div>
                    <div class="rm-designation"><?php echo $location_new_line; ?></div>
                </div>
            <?php } ?>
        </div> 
        <!-- Thumb Section End -->
         
        <!-- Popup Section Start -->
        <div>
            <?php foreach ($mryrm_reviews as $key => $review) { ?>
            
            <?php
            
            $name_arr = explode(' ', $review->author);
            $l_name = isset($name_arr[1]) ? ucfirst(substr($name_arr[1], -1)) : '';
            $reviewer_name = $name_arr[1] . ' '.$l_name;
            
            // state & city processing
            $mryrm_state_n_city = '';
            if ($obj->city) {
                $mryrm_state_n_city .= ' - ' . $obj->city;
            }
            if ($obj->state) {
                $mryrm_state_n_city .= ' ' . $obj->state;
            }                    
            $location_new_line = $mryrm_state_n_city != '' ? $is_location_new_line : '';
            
            ?>
            
                <div class="rm-model" id="rm-modal-<?php echo $key; ?>">
                    <div class="rm-model-inner">
                        <div class="rm-close-btn">×</div>
                        <div class="rm-model-wrap">
                            <div class="pop-up-rm">
                                <div class="rm-title-sm">TESTIMONIALS</div>
                                <p>“<?php echo $review->review; ?>”</p>
                                <div class="rm-divider"></div>
                                <div class="p-rm-name"><?php echo $reviewer_name; ?></div>
                                <div class="p-rm-designation"><?php echo $location_new_line; ?></div>
                            </div>
                        </div>
                    </div>
                <div class="rm-bg-overlay"></div>
                </div>
            <?php } ?>
        </div>
        <!-- Popup Section End -->
        
        <?php
            $mryrm_review_count = count($mryrm_rating);
            $mryrm_review_sum = array_sum($mryrm_rating);            $mryrm_avg_rating = $mryrm_review_sum / $mryrm_review_count;

        ?>    
    
    <?php if(!empty($mryrm_setting)){ ?>
        <div style="display:none;">
            <meta itemprop="name" content="<?php echo $mryrm_setting->org_name; ?>">
            <div itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">
                <meta itemprop="streetAddress" content="<?php echo $mryrm_setting->org_address_line; ?>">
                <meta itemprop="addressLocality" content="<?php echo $mryrm_setting->city; ?>">
                <meta itemprop="addressRegion" content="<?php echo $mryrm_setting->state; ?>">
                <meta itemprop="postalCode" content="<?php echo $mryrm_setting->org_zipcode; ?>">
                <meta itemprop="addressCountry" content="US">
            </div>
            <meta itemprop="url" content="<?php echo $mryrm_setting->org_url; ?>">
            <meta itemprop="logo" content="<?php echo $mryrm_setting->org_logo_url; ?>">
            <meta itemprop="image" content="<?php echo $mryrm_setting->org_logo_url; ?>">
            <meta itemprop="priceRange" content="$$$">
            <meta itemprop="telePhone" content="<?php echo $mryrm_setting->org_phone; ?>">
           <div itemprop="AggregateRating" itemscope itemtype="schema.org/AggregateRating">         
               <meta itemprop="ratingValue" content="<?php echo $mryrm_avg_rating; ?>.0">
               <meta itemprop="bestRating" content="<?php echo max($mryrm_rating); ?>.0">
               <meta itemprop="worstRating" content="1.0">
               <meta itemprop="reviewCount" content="<?php echo $mryrm_review_count; ?>">                                      
               <meta itemprop="name" content="<?php echo $mryrm_setting->org_name; ?>">                                     
           </div>
        </div>         
    <?php } ?>
         
    </div>
    <?php
   
}

/* ABC NEW FRONT END */