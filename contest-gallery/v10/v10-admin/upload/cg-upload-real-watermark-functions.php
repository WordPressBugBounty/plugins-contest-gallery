<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_upload_form_real_watermark_normalize_gallery_version')){
    function cg_upload_form_real_watermark_normalize_gallery_version($dbGalleryVersion){
        $dbGalleryVersion = trim((string)$dbGalleryVersion);
        if($dbGalleryVersion===''){
            return '';
        }
        return preg_replace('/[^0-9.].*$/', '', $dbGalleryVersion);
    }
}

if(!function_exists('cg_upload_form_real_watermark_supported')){
    function cg_upload_form_real_watermark_supported($dbGalleryVersion){
        return true;
    }
}

if(!function_exists('cg_upload_form_legacy_watermark_supported')){
    function cg_upload_form_legacy_watermark_supported($dbGalleryVersion){
        $dbGalleryVersion = cg_upload_form_real_watermark_normalize_gallery_version($dbGalleryVersion);
        return ($dbGalleryVersion==='' || version_compare($dbGalleryVersion, '30.0.2', '<'));
    }
}

if(!function_exists('cg_upload_form_real_watermark_is_pro_version')){
    function cg_upload_form_real_watermark_is_pro_version(){
        return (function_exists('cg_get_version') && cg_get_version()==='contest-gallery-pro');
    }
}

if(!function_exists('cg_upload_form_real_watermark_default_settings')){
    function cg_upload_form_real_watermark_default_settings(){
        $size = cg_upload_form_real_watermark_is_pro_version() ? '64' : '512';
        return array(
            'WatermarkTitle' => 'Contest Gallery',
            'WatermarkPosition' => 'center',
            'WatermarkSize' => $size
        );
    }
}

if(!function_exists('cg_upload_form_real_watermark_sanitize_settings')){
    function cg_upload_form_real_watermark_sanitize_settings($settings){
        $default = cg_upload_form_real_watermark_default_settings();
        if(!is_array($settings)){
            $settings = array();
        }

        $title = isset($settings['WatermarkTitle']) ? sanitize_text_field(wp_unslash($settings['WatermarkTitle'])) : $default['WatermarkTitle'];
        $title = trim($title);
        if($title===''){
            $title = $default['WatermarkTitle'];
        }
        if(function_exists('mb_substr')){
            $title = mb_substr($title, 0, 256);
        }else{
            $title = substr($title, 0, 256);
        }

        $positions = array('center','upperLeft','upperRight','lowerRight','lowerLeft');
        $position = isset($settings['WatermarkPosition']) ? sanitize_text_field(wp_unslash($settings['WatermarkPosition'])) : $default['WatermarkPosition'];
        if(!in_array($position, $positions, true)){
            $position = $default['WatermarkPosition'];
        }

        $sizes = array('512','256','128','64','32','16','8');
        $size = isset($settings['WatermarkSize']) ? sanitize_text_field(wp_unslash($settings['WatermarkSize'])) : $default['WatermarkSize'];
        if(!in_array($size, $sizes, true)){
            $size = $default['WatermarkSize'];
        }

        if(!cg_upload_form_real_watermark_is_pro_version()){
            $position = 'center';
            if($size !== '256'){
                $size = '512';
            }
        }

        return array(
            'WatermarkTitle' => $title,
            'WatermarkPosition' => $position,
            'WatermarkSize' => $size
        );
    }
}

if(!function_exists('cg_upload_form_real_watermark_get_settings')){
    function cg_upload_form_real_watermark_get_settings($value){
        $settings = cg_upload_form_real_watermark_default_settings();
        if(!empty($value->RealWatermarkSettings)){
            $storedSettings = maybe_unserialize($value->RealWatermarkSettings);
            if(is_array($storedSettings)){
                $settings = cg_upload_form_real_watermark_sanitize_settings($storedSettings);
            }
        }
        return $settings;
    }
}

if(!function_exists('cg_upload_form_real_watermark_get_stored_array')){
    function cg_upload_form_real_watermark_get_stored_array($settings){
        if(empty($settings)){
            return false;
        }
        if(!is_array($settings)){
            $settings = maybe_unserialize($settings);
        }
        if(!is_array($settings)){
            return false;
        }
        return $settings;
    }
}

if(!function_exists('cg_upload_form_real_watermark_is_active')){
    function cg_upload_form_real_watermark_is_active($settings){
        $settings = cg_upload_form_real_watermark_get_stored_array($settings);
        if(empty($settings)){
            return false;
        }
        if(array_key_exists('WatermarkIsActive', $settings)){
            return !empty($settings['WatermarkIsActive']);
        }
        return true;
    }
}

if(!function_exists('cg_upload_options_real_watermark_get_settings')){
    function cg_upload_options_real_watermark_get_settings($value){
        $settings = cg_upload_form_real_watermark_default_settings();
        if(!empty($value->UploadRealWatermarkSettings)){
            $storedSettings = maybe_unserialize($value->UploadRealWatermarkSettings);
            if(is_array($storedSettings)){
                $settings = cg_upload_form_real_watermark_sanitize_settings($storedSettings);
            }
        }
        return $settings;
    }
}

if(!function_exists('cg_upload_options_real_watermark_get_stored_array')){
    function cg_upload_options_real_watermark_get_stored_array($settings){
        if(empty($settings)){
            return false;
        }
        if(!is_array($settings)){
            $settings = maybe_unserialize($settings);
        }
        if(!is_array($settings)){
            return false;
        }
        return $settings;
    }
}

if(!function_exists('cg_upload_options_real_watermark_is_active')){
    function cg_upload_options_real_watermark_is_active($settings){
        $settings = cg_upload_options_real_watermark_get_stored_array($settings);
        if(empty($settings)){
            return false;
        }
        if(array_key_exists('WatermarkIsActive', $settings)){
            return !empty($settings['WatermarkIsActive']);
        }
        return true;
    }
}

if(!function_exists('cg_upload_options_real_watermark_prepare_for_storage')){
    function cg_upload_options_real_watermark_prepare_for_storage($post){
        $settings = array(
            'WatermarkTitle' => isset($post['UploadRealWatermarkTitle']) ? $post['UploadRealWatermarkTitle'] : '',
            'WatermarkPosition' => isset($post['UploadRealWatermarkPosition']) ? $post['UploadRealWatermarkPosition'] : '',
            'WatermarkSize' => isset($post['UploadRealWatermarkSize']) ? $post['UploadRealWatermarkSize'] : ''
        );
        $settings = cg_upload_form_real_watermark_sanitize_settings($settings);
        $settings['WatermarkIsActive'] = empty($post['UploadRealWatermarkChecked']) ? 0 : 1;
        return serialize($settings);
    }
}

if(!function_exists('cg_upload_options_real_watermark_get_json_value')){
    function cg_upload_options_real_watermark_get_json_value($settings){
        if(!cg_upload_options_real_watermark_is_active($settings)){
            return '';
        }
        $settings = cg_upload_options_real_watermark_get_stored_array($settings);
        if(!is_array($settings)){
            return '';
        }
        return cg_upload_form_real_watermark_sanitize_settings($settings);
    }
}

if(!function_exists('cg_upload_form_real_watermark_get_selected_upload_key')){
    function cg_upload_form_real_watermark_get_selected_upload_key($uploadPost, $dbGalleryVersion){
        if(!cg_upload_form_real_watermark_supported($dbGalleryVersion) || !is_array($uploadPost)){
            return '';
        }
        $selectedUploadKey = '';
        foreach($uploadPost as $uploadKey => $field){
            if(!empty($field['realWatermarkChecked'])){
                $selectedUploadKey = (string)$uploadKey;
            }
        }
        return $selectedUploadKey;
    }
}

if(!function_exists('cg_upload_form_legacy_watermark_get_selected_upload_key')){
    function cg_upload_form_legacy_watermark_get_selected_upload_key($uploadPost, $dbGalleryVersion, $realWatermarkSelectedUploadKey){
        if(!cg_upload_form_legacy_watermark_supported($dbGalleryVersion) || !is_array($uploadPost) || $realWatermarkSelectedUploadKey!==''){
            return '';
        }
        $selectedUploadKey = '';
        foreach($uploadPost as $uploadKey => $field){
            if(!empty($field['watermarkChecked']) && !empty($field['watermarkPosition'])){
                $selectedUploadKey = (string)$uploadKey;
            }
        }
        return $selectedUploadKey;
    }
}

if(!function_exists('cg_upload_form_real_watermark_prepare_for_storage')){
    function cg_upload_form_real_watermark_prepare_for_storage($field, $dbGalleryVersion, $uploadKey, $selectedUploadKey){
        if(!cg_upload_form_real_watermark_supported($dbGalleryVersion)){
            return '';
        }
        $default = cg_upload_form_real_watermark_default_settings();
        $settings = array(
            'WatermarkTitle' => $default['WatermarkTitle'],
            'WatermarkPosition' => isset($field['realWatermarkPosition']) ? $field['realWatermarkPosition'] : '',
            'WatermarkSize' => isset($field['realWatermarkSize']) ? $field['realWatermarkSize'] : ''
        );
        $settings = cg_upload_form_real_watermark_sanitize_settings($settings);
        $settings['WatermarkIsActive'] = ((string)$uploadKey === (string)$selectedUploadKey && !empty($field['realWatermarkChecked'])) ? 1 : 0;
        return serialize($settings);
    }
}

if(!function_exists('cg_upload_form_real_watermark_update_saved_field')){
    function cg_upload_form_real_watermark_update_saved_field($tablename_form_input, $fieldId, $realWatermarkSettings){
        global $wpdb;
        $fieldId = absint($fieldId);
        if(empty($fieldId)){
            return;
        }
        $wpdb->update(
            "$tablename_form_input",
            array('RealWatermarkSettings' => $realWatermarkSettings),
            array('id' => $fieldId),
            array('%s'),
            array('%d')
        );
    }
}

if(!function_exists('cg_upload_form_legacy_watermark_position_from_field')){
    function cg_upload_form_legacy_watermark_position_from_field($field, $dbGalleryVersion, $uploadKey, $selectedUploadKey){
        if(!cg_upload_form_legacy_watermark_supported($dbGalleryVersion) || $selectedUploadKey==='' || (string)$uploadKey !== (string)$selectedUploadKey || empty($field['watermarkChecked']) || empty($field['watermarkPosition'])){
            return '';
        }
        $position = sanitize_text_field(wp_unslash($field['watermarkPosition']));
        $positions = array('top-left','top-right','bottom-left','bottom-right','center');
        if(!in_array($position, $positions, true)){
            return '';
        }
        return $position;
    }
}

if(!function_exists('cg_upload_form_render_legacy_watermark_option')){
    function cg_upload_form_render_legacy_watermark_option($id, $value, $cgProFalse, $addBorderLeftNone, $forceUnchecked){
        $watermarkPosition = 'top-left';
        $watermarkPositionDisabled = 'cg_disabled_watermark';
        $checkedWatermark = '';
        if(!empty($value->WatermarkPosition) && trim($value->WatermarkPosition)!==''){
            $watermarkPosition = $value->WatermarkPosition;
        }
        if(!$forceUnchecked && !empty($value->WatermarkPosition) && trim($value->WatermarkPosition)!==''){
            $watermarkPositionDisabled = '';
            $checkedWatermark = "checked='checked'";
        }

        $borderLeftNone = $addBorderLeftNone ? ' cg_border_left_none' : '';
        $positions = array(
            'top-left' => 'Top Left',
            'top-right' => 'Top Right',
            'bottom-left' => 'Bottom Left',
            'bottom-right' => 'Bottom Right',
            'center' => 'Center'
        );

        echo "<div class='cg_view_options_row'>";
        echo "<div class='cg_view_option $cgProFalse cg_view_option_100_percent cg_view_option_watermark cg_border_bottom_none$borderLeftNone'>";
        echo "<div class='cg_view_option_title'><p>Use as watermark for gallery images:<br>(only 1 allowed)<br><span class='cg_view_option_title_note'><b>NOTE:</b> CSS based, original image source will be not watermarked</span></p></div>";
        echo "<div class='cg_view_option_checkbox'><input type='checkbox' name='upload[$id][watermarkChecked]' $checkedWatermark></div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='cg_view_options_row'>";
        echo "<div class='cg_view_option $cgProFalse cg_view_option_100_percent cg_view_option_not_disable cg_border_bottom_none cg_view_option_watermark_position cg_view_option_flex_flow_column $watermarkPositionDisabled$borderLeftNone'>";
        echo "<div class='cg_view_option_title cg_view_option_title_full_width'><p>Watermark position</p></div>";
        echo "<div class='cg_view_option_select cg_view_option_input_full_width'><select class='cg_watermark_position' name='upload[$id][watermarkPosition]'>";
        foreach($positions as $positionValue => $positionText){
            $selected = ($watermarkPosition===$positionValue) ? ' selected' : '';
            echo "<option value='".esc_attr($positionValue)."'$selected>".esc_html($positionText)."</option>";
        }
        echo "</select></div>";
        echo "</div>";
        echo "</div>";
    }
}

if(!function_exists('cg_upload_form_render_real_watermark_option')){
    function cg_upload_form_render_real_watermark_option($id, $value, $addBorderLeftNone){
        $settings = cg_upload_form_real_watermark_get_settings($value);
        $isChecked = cg_upload_form_real_watermark_is_active($value->RealWatermarkSettings);
        $checkedWatermark = $isChecked ? "checked='checked'" : '';
        $checkboxClass = $isChecked ? 'cg_view_option_checked' : 'cg_view_option_unchecked';
        $settingsDisabledClass = $isChecked ? '' : ' cg_disabled_watermark';
        $borderLeftNone = $addBorderLeftNone ? ' cg_border_left_none' : '';
        $isProVersion = cg_upload_form_real_watermark_is_pro_version();

        echo "<div class='cg_view_options_row'>";
        echo "<div class='cg_view_option cg_view_option_100_percent cg_border_bottom_none$borderLeftNone cg_view_option_watermark cg_view_option_real_watermark cg_view_option_checkbox_container'>";
        echo "<div class='cg_view_option_title'><p>Use as real watermark for gallery images:<br>(only 1 allowed)<br><span class='cg_view_option_title_note'><b>NOTE:</b> Uses the submitted value of this field as watermark text. Will overwrite \"Upload options watermark settings\"</span></p></div>";
        echo "<div class='cg_view_option_checkbox $checkboxClass'><input type='checkbox' name='upload[$id][realWatermarkChecked]' $checkedWatermark></div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='cg_view_options_row'>";
        echo "<div class='cg_view_option cg_view_option_100_percent cg_border_bottom_none$borderLeftNone cg_view_option_not_disable cg_view_option_flex_flow_column cg_view_option_real_watermark_settings cg_upload_real_watermark_settings_panel$settingsDisabledClass'>";
        echo "<div class='cg_entry_watermark_settings'>";
        echo "<label>Position</label>";
        echo "<select class='cg_real_watermark_position' name='upload[$id][realWatermarkPosition]'>";
        $positions = array(
            'center' => 'Center',
            'upperLeft' => 'Upper left',
            'upperRight' => 'Upper right',
            'lowerRight' => 'Lower right',
            'lowerLeft' => 'Lower left'
        );
        foreach($positions as $positionValue => $positionText){
            $selected = ($settings['WatermarkPosition']===$positionValue) ? ' selected' : '';
            $isProOnlyOption = (!$isProVersion && $positionValue!=='center') ? true : false;
            $class = $isProOnlyOption ? " class='cg-pro-false'" : '';
            $proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
            echo "<option value='".esc_attr($positionValue)."'$selected$class>".esc_html($positionText)."$proText</option>";
        }
        echo "</select>";
        echo "<label>Size</label>";
        echo "<select class='cg_real_watermark_size' name='upload[$id][realWatermarkSize]'>";
        foreach(array(512,256,128,64,32,16,8) as $size){
            $selected = ($settings['WatermarkSize']===(string)$size) ? ' selected' : '';
            $isProOnlyOption = (!$isProVersion && !in_array($size, array(512,256), true)) ? true : false;
            $class = $isProOnlyOption ? " class='cg-pro-false'" : '';
            $proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
            $sizeLabel = function_exists('cg_get_watermark_size_label') ? cg_get_watermark_size_label($size) : $size;
            echo "<option value='".esc_attr($size)."'$selected$class>".esc_html($sizeLabel)."$proText</option>";
        }
        echo "</select>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}

if(!function_exists('cg_upload_options_real_watermark_render')){
    function cg_upload_options_real_watermark_render($settings, $isChecked){
        $isChecked = !empty($isChecked);
        $checkedWatermark = $isChecked ? "checked='checked'" : '';
        $checkboxClass = $isChecked ? 'cg_view_option_checked' : 'cg_view_option_unchecked';
        $settingsDisabledClass = $isChecked ? '' : ' cg_disabled_watermark';
        $isProVersion = cg_upload_form_real_watermark_is_pro_version();
        $isProData = $isProVersion ? '1' : '0';

        echo "<div class='cg_view_options_rows_container'>";
        echo "<p class='cg_view_options_rows_container_title'>Real watermark for frontend uploads<br><span class='cg_view_options_rows_container_title_note'><span class='cg_font_weight_bold'>NOTE:</span> Default settings for uploaded gallery images. Upload form field watermark settings overwrite these upload option watermark settings.</span></p>";
        echo "<div class='cg_view_options_row'>";
        echo "<div class='cg_view_option cg_view_option_100_percent cg_border_bottom_none cg_view_option_upload_options_real_watermark cg_view_option_checkbox_container'>";
        echo "<div class='cg_view_option_title'><p>Use real watermark for uploaded gallery images</p></div>";
        echo "<div class='cg_view_option_checkbox $checkboxClass'><input type='checkbox' name='UploadRealWatermarkChecked' id='UploadRealWatermarkChecked' $checkedWatermark></div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='cg_view_options_row cg_margin_bottom_30'>";
        echo "<div class='cg_view_option cg_view_option_100_percent cg_border_top_none cg_view_option_not_disable cg_view_option_flex_flow_column cg_view_option_upload_options_real_watermark_settings cg_upload_real_watermark_settings_panel$settingsDisabledClass' data-cg-is-pro='$isProData'>";
        echo "<div class='cg_entry_watermark_settings'>";
        echo "<label>Text</label>";
        echo "<input type='text' name='UploadRealWatermarkTitle' maxlength='256' value='".esc_attr($settings['WatermarkTitle'])."'>";
        echo "<label>Position</label>";
        echo "<select class='cg_real_watermark_position cg_upload_options_real_watermark_position' name='UploadRealWatermarkPosition'>";
        $positions = array(
            'center' => 'Center',
            'upperLeft' => 'Upper left',
            'upperRight' => 'Upper right',
            'lowerRight' => 'Lower right',
            'lowerLeft' => 'Lower left'
        );
        foreach($positions as $positionValue => $positionText){
            $selected = ($settings['WatermarkPosition']===$positionValue) ? ' selected' : '';
            $isProOnlyOption = (!$isProVersion && $positionValue!=='center') ? true : false;
            $class = $isProOnlyOption ? " class='cg-pro-false'" : '';
            $proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
            echo "<option value='".esc_attr($positionValue)."'$selected$class>".esc_html($positionText)."$proText</option>";
        }
        echo "</select>";
        echo "<label>Size</label>";
        echo "<select class='cg_real_watermark_size cg_upload_options_real_watermark_size' name='UploadRealWatermarkSize'>";
        foreach(array(512,256,128,64,32,16,8) as $size){
            $selected = ($settings['WatermarkSize']===(string)$size) ? ' selected' : '';
            $isProOnlyOption = (!$isProVersion && !in_array($size, array(512,256), true)) ? true : false;
            $class = $isProOnlyOption ? " class='cg-pro-false'" : '';
            $proText = $isProOnlyOption ? " <span class='cg-pro-false-text'>(PRO)</span>" : '';
            $sizeLabel = function_exists('cg_get_watermark_size_label') ? cg_get_watermark_size_label($size) : $size;
            echo "<option value='".esc_attr($size)."'$selected$class>".esc_html($sizeLabel)."$proText</option>";
        }
        echo "</select>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
}

if(!function_exists('cg_upload_form_render_watermark_option')){
    function cg_upload_form_render_watermark_option($id, $value, $dbGalleryVersion, $cgProFalse, $addBorderLeftNone){
        if(cg_upload_form_legacy_watermark_supported($dbGalleryVersion)){
            $realWatermarkSettings = isset($value->RealWatermarkSettings) ? $value->RealWatermarkSettings : '';
            cg_upload_form_render_legacy_watermark_option($id, $value, $cgProFalse, $addBorderLeftNone, cg_upload_form_real_watermark_is_active($realWatermarkSettings));
            cg_upload_form_render_real_watermark_option($id, $value, $addBorderLeftNone);
        }else{
            cg_upload_form_render_real_watermark_option($id, $value, $addBorderLeftNone);
        }
    }
}
