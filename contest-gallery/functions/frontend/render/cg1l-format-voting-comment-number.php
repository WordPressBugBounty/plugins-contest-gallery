<?php

if(!function_exists('cg1l_get_voting_comment_number_format')){
    function cg1l_get_voting_comment_number_format($options = array()){
        if(
            !empty($options['visual']['VotingCommentNumberFormat']) &&
            $options['visual']['VotingCommentNumberFormat'] === 'comma'
        ){
            return 'comma';
        }

        return 'dot';
    }
}

if(!function_exists('cg1l_format_voting_comment_number')){
    function cg1l_format_voting_comment_number($value,$options = array(),$maxDecimals = 0){
        if($value === '' || $value === null || !is_numeric($value)){
            return '';
        }

        $maxDecimals = max(0,min(6,intval($maxDecimals)));
        $number = round(floatval($value),$maxDecimals);
        $decimals = 0;

        if($maxDecimals > 0){
            $normalized = number_format($number,$maxDecimals,'.','');
            $normalized = rtrim(rtrim($normalized,'0'),'.');
            $decimalPosition = strpos($normalized,'.');
            if($decimalPosition !== false){
                $decimals = strlen($normalized) - $decimalPosition - 1;
            }
        }

        if(cg1l_get_voting_comment_number_format($options) === 'comma'){
            return number_format($number,$decimals,',','.');
        }

        return number_format($number,$decimals,'.',',');
    }
}
