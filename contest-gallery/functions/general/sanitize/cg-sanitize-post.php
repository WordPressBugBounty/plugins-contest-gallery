<?php

if(!function_exists('cg1l_sanitize_method')){
	function cg1l_sanitize_method ($value) {
                    $pattern = "/UNION +ALL/mi";
                    if(strpos($value, "\n") !== FALSE) {
                        // possible manual method
                        /*$value = explode( "\n", $value );
                        foreach($value as $key => $valueToSanitize){
                            $value[$key] = sanitize_text_field($valueToSanitize);
                        }
                        $value = implode( "\n", $value);
                        $POST[$key] = $value;*/
                        $value= str_ireplace(['onmouseover','autofocus','onfocus','style=','style =','style  ='],'',sanitize_textarea_field($value));// source https://developer.wordpress.org/reference/functions/sanitize_textarea_field/
                    }else{
                        $value = str_ireplace(['onmouseover','autofocus','onfocus','style=','style =','style  ='],'',sanitize_text_field($value));
                    }
					$value = esc_attr($value);
					$value = esc_html($value);
		$value = preg_replace($pattern,'',$value);
		return $value;
	}
}

if(!function_exists('cg1l_sanitize_post')){
	function cg1l_sanitize_post ($POST) {

		if(is_array($POST)){
			foreach($POST as $key => $value){
				if(is_array($POST[$key])){
					$POST[$key] = cg1l_sanitize_post($POST[$key] );
				}else{
					$POST[$key]  = cg1l_sanitize_method($value);
                    }
                }
            }
        return $POST;
    }
}


