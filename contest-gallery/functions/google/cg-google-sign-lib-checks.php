<?php

if(!function_exists('cg_google_sign_in_lib_get_data')){
    function cg_google_sign_in_lib_get_data(){

	    $PHPversionClient = phpversion();
	    $libVersionClient = 'built-in verifier';

		return [
			'cgPHPversionClient' => $PHPversionClient,
			'cgGoogleSignInLibVersionClient' => $libVersionClient,
		];
    }
}

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

if(!function_exists('cg_google_sign_in_lib_checks')){
    function cg_google_sign_in_lib_checks(){

        $status = [];
        $opensslStatus = cg_google_sign_in_get_openssl_status();
        $status['openssl_verify'] = $opensslStatus['openssl_verify'];
        $status['openssl_pkey_get_public'] = $opensslStatus['openssl_pkey_get_public'];
        $status['openssl_status_text'] = cg_google_sign_in_get_openssl_status_text();

        if(!$opensslStatus['openssl_verify'] || !$opensslStatus['openssl_pkey_get_public']){
            $status['error'] = true;
            $status['code'] = 'openssl-missing';
            $status['message'] = $status['openssl_status_text'].' PHP OpenSSL support is required for the built-in Google sign in verifier.';
            return $status;
        }

        $status['success'] = true;
        $status['code'] = 'built-in-verifier';
        $status['message'] = $status['openssl_status_text'].' The built-in server verification can run on this server.';
        return $status;

    }
}
