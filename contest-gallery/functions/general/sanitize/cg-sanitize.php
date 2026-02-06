<?php

if(!function_exists('cg1l_ip_in_cidr')){
    function cg1l_ip_in_cidr($ip, $cidr) {
        // English comment: Check if IPv4 is inside CIDR range
        $parts = explode('/', $cidr);
        if (count($parts) !== 2) { return false; }

        $subnet = $parts[0];
        $mask = intval($parts[1]);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) { return false; }
        if ($mask < 0 || $mask > 32) { return false; }

        $maskLong = -1 << (32 - $mask);
        return (($ipLong & $maskLong) === ($subnetLong & $maskLong));
    }
}


if(!function_exists('cg1l_is_blocked_ip')){
    function cg1l_is_blocked_ip($ip) {
        // English comment: Block localhost, private, link-local, and reserved ranges (SSRF protection)
        $blocked = [
            '127.0.0.0/8',      // localhost
            '10.0.0.0/8',       // private
            '172.16.0.0/12',    // private
            '192.168.0.0/16',   // private
            '169.254.0.0/16',   // link-local (cloud metadata often reachable)
            '0.0.0.0/8',        // "this" network
            '100.64.0.0/10',    // carrier-grade NAT
            '192.0.0.0/24',     // reserved
            '192.0.2.0/24',     // TEST-NET
            '198.18.0.0/15',    // benchmark testing
            '198.51.100.0/24',  // TEST-NET
            '203.0.113.0/24',   // TEST-NET
            '224.0.0.0/4',      // multicast
            '240.0.0.0/4'       // reserved
        ];

        foreach ($blocked as $cidr) {
            if (cg1l_ip_in_cidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }
}

if(!function_exists('cg1l_is_safe_remote_url')){
    function cg1l_is_safe_remote_url($url) {
        // English comment: Minimal SSRF guard for http/https URLs and public IPs only
        if (empty($url) || !is_string($url)) { return false; }

        $parts = wp_parse_url($url);
        if (empty($parts) || empty($parts['scheme']) || empty($parts['host'])) { return false; }

        $scheme = strtolower($parts['scheme']);
        if ($scheme !== 'http' && $scheme !== 'https') { return false; }

        $host = strtolower($parts['host']);

        // English comment: Quick host blocks
        if ($host === 'localhost' || substr($host, -6) === '.local') { return false; }
        if ($host === '127.0.0.1' || $host === '::1') { return false; }

        // English comment: If host is an IP, validate directly (IPv4 only in this minimal guard)
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (cg1l_is_blocked_ip($host)) { return false; }
            return true;
        }

        // English comment: Resolve DNS and block if it points to private/local/reserved IPv4
        $resolved = gethostbynamel($host);
        if (empty($resolved) || !is_array($resolved)) { return false; }

        foreach ($resolved as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                if (cg1l_is_blocked_ip($ip)) { return false; }
            } else {
                return false;
            }
        }

        return true;
    }
}


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
			$value= str_ireplace(['onmouseover','autofocus','onfocus','style=','style =','style  ='],'',sanitize_textarea_field(wp_unslash($value)));// source https://developer.wordpress.org/reference/functions/sanitize_textarea_field/
		}else{
			$value = str_ireplace(['onmouseover','autofocus','onfocus','style=','style =','style  ='],'',sanitize_text_field(wp_unslash($value)));
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

if(!function_exists('cg1l_sanitize_atts')){
	function cg1l_sanitize_atts ($atts) {

		if(is_array($atts)){
            $newAtts = [];
			foreach($atts as $key => $value){
                $newAtts[$key] = cg1l_sanitize_method($value);
			}
            return $newAtts;
		}else{
            return $atts;
        }

	}
}


