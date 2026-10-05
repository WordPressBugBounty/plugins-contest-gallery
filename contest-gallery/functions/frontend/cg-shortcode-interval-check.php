<?php
if(!function_exists('cg_shortcode_interval_check')){
    function cg_shortcode_interval_check($GalleryID,$options,$shortcode_name,$isFromCGgalleries = false){

	    $wp_upload_dir = wp_upload_dir();

	    if($shortcode_name=='cg_users_reg' || $shortcode_name=='cg_google_sign_in' || $shortcode_name=='cg_users_login'){
		    $optionsPath = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/json/interval-conf.json';
            if(!file_exists($optionsPath)){
                file_put_contents($optionsPath,json_encode(['interval' => ['cg_users_reg' => [],'cg_google_sign_in' => [],'cg_users_login' => []]]));
            }
		    $options = json_decode(file_get_contents($optionsPath),true);
	    }

        $isActive = true;
        $shortcodeCheckIsActivated = false;
        $intervalStartDate = null;
        $intervalEndDate = null;
        $TextWhenShortcodeIntervalIsOn = '';
        $TextWhenShortcodeIntervalIsOff = '';

        // correction for 27.0.0 from cg_users_contact to cg_users_upload
        if(!empty($options['interval']['cg_users_contact']) && empty($options['interval']['cg_users_upload'])){
            $options['interval']['cg_users_upload'] = $options['interval']['cg_users_contact'];
        }

        if(isset($options['interval'][$shortcode_name])
            && isset($options['interval'][$shortcode_name]['active'])
            && $options['interval'][$shortcode_name]['active']=='on'
        ){
            $shortcodeCheckIsActivated = true;
            $TextWhenShortcodeIntervalIsOn = $options['interval'][$shortcode_name]['TextWhenShortcodeIntervalIsOn'];
            $TextWhenShortcodeIntervalIsOff = $options['interval'][$shortcode_name]['TextWhenShortcodeIntervalIsOff'];
            $isActive = false;
            // Use the WordPress timezone for both calendar dates and comparisons.
            if(function_exists('wp_timezone')){
                $timezone = wp_timezone();
            }else{
                $timezoneName = get_option('timezone_string');
                if(!$timezoneName){
                    $offset = (float)get_option('gmt_offset');
                    $timezoneName = sprintf('%s%02d:%02d',$offset<0 ? '-' : '+',abs((int)$offset),abs((int)round($offset*60))%60);
                }
                $timezone = new DateTimeZone($timezoneName);
            }
            $dateNow = new DateTime('now',$timezone);
            $currentYear = $dateNow->format('Y');
            // selectedIntervalType
            if(isset($options['interval'][$shortcode_name][$currentYear])){
                $interval = $options['interval'][$shortcode_name][$currentYear]['selectedIntervalType'];
                $currentMonthName = strtolower($dateNow->format('F'));
                if(isset($options['interval'][$shortcode_name][$currentYear])){
                    if($interval=='monthly'){
                        $fromDate = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['fromDate'];
                        $toDate = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['toDate'];
                        if($fromDate && $toDate){
                            $fromDate = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['fromDate'];
                            $toDate = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['toDate'];
                            $fromHours = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['fromHours'];
                            $fromMinutes = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['fromMinutes'];
                            $toHours = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['toHours'];
                            $toMinutes = $options['interval'][$shortcode_name][$currentYear][$interval][$currentMonthName]['toMinutes'];
                            $intervalStartDate = new DateTime($fromDate.' '.$fromHours.':'.$fromMinutes.':00',$timezone);
                            $intervalEndDate = new DateTime($toDate.' '.$toHours.':'.$toMinutes.':59',$timezone);

                            if($intervalStartDate->getTimestamp()<=$dateNow->getTimestamp()
                                && $intervalEndDate->getTimestamp()>=$dateNow->getTimestamp()){
                                $isActive = true;
                            }
                        }
                    }
                    if($interval=='weekly'){

                        $dayStart = $options['interval'][$shortcode_name][$currentYear][$interval]['dayStart'];
                        $dayEnd = $options['interval'][$shortcode_name][$currentYear][$interval]['dayEnd'];
                        if($dayStart && $dayEnd){

                            $fromHours = $options['interval'][$shortcode_name][$currentYear][$interval]['fromHours'];
                            $fromMinutes = $options['interval'][$shortcode_name][$currentYear][$interval]['fromMinutes'];
                            $toHours = $options['interval'][$shortcode_name][$currentYear][$interval]['toHours'];
                            $toMinutes = $options['interval'][$shortcode_name][$currentYear][$interval]['toMinutes'];
                            $intervalStartDate = clone $dateNow;
                            $intervalStartDate->modify("$dayStart this week")->setTime((int)$fromHours,(int)$fromMinutes,0);
                            $intervalEndDate = clone $dateNow;
                            $intervalEndDate->modify("$dayEnd this week")->setTime((int)$toHours,(int)$toMinutes,59);

                            if($intervalStartDate->getTimestamp()<=$dateNow->getTimestamp()
                                && $intervalEndDate->getTimestamp()>=$dateNow->getTimestamp()){
                                $isActive = true;
                            }
                        }
                    }
                    if($interval=='daily'){
                        $fromHours = $options['interval'][$shortcode_name][$currentYear][$interval]['fromHours'];
                        $fromMinutes = $options['interval'][$shortcode_name][$currentYear][$interval]['fromMinutes'];
                        $toHours = $options['interval'][$shortcode_name][$currentYear][$interval]['toHours'];
                        $toMinutes = $options['interval'][$shortcode_name][$currentYear][$interval]['toMinutes'];
                        // The end-minute select stores midnight/full hours as "0".
                        if(is_numeric($fromHours) && is_numeric($toHours) && is_numeric($fromMinutes) && is_numeric($toMinutes)){
                            $intervalStartDate = clone $dateNow;
                            $intervalStartDate->setTime((int)$fromHours,(int)$fromMinutes,0);
                            $intervalEndDate = clone $dateNow;
                            $intervalEndDate->setTime((int)$toHours,(int)$toMinutes,59);
                            if($intervalStartDate->getTimestamp()<=$dateNow->getTimestamp()
                                && $intervalEndDate->getTimestamp()>=$dateNow->getTimestamp()){
                                $isActive = true;
                            }
                        }
                    }
                }
            }
        }

        return [
            'shortcodeIsActive' => $isActive,
            //'shortcodeIsActive' => true,
            'shortcodeCheckIsActivated' => $shortcodeCheckIsActivated,
            'intervalStartDate' => $intervalStartDate,
            'intervalEndDate' => $intervalEndDate,
            'TextWhenShortcodeIntervalIsOn' => $TextWhenShortcodeIntervalIsOn,
            'TextWhenShortcodeIntervalIsOff' => $TextWhenShortcodeIntervalIsOff,
        ];

    }
}

if(!function_exists('cg_shortcode_interval_check_show_ajax_message')){
    function cg_shortcode_interval_check_show_ajax_message($intervalConf,$GalleryID = 0){

        if(!$intervalConf['shortcodeIsActive']){
            ?>
            <script data-cg-processing="true">
                var gid = <?php echo json_encode($GalleryID); ?>;
                var TextWhenShortcodeIntervalIsOff = <?php echo json_encode($intervalConf['TextWhenShortcodeIntervalIsOff']);?>;
                cgJsClass.gallery.function.message.showPro(gid,TextWhenShortcodeIntervalIsOff);
            </script>
            <?php
        }

    }
}
