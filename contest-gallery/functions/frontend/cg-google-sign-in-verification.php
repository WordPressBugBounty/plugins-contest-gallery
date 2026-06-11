<?php

if(!function_exists('cg_google_sign_in_get_openssl_status')){
    function cg_google_sign_in_get_openssl_status(){
        return [
            'openssl_verify' => function_exists('openssl_verify'),
            'openssl_pkey_get_public' => function_exists('openssl_pkey_get_public'),
        ];
    }
}

if(!function_exists('cg_google_sign_in_get_openssl_status_text')){
    function cg_google_sign_in_get_openssl_status_text(){
        $opensslStatus = cg_google_sign_in_get_openssl_status();
        $statusText = ($opensslStatus['openssl_verify'] && $opensslStatus['openssl_pkey_get_public']) ? 'OpenSSL is active here:' : 'OpenSSL is not fully available here:';

        return $statusText.' openssl_verify='.(($opensslStatus['openssl_verify']) ? 'yes' : 'no').', openssl_pkey_get_public='.(($opensslStatus['openssl_pkey_get_public']) ? 'yes' : 'no').'.';
    }
}

if(!function_exists('cg_google_sign_in_log_verification_error')){
    function cg_google_sign_in_log_verification_error($errorMessage){
        if(defined('WP_DEBUG') && WP_DEBUG && !empty($errorMessage)){
            error_log('Contest Gallery Google sign in verification failed: '.$errorMessage);
        }
    }
}

if(!function_exists('cg_google_sign_in_base64_url_decode')){
    function cg_google_sign_in_base64_url_decode($value){
        $value = str_replace(array('-', '_'), array('+', '/'), $value);
        $padding = strlen($value) % 4;
        if($padding){
            $value .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($value, true);
    }
}

if(!function_exists('cg_google_sign_in_decode_json_token_part')){
    function cg_google_sign_in_decode_json_token_part($value){
        $decoded = cg_google_sign_in_base64_url_decode($value);
        if($decoded === false){
            return false;
        }
        $json = json_decode($decoded, true);
        if(!is_array($json)){
            return false;
        }
        return $json;
    }
}

if(!function_exists('cg_google_sign_in_get_cert_cache_ttl')){
    function cg_google_sign_in_get_cert_cache_ttl($cacheControlHeader){
        $ttl = 3600;

        if(is_array($cacheControlHeader)){
            $cacheControlHeader = implode(',', $cacheControlHeader);
        }

        if(!empty($cacheControlHeader) && preg_match('/max-age=([0-9]+)/', $cacheControlHeader, $matches)){
            $ttl = intval($matches[1]);
        }

        if($ttl < 300){
            $ttl = 300;
        }

        return $ttl;
    }
}

if(!function_exists('cg_google_sign_in_get_public_certs')){
    function cg_google_sign_in_get_public_certs(&$errorMessage, $forceRefresh = false){
        $transientKey = 'cg_google_sign_in_public_certs';

        if(!$forceRefresh){
            $cached = get_transient($transientKey);
            if(is_array($cached) && !empty($cached)){
                return $cached;
            }
        }else{
            delete_transient($transientKey);
        }

        $response = wp_remote_get('https://www.googleapis.com/oauth2/v1/certs', array(
            'timeout' => 10,
        ));

        if(is_wp_error($response)){
            $errorMessage = $response->get_error_message();
            return false;
        }

        $responseCode = intval(wp_remote_retrieve_response_code($response));
        if($responseCode !== 200){
            $errorMessage = 'Google public certificates could not be loaded. HTTP status: '.$responseCode;
            return false;
        }

        $certs = json_decode(wp_remote_retrieve_body($response), true);
        if(!is_array($certs) || empty($certs)){
            $errorMessage = 'Google public certificates response could not be parsed.';
            return false;
        }

        set_transient(
            $transientKey,
            $certs,
            cg_google_sign_in_get_cert_cache_ttl(wp_remote_retrieve_header($response, 'cache-control'))
        );

        return $certs;
    }
}

if(!function_exists('cg_google_sign_in_is_email_verified')){
    function cg_google_sign_in_is_email_verified($payload){
        if(!isset($payload['email_verified'])){
            return false;
        }

        return ($payload['email_verified'] === true || $payload['email_verified'] === 'true' || $payload['email_verified'] === 1 || $payload['email_verified'] === '1');
    }
}

if(!function_exists('cg_google_sign_in_verify_id_token')){
    function cg_google_sign_in_verify_id_token($CLIENT_ID, $id_token, &$errorMessage){
        if(empty($CLIENT_ID)){
            $errorMessage = 'Google client id is missing.';
            return false;
        }

        if(empty($id_token) || strlen($id_token) > 10000){
            $errorMessage = 'Google ID token is missing or too long.';
            return false;
        }

        $opensslStatus = cg_google_sign_in_get_openssl_status();
        if(!$opensslStatus['openssl_verify'] || !$opensslStatus['openssl_pkey_get_public']){
            $errorMessage = cg_google_sign_in_get_openssl_status_text().' PHP OpenSSL support is required for Google ID token verification.';
            return false;
        }

        $tokenParts = explode('.', $id_token);
        if(count($tokenParts) !== 3){
            $errorMessage = 'Google ID token format is invalid.';
            return false;
        }

        $header = cg_google_sign_in_decode_json_token_part($tokenParts[0]);
        $payload = cg_google_sign_in_decode_json_token_part($tokenParts[1]);
        $signature = cg_google_sign_in_base64_url_decode($tokenParts[2]);

        if(!is_array($header) || !is_array($payload) || $signature === false){
            $errorMessage = 'Google ID token could not be decoded.';
            return false;
        }

        if(empty($header['alg']) || $header['alg'] !== 'RS256'){
            $errorMessage = 'Google ID token uses an unsupported signature algorithm.';
            return false;
        }

        if(empty($header['kid'])){
            $errorMessage = 'Google ID token key id is missing.';
            return false;
        }

        $certs = cg_google_sign_in_get_public_certs($errorMessage);
        if(!$certs || empty($certs[$header['kid']])){
            $certs = cg_google_sign_in_get_public_certs($errorMessage, true);
        }

        if(!$certs || empty($certs[$header['kid']])){
            $errorMessage = 'Matching Google public certificate was not found.';
            return false;
        }

        $publicKey = openssl_pkey_get_public($certs[$header['kid']]);
        if(!$publicKey){
            $errorMessage = 'Google public certificate could not be used.';
            return false;
        }

        $verifyResult = openssl_verify($tokenParts[0].'.'.$tokenParts[1], $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if(is_resource($publicKey)){
            openssl_free_key($publicKey);
        }

        if($verifyResult !== 1){
            $errorMessage = 'Google ID token signature is invalid.';
            return false;
        }

        $audienceIsValid = false;
        if(isset($payload['aud'])){
            if(is_array($payload['aud'])){
                $audienceIsValid = in_array($CLIENT_ID, $payload['aud'], true);
            }else{
                $audienceIsValid = ($payload['aud'] === $CLIENT_ID);
            }
        }

        if(!$audienceIsValid){
            $errorMessage = 'Google ID token audience does not match the configured client id.';
            return false;
        }

        if(isset($payload['azp']) && $payload['azp'] !== $CLIENT_ID){
            $errorMessage = 'Google ID token authorized party does not match the configured client id.';
            return false;
        }

        if(empty($payload['iss']) || !in_array($payload['iss'], array('accounts.google.com', 'https://accounts.google.com'), true)){
            $errorMessage = 'Google ID token issuer is invalid.';
            return false;
        }

        $now = time();
        $clockSkew = 300;

        if(empty($payload['exp']) || intval($payload['exp']) < ($now - $clockSkew)){
            $errorMessage = 'Google ID token is expired.';
            return false;
        }

        if(empty($payload['iat']) || intval($payload['iat']) > ($now + $clockSkew)){
            $errorMessage = 'Google ID token issued-at timestamp is invalid.';
            return false;
        }

        if(!empty($payload['nbf']) && intval($payload['nbf']) > ($now + $clockSkew)){
            $errorMessage = 'Google ID token is not valid yet.';
            return false;
        }

        if(empty($payload['sub'])){
            $errorMessage = 'Google ID token subject is missing.';
            return false;
        }

        if(empty($payload['email']) || !is_email($payload['email'])){
            $errorMessage = 'Google ID token email is missing or invalid.';
            return false;
        }

        if(!cg_google_sign_in_is_email_verified($payload)){
            $errorMessage = 'Google account email is not verified.';
            return false;
        }

        return $payload;
    }
}

if(!function_exists('cg_google_sign_in_verification_error')){
    function cg_google_sign_in_verification_error($errorMessage, $isFromUpload = false){
        cg_google_sign_in_log_verification_error($errorMessage);
        $publicMessage = 'Google client could not be verified, code 511. Please contact administrator.';

        if($isFromUpload){
            ?>
            <script data-cg-processing="true">
                cgJsClass.gallery.upload.doneUploadFailed = true;
                cgJsClass.gallery.upload.failMessage = <?php echo json_encode($publicMessage);?>;
            </script>
            <?php
            echo esc_html($publicMessage);
            die;
        }else{
            echo esc_html($publicMessage);
            ?>

            <script data-cg-processing="true">

                cgJsClass.gallery.function.message.close();
                cgJsClass.gallery.function.message.showPro(undefined,<?php echo json_encode($publicMessage);?>);

            </script>

            <?php
            die;
        }
    }
}

if(!function_exists('cg_google_sign_in_verification')){
    function cg_google_sign_in_verification($CLIENT_ID,$id_token,$isFromUpload = false){

        $errorMessage = '';
        $payload = cg_google_sign_in_verify_id_token($CLIENT_ID, $id_token, $errorMessage);

        if ($payload) {
            return $payload;
        }

        cg_google_sign_in_verification_error($errorMessage, $isFromUpload);

    }
}
