<?php

// source https://stackoverflow.com/questions/13818064/check-if-an-ip-address-is-private
if(!function_exists('cg_check_if_ip_is_private')){
    function cg_check_if_ip_is_private ($ip) {
        $pri_addrs = array (
            '10.0.0.0|10.255.255.255', // single class A network
            '172.16.0.0|172.31.255.255', // 16 contiguous class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous class C network
            '169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
            '127.0.0.0|127.255.255.255' // localhost
        );

        $long_ip = ip2long ($ip);
        if ($long_ip != -1) {

            foreach ($pri_addrs AS $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);

                // IF IS PRIVATE
                if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if(!function_exists('cg_available_ip_getter_types')){
    function cg_available_ip_getter_types () {

        $array = array();

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $array[] = 'HTTP_CLIENT_IP';
        }
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $array[] = 'REMOTE_ADDR';
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $array[] = 'HTTP_X_FORWARDED_FOR';
        }
        if (!empty($_SERVER['REMOTE_HOST'])) {
            $array[] = 'REMOTE_HOST';
        }
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $array[] = 'HTTP_X_CLUSTER_CLIENT_IP';
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $array[] = 'HTTP_FORWARDED_FOR';
        }
        if (!empty($_SERVER['HTTP_FORWARDED'])) {
            $array[] = 'HTTP_FORWARDED';
        }

        return $array;

    }
}


if(!function_exists('cg_normalize_user_ip')){
    function cg_normalize_user_ip($ip){
        if(!is_string($ip) || !filter_var($ip,FILTER_VALIDATE_IP)){return '';}
        $packed = inet_pton($ip);
        return $packed===false ? '' : inet_ntop($packed);
    }
}

if(!function_exists('cg_user_ip_matches_proxy')){
    function cg_user_ip_matches_proxy($ip,$proxy){
        if(!is_string($proxy)){return false;}
        $parts = explode('/',$proxy);
        if(count($parts)>2){return false;}
        $network = cg_normalize_user_ip($parts[0]);
        if($network===''){return false;}
        $addressBytes = inet_pton($ip);
        $networkBytes = inet_pton($network);
        if($addressBytes===false || strlen($addressBytes)!==strlen($networkBytes)){return false;}
        $bits = strlen($networkBytes)*8;
        if(count($parts)===2){
            if(!preg_match('/^[0-9]{1,3}$/D',$parts[1]) || intval($parts[1])>$bits){return false;}
            $bits = intval($parts[1]);
        }
        $bytes = intval($bits/8);
        if(substr($addressBytes,0,$bytes)!==substr($networkBytes,0,$bytes)){return false;}
        $remaining = $bits%8;
        if(!$remaining){return true;}
        $mask = (255 << (8-$remaining)) & 255;
        return (ord($addressBytes[$bytes]) & $mask)===(ord($networkBytes[$bytes]) & $mask);
    }
}

if(!function_exists('cg_user_ip_is_trusted_proxy')){
    function cg_user_ip_is_trusted_proxy($ip,$proxies){
        foreach($proxies as $proxy){if(cg_user_ip_matches_proxy($ip,$proxy)){return true;}}
        return false;
    }
}

if(!function_exists('cg_resolve_user_ip')){
    function cg_resolve_user_ip(){
        $remote = isset($_SERVER['REMOTE_ADDR']) ? cg_normalize_user_ip($_SERVER['REMOTE_ADDR']) : '';
        $result = array('ip'=>$remote!=='' ? $remote : 'unknown','type'=>$remote!=='' ? 'REMOTE_ADDR' : 'unknown');
        if($remote===''){return $result;}
        // Private peers are not automatically trusted. Configure the actual proxy IPs/CIDRs server-side.
        $proxies = apply_filters('cg_trusted_proxies',array());
        if(!is_array($proxies) || !cg_user_ip_is_trusted_proxy($remote,$proxies)){return $result;}
        $header = apply_filters('cg_trusted_proxy_header','HTTP_X_FORWARDED_FOR');
        if(!in_array($header,array('HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','HTTP_CLIENT_IP'),true)){return $result;}
        if(empty($_SERVER[$header]) || !is_string($_SERVER[$header]) || strlen($_SERVER[$header])>4096){return $result;}
        $forwarded = $_SERVER[$header];
        if($header!=='HTTP_X_FORWARDED_FOR'){
            $client = cg_normalize_user_ip(trim($forwarded));
            return $client!=='' ? array('ip'=>$client,'type'=>$header) : $result;
        }
        $chain = explode(',',$forwarded);
        if(count($chain)>32){return $result;}
        foreach($chain as $index=>$hop){
            $chain[$index] = cg_normalize_user_ip(trim($hop));
            if($chain[$index]===''){return $result;}
        }
        // Ignore attacker-controlled prefixes to the left of the first untrusted hop.
        for($index=count($chain)-1;$index>=0;$index--){
            if(!cg_user_ip_is_trusted_proxy($chain[$index],$proxies)){
                return array('ip'=>$chain[$index],'type'=>$header);
            }
        }
        return $result;
    }
}

add_action('cg_get_user_ip','cg_get_user_ip');
if(!function_exists('cg_get_user_ip')){
    function cg_get_user_ip(){
        $result = cg_resolve_user_ip();
        return $result['ip'];
    }
}
