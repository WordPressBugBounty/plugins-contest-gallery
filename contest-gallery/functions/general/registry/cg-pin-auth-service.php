<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_pin_auth_t')){
	function cg_pin_auth_t($default,$replacements = array()){
		static $custom = null;
		if($custom===null){
			$custom = array();
			$wpUploadDir = wp_upload_dir();
			$file = $wpUploadDir['basedir'].'/contest-gallery/gallery-general/json/translations.json';
			if(file_exists($file)){
				$decoded = json_decode(file_get_contents($file),true);
				if(is_array($decoded)){$custom = $decoded;}
			}
		}
		$text = !empty($custom[$default]) ? contest_gal1ery_convert_for_html_output_without_nl2br($custom[$default]) : __($default,'contest-gallery');
		if($text===''){$text = $default;}
		if(is_array($replacements) && count($replacements)){
			$replace = array();
			foreach($replacements as $name=>$value){$replace['{'.$name.'}'] = $value;}
			$text = strtr($text,$replace);
		}
		return $text;
	}
}

if(!function_exists('cg_pin_auth_transient_key')){
	function cg_pin_auth_transient_key($requestKey){
		return 'cg_pin_auth_'.md5((string)$requestKey);
	}
}

if(!function_exists('cg_pin_auth_rate_key')){
	function cg_pin_auth_rate_key($email,$suffix){
		return 'cg_pin_rate_'.md5(strtolower(trim((string)$email)).'|'.$suffix);
	}
}

if(!function_exists('cg_pin_auth_active_key')){
	function cg_pin_auth_active_key($email,$source){
		return 'cg_pin_active_'.md5(strtolower(trim((string)$email)).'|'.sanitize_key($source));
	}
}

if(!function_exists('cg_pin_auth_lock_name')){
	function cg_pin_auth_lock_name($scope,$value){
		return 'cg_pin_'.substr(md5($scope.'|'.$value),0,48);
	}
}

if(!function_exists('cg_pin_auth_acquire_lock')){
	function cg_pin_auth_acquire_lock($scope,$value,$timeout = 5){
		global $wpdb;
		$lockName = cg_pin_auth_lock_name($scope,$value);
		$acquired = $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)',$lockName,absint($timeout)));
		return ((string)$acquired==='1') ? $lockName : false;
	}
}

if(!function_exists('cg_pin_auth_release_lock')){
	function cg_pin_auth_release_lock($lockName){
		global $wpdb;
		if($lockName){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lockName));}
	}
}

if(!function_exists('cg_pin_auth_busy_error')){
	function cg_pin_auth_busy_error(){
		return new WP_Error('cg_pin_busy',cg_pin_auth_t('The PIN request is being processed. Please try again.'));
	}
}

if(!function_exists('cg_pin_auth_get_expiry')){
	function cg_pin_auth_get_expiry(){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_registry_and_login_options';
		$expiry = absint($wpdb->get_var("SELECT PinExpiry FROM $table WHERE GeneralID = 1"));
		return $expiry ? $expiry : 900;
	}
}

if(!function_exists('cg_pin_auth_get_options')){
	function cg_pin_auth_get_options(){
		global $wpdb;
		return $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contest_gal1ery_pro_options WHERE GeneralID = 1");
	}
}

if(!function_exists('cg_pin_auth_cooldown_seconds')){
	function cg_pin_auth_cooldown_seconds(){
		return MINUTE_IN_SECONDS;
	}
}

if(!function_exists('cg_pin_auth_reset_rate_limits')){
	function cg_pin_auth_reset_rate_limits($email){
		$email = sanitize_email(strtolower($email));
		if(!is_email($email)){return false;}
		$rateLock = cg_pin_auth_acquire_lock('rate',$email);
		if(!$rateLock){return false;}
		delete_transient(cg_pin_auth_rate_key($email,'cooldown'));
		delete_transient(cg_pin_auth_rate_key($email,'hour'));
		cg_pin_auth_release_lock($rateLock);
		return true;
	}
}

if(!function_exists('cg_pin_auth_reserve_request')){
	function cg_pin_auth_reserve_request($email){
		$countKey = cg_pin_auth_rate_key($email,'hour');
		$cooldownKey = cg_pin_auth_rate_key($email,'cooldown');
		$cooldownUntil = absint(get_transient($cooldownKey));
		if($cooldownUntil){
			$retryAfter = $cooldownUntil>time() ? $cooldownUntil-time() : cg_pin_auth_cooldown_seconds();
			$secondsLabel = $retryAfter===1 ? cg_pin_auth_t('second') : cg_pin_auth_t('seconds');
			return new WP_Error(
				'cg_pin_rate_limited',
				cg_pin_auth_t('Please wait {seconds} {secondsLabel} before requesting another PIN.',array('seconds'=>$retryAfter,'secondsLabel'=>$secondsLabel)),
				array('reason'=>'cooldown','retryAfter'=>$retryAfter)
			);
		}
		if(absint(get_transient($countKey))>=5){
			return new WP_Error(
				'cg_pin_rate_limited',
				cg_pin_auth_t('Too many PINs were requested. Please try again later.'),
				array('reason'=>'hourly_limit','retryAfter'=>0)
			);
		}
		$cooldownSeconds = cg_pin_auth_cooldown_seconds();
		$cooldownValue = time()+$cooldownSeconds;
		$countValue = absint(get_transient($countKey))+1;
		set_transient($cooldownKey,$cooldownValue,$cooldownSeconds);
		set_transient($countKey,$countValue,HOUR_IN_SECONDS);
		if(absint(get_transient($cooldownKey))!==$cooldownValue || absint(get_transient($countKey))!==$countValue){
			return cg_pin_auth_busy_error();
		}
		return true;
	}
}

if(!function_exists('cg_pin_auth_send_mail')){
	function cg_pin_auth_send_mail($email,$pin){
		$proOptions = cg_pin_auth_get_options();
		if(empty($proOptions)){
			return false;
		}
		$placeholder = '$pin$';
		$body = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->TextPinConfirmation);
		if(empty($body)){
			$body = 'Use the PIN below to continue: <br/><br/> $pin$';
		}
		if(stripos($body,$placeholder)===false){
			$body .= '<br/><br/>'.$placeholder;
		}
		$subject = contest_gal1ery_convert_for_html_output_without_nl2br($proOptions->RegPinSubject);
		if(empty($subject)){
			$subject = 'Your confirmation PIN: $pin$';
		}
		$subject = str_ireplace($placeholder,$pin,$subject);
		return cg1l_send_registration_mail($proOptions,0,1,$email,$subject,$body,'',$placeholder,$pin);
	}
}

if(!function_exists('cg_pin_auth_pending_rows')){
	function cg_pin_auth_pending_rows($activationKey){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
		return $wpdb->get_results($wpdb->prepare(
			"SELECT Field_Type, Field_Content, Tstamp FROM $table WHERE activation_key = %s",
			$activationKey
		));
	}
}

if(!function_exists('cg_pin_auth_pending_data')){
	function cg_pin_auth_pending_data($activationKey){
		$rows = cg_pin_auth_pending_rows($activationKey);
		if(empty($rows)){
			return false;
		}
		$data = array('activationKey'=>$activationKey,'email'=>'','codeHash'=>'','requestKey'=>'','context'=>array());
		foreach($rows as $row){
			if($row->Field_Type==='main-mail'){$data['email'] = sanitize_email($row->Field_Content);}
			if($row->Field_Type==='activation-pin'){$data['codeHash'] = (string)$row->Field_Content;}
			if($row->Field_Type==='cg-pin-request-key'){$data['requestKey'] = sanitize_text_field($row->Field_Content);}
			if($row->Field_Type==='cg-pin-auth-context'){
				$context = json_decode($row->Field_Content,true);
				if(is_array($context)){$data['context'] = $context;}
			}
			if(!empty($row->Tstamp)){$data['created'] = absint($row->Tstamp);}
		}
		if(empty($data['email']) || empty($data['codeHash']) || empty($data['requestKey']) || empty($data['context']['source'])){
			return false;
		}
		return $data;
	}
}

if(!function_exists('cg_pin_auth_source_can_create_user')){
	function cg_pin_auth_source_can_create_user($source){
		return in_array($source,array('paid-actions','users-pin-access'),true);
	}
}

if(!function_exists('cg_pin_auth_find_pending')){
	function cg_pin_auth_find_pending($email,$source){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
		$keys = $wpdb->get_col($wpdb->prepare(
			"SELECT activation_key FROM $table WHERE Field_Type = 'main-mail' AND Field_Content = %s ORDER BY id DESC",
			$email
		));
		foreach($keys as $key){
			$data = cg_pin_auth_pending_data($key);
			if($data && $data['context']['source']===$source){
				return $data;
			}
		}
		return false;
	}
}

if(!function_exists('cg_pin_auth_find_paid_pending')){
	function cg_pin_auth_find_paid_pending($email){
		return cg_pin_auth_find_pending($email,'paid-actions');
	}
}

if(!function_exists('cg_pin_auth_store_pending')){
	function cg_pin_auth_store_pending($email,$requestKey,$pinHash,$context,$activationKey = ''){
		global $wpdb;
		$table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
		$time = time();
		$version = function_exists('cg_get_version_for_scripts') ? cg_get_version_for_scripts() : '';
		if(empty($activationKey)){
			$activationKey = md5($time.wp_generate_password(32,true,true));
		}
		$wpdb->delete($table,array('activation_key'=>$activationKey),array('%s'));
		$rows = array(
			array('type'=>'main-mail','content'=>$email),
			array('type'=>'activation-pin','content'=>$pinHash),
			array('type'=>'cg-pin-request-key','content'=>$requestKey),
			array('type'=>'cg-pin-auth-context','content'=>wp_json_encode($context))
		);
		foreach($rows as $row){
			$inserted = $wpdb->insert($table,array(
				'GalleryID'=>0,'wp_user_id'=>0,'f_input_id'=>0,'Field_Type'=>$row['type'],
				'Field_Content'=>$row['content'],'activation_key'=>$activationKey,'Checked'=>0,
				'Version'=>$version,'GeneralID'=>1,'Tstamp'=>$time
			),array('%d','%d','%d','%s','%s','%s','%d','%s','%d','%d'));
			if($inserted===false){
				$wpdb->delete($table,array('activation_key'=>$activationKey),array('%s'));
				return new WP_Error('cg_pin_pending_store_failed',cg_pin_auth_t('The account confirmation could not be prepared.'));
			}
		}
		return $activationKey;
	}
}

if(!function_exists('cg_pin_auth_store_paid_pending')){
	function cg_pin_auth_store_paid_pending($email,$requestKey,$pinHash,$context,$activationKey = ''){
		return cg_pin_auth_store_pending($email,$requestKey,$pinHash,$context,$activationKey);
	}
}

if(!function_exists('cg_pin_auth_delete_pending')){
	function cg_pin_auth_delete_pending($activationKey){
		global $wpdb;
		if($activationKey){
			$wpdb->delete($wpdb->prefix.'contest_gal1ery_create_user_entries',array('activation_key'=>$activationKey),array('%s'));
		}
	}
}

if(!function_exists('cg_pin_auth_clear_challenge')){
	function cg_pin_auth_clear_challenge($requestKey,$data = array()){
		delete_transient(cg_pin_auth_transient_key($requestKey));
		if(!empty($data['email']) && !empty($data['source'])){
			$activeKey = cg_pin_auth_active_key($data['email'],$data['source']);
			if((string)get_transient($activeKey)===(string)$requestKey){delete_transient($activeKey);}
		}
	}
}

if(!function_exists('cg_pin_auth_build_challenge')){
	function cg_pin_auth_build_challenge($email,$source,$context,$requestKey = '',$activationKey = ''){
		$expiry = cg_pin_auth_get_expiry();
		$pin = (string)wp_rand(100000,999999);
		if(empty($requestKey)){$requestKey = wp_generate_password(48,false,false);}
		$userId = email_exists($email);
		$context = is_array($context) ? $context : array();
		$context['source'] = $source;
		$data = array(
			'email'=>strtolower($email),'source'=>$source,'userId'=>absint($userId),
			'codeHash'=>password_hash($pin,PASSWORD_DEFAULT),'attempts'=>0,
			'expires'=>time()+$expiry,'context'=>$context
		);
		if(!$userId){
			if(!cg_pin_auth_source_can_create_user($source)){
				$data['isDecoy'] = 1;
			}else{
				$data['activationKey'] = $activationKey ? $activationKey : md5(time().wp_generate_password(32,true,true));
			}
		}
		if(!$userId && empty($data['isDecoy'])){
			$stored = cg_pin_auth_store_pending($email,$requestKey,$data['codeHash'],$data['context'],$data['activationKey']);
			if(is_wp_error($stored)){return $stored;}
			$data['activationKey'] = $stored;
		}
		$activeKey = cg_pin_auth_active_key($email,$source);
		$previousRequestKey = get_transient($activeKey);
		if($previousRequestKey && (string)$previousRequestKey!==(string)$requestKey){
			delete_transient(cg_pin_auth_transient_key($previousRequestKey));
		}
		$challengeStored = set_transient(cg_pin_auth_transient_key($requestKey),$data,$expiry);
		set_transient($activeKey,$requestKey,$expiry);
		if($challengeStored===false || (string)get_transient($activeKey)!==(string)$requestKey){
			cg_pin_auth_clear_challenge($requestKey,$data);
			if(!empty($data['activationKey'])){cg_pin_auth_delete_pending($data['activationKey']);}
			return cg_pin_auth_busy_error();
		}
		if(empty($data['isDecoy']) && !cg_pin_auth_send_mail($email,$pin)){
			cg_pin_auth_clear_challenge($requestKey,$data);
			if(!empty($data['activationKey'])){cg_pin_auth_delete_pending($data['activationKey']);}
			return new WP_Error('cg_pin_send_failed',cg_pin_auth_t('The PIN could not be sent.'));
		}
		return array(
			'requestKey'=>$requestKey,'expiresIn'=>$expiry,
			'retryAfter'=>cg_pin_auth_cooldown_seconds()
		);
	}
}

if(!function_exists('cg_pin_auth_request')){
	function cg_pin_auth_request($email,$source,$context = array()){
		$email = sanitize_email(strtolower($email));
		if(!is_email($email)){
			return new WP_Error('cg_pin_invalid_email',cg_pin_auth_t('Enter a valid email address.'));
		}
		$rateLock = cg_pin_auth_acquire_lock('rate',$email);
		if(!$rateLock){return cg_pin_auth_busy_error();}
		$reserved = cg_pin_auth_reserve_request($email);
		if(is_wp_error($reserved)){
			cg_pin_auth_release_lock($rateLock);
			return $reserved;
		}
		$pending = false;
		if(cg_pin_auth_source_can_create_user($source) && !email_exists($email)){
			$pending = cg_pin_auth_find_pending($email,$source);
		}
		$result = cg_pin_auth_build_challenge(
			$email,$source,$context,
			$pending ? $pending['requestKey'] : '',
			$pending ? $pending['activationKey'] : ''
		);
		cg_pin_auth_release_lock($rateLock);
		return $result;
	}
}

if(!function_exists('cg_pin_auth_get_challenge')){
	function cg_pin_auth_get_challenge($requestKey){
		$requestKey = sanitize_text_field($requestKey);
		if(empty($requestKey)){return false;}
		$data = get_transient(cg_pin_auth_transient_key($requestKey));
		return is_array($data) ? $data : false;
	}
}

if(!function_exists('cg_pin_auth_create_user')){
	function cg_pin_auth_create_user($email){
		$existing = email_exists($email);
		if($existing){return absint($existing);}
		$parts = explode('@',$email);
		$displayName = sanitize_text_field($parts[0]);
		$login = cg1l_generate_unique_login_from_email($email);
		$userId = wp_insert_user(array(
			'user_login'=>$login,'user_email'=>$email,'user_pass'=>wp_generate_password(32,true,true),
			'display_name'=>$displayName,'nickname'=>$displayName
		));
		if(is_wp_error($userId)){return $userId;}
		global $wpdb;
		$optionsTable = $wpdb->prefix.'contest_gal1ery_registry_and_login_options';
		$role = $wpdb->get_var("SELECT RegistryUserRole FROM $optionsTable WHERE GeneralID = 1");
		$role = cg_get_safe_registry_user_role($role,14);
		cg_wp_update_user_with_safe_registry_role(array('ID'=>$userId,'nickname'=>$displayName),$role,14);
		return absint($userId);
	}
}

if(!function_exists('cg_pin_auth_verify')){
	function cg_pin_auth_verify($requestKey,$pin,$allowedSource = ''){
		$requestKey = sanitize_text_field($requestKey);
		$pin = preg_replace('/[^0-9]/','',(string)$pin);
		$challengeLock = cg_pin_auth_acquire_lock('challenge',$requestKey);
		if(!$challengeLock){return cg_pin_auth_busy_error();}
		$data = cg_pin_auth_get_challenge($requestKey);
		if(!$data){cg_pin_auth_release_lock($challengeLock);return false;}
		$activeRequestKey = get_transient(cg_pin_auth_active_key($data['email'],$data['source']));
		if((string)$activeRequestKey!==(string)$requestKey){
			cg_pin_auth_clear_challenge($requestKey,$data);
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_invalid',cg_pin_auth_t('The PIN is invalid or expired.'));
		}
		if($allowedSource && (empty($data['source']) || $data['source']!==$allowedSource)){
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_invalid',cg_pin_auth_t('The PIN is invalid or expired.'));
		}
		if(strlen($pin)!==6 || empty($data['expires']) || absint($data['expires'])<time()){
			cg_pin_auth_clear_challenge($requestKey,$data);
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_expired',cg_pin_auth_t('The PIN is invalid or expired.'));
		}
		if(absint($data['attempts'])>=5){
			cg_pin_auth_clear_challenge($requestKey,$data);
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_attempts',cg_pin_auth_t('Too many attempts. Request a new PIN.'));
		}
		if(empty($data['codeHash']) || !password_verify($pin,$data['codeHash'])){
			$data['attempts'] = absint($data['attempts'])+1;
			if($data['attempts']>=5){
				cg_pin_auth_clear_challenge($requestKey,$data);
				cg_pin_auth_release_lock($challengeLock);
				return new WP_Error('cg_pin_attempts',cg_pin_auth_t('Too many attempts. Request a new PIN.'));
			}
			set_transient(cg_pin_auth_transient_key($requestKey),$data,max(1,absint($data['expires'])-time()));
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_wrong',cg_pin_auth_t('The PIN is incorrect.'));
		}
		if(!empty($data['isDecoy'])){
			cg_pin_auth_clear_challenge($requestKey,$data);
			cg_pin_auth_release_lock($challengeLock);
			return new WP_Error('cg_pin_invalid',cg_pin_auth_t('The PIN is invalid or expired.'));
		}
		if(!empty($data['activationKey'])){
			$pending = cg_pin_auth_pending_data($data['activationKey']);
			if(!$pending || $pending['requestKey']!==$requestKey || $pending['context']['source']!==$data['source'] || !password_verify($pin,$pending['codeHash'])){
				cg_pin_auth_clear_challenge($requestKey,$data);
				cg_pin_auth_release_lock($challengeLock);
				return new WP_Error('cg_pin_invalid',cg_pin_auth_t('The PIN is invalid or expired.'));
			}
			cg_pin_auth_clear_challenge($requestKey,$data);
			$userId = cg_pin_auth_create_user($data['email']);
			if(is_wp_error($userId)){cg_pin_auth_release_lock($challengeLock);return $userId;}
			cg1l_delete_unconfirmed_user($data['email']);
		}else{
			$userId = !empty($data['userId']) ? absint($data['userId']) : 0;
			$user = $userId ? get_user_by('id',$userId) : false;
			if(!$user || strtolower($user->user_email)!==strtolower($data['email'])){
				cg_pin_auth_clear_challenge($requestKey,$data);
				cg_pin_auth_release_lock($challengeLock);
				return new WP_Error('cg_pin_user_not_found',cg_pin_auth_t('The user account could not be found.'));
			}
			cg_pin_auth_clear_challenge($requestKey,$data);
		}
		cg_pin_auth_release_lock($challengeLock);
		cg_pin_auth_reset_rate_limits($data['email']);
		return array('userId'=>absint($userId),'email'=>$data['email'],'source'=>$data['source'],'context'=>$data['context']);
	}
}

if(!function_exists('cg_pin_auth_resend')){
	function cg_pin_auth_resend($requestKey){
		$requestKey = sanitize_text_field($requestKey);
		$challengeLock = cg_pin_auth_acquire_lock('challenge',$requestKey);
		if(!$challengeLock){return cg_pin_auth_busy_error();}
		$data = cg_pin_auth_get_challenge($requestKey);
		if(!$data){
			global $wpdb;
			$table = $wpdb->prefix.'contest_gal1ery_create_user_entries';
			$activationKey = $wpdb->get_var($wpdb->prepare(
				"SELECT activation_key FROM $table WHERE Field_Type = 'cg-pin-request-key' AND Field_Content = %s ORDER BY id DESC LIMIT 1",
				$requestKey
			));
			$pending = $activationKey ? cg_pin_auth_pending_data($activationKey) : false;
			if(!$pending){cg_pin_auth_release_lock($challengeLock);return new WP_Error('cg_pin_invalid',cg_pin_auth_t('The PIN request is no longer available.'));}
			$data = array(
				'email'=>$pending['email'],'source'=>$pending['context']['source'],'userId'=>0,
				'context'=>$pending['context'],'activationKey'=>$pending['activationKey']
			);
		}
		cg_pin_auth_release_lock($challengeLock);
		$rateLock = cg_pin_auth_acquire_lock('rate',$data['email']);
		if(!$rateLock){return cg_pin_auth_busy_error();}
		$reserved = cg_pin_auth_reserve_request($data['email']);
		if(is_wp_error($reserved)){
			cg_pin_auth_release_lock($rateLock);
			return $reserved;
		}
		$result = cg_pin_auth_build_challenge(
			$data['email'],$data['source'],isset($data['context']) ? $data['context'] : array(),
			$requestKey,!empty($data['activationKey']) ? $data['activationKey'] : ''
		);
		cg_pin_auth_release_lock($rateLock);
		return $result;
	}
}

if(!function_exists('cg_pin_auth_resend_paid_pending_by_email')){
	function cg_pin_auth_resend_paid_pending_by_email($email){
		$pending = cg_pin_auth_find_paid_pending(sanitize_email(strtolower($email)));
		if(!$pending){return false;}
		return cg_pin_auth_resend($pending['requestKey']);
	}
}

if(!function_exists('cg_pin_auth_login_result_script')){
	function cg_pin_auth_login_result_script($userId){
		$key = wp_hash_password(wp_generate_password(32,true,true));
		update_user_meta($userId,'cgGetLoggedInFrontendUserKey',$key);
		echo '<script data-cg-processing="true" data-cg-success="true">';
		echo 'cgJsClass.gallery.vars.pinMessage="success";';
		echo 'cgJsClass.gallery.vars.pinVerified=true;';
		echo 'cgJsClass.gallery.vars.cgGetLoggedInFrontendUserKey='.wp_json_encode($key).';';
		echo 'cgJsClass.gallery.vars.cgJustLoggedInWpUserId='.wp_json_encode(absint($userId)).';';
		echo '</script>';
	}
}

if(!function_exists('cg_pin_auth_render_paid_actions_auth')){
	function cg_pin_auth_render_paid_actions_auth($isSandbox = false,$hasGoogleSignIn = false){
		$kicker = $isSandbox ? cg_paid_actions_t('SandboxCheckoutUpper') : cg_paid_actions_t('SecureCheckout');
		$title = $isSandbox ? cg_paid_actions_t('SignInSandboxCheckout') : cg_paid_actions_t('SignInBuyCredits');
		$description = $isSandbox ?
			cg_paid_actions_t('SignInSandboxDescription') :
			($hasGoogleSignIn ? cg_paid_actions_t('SignInBuyDescription') : cg_paid_actions_t('SignInBuyDescriptionEmailOnly'));
		ob_start();
		?>
		<div class="cg-paid-actions-auth">
			<div class="cg-paid-actions-modal-kicker"><?php echo esc_html($kicker); ?></div>
			<h2><?php echo esc_html($title); ?></h2>
			<p><?php echo esc_html($description); ?></p>
			<?php if($hasGoogleSignIn){ ?>
				<div class="cg-paid-actions-google"></div>
				<div class="cg-paid-actions-google-unavailable cg_hide" role="status"><?php echo esc_html(cg_paid_actions_t('GoogleSignInUnavailable')); ?></div>
				<div class="cg-paid-actions-or"><span><?php echo esc_html(cg_paid_actions_t('Or')); ?></span></div>
			<?php } ?>
			<form class="cg-paid-actions-code-form">
				<label><span><?php echo esc_html(cg_paid_actions_t('EmailAddress')); ?></span><input type="email" class="cg-paid-actions-email" autocomplete="email" required></label>
				<button type="submit" class="cg-paid-actions-primary"><?php echo esc_html(cg_paid_actions_t('EmailMePin')); ?></button>
			</form>
			<form class="cg-paid-actions-verify-form cg_hide">
				<label><span><?php echo esc_html(cg_paid_actions_t('SixDigitPin')); ?></span></label>
				<div class="cg-paid-actions-pin-fields" role="group" aria-label="<?php echo esc_attr(cg_paid_actions_t('SixDigitPin')); ?>">
					<input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>1))); ?>">
					<input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>2))); ?>">
					<input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>3))); ?>">
					<input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>4))); ?>">
					<input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>5))); ?>">
					<input type="text" inputmode="numeric" maxlength="1" aria-label="<?php echo esc_attr(cg_paid_actions_t('PinDigit',array('number'=>6))); ?>">
				</div>
				<button type="submit" class="cg-paid-actions-primary"><?php echo esc_html(cg_paid_actions_t('ConfirmPin')); ?></button>
				<button type="button" class="cg-paid-actions-send-new-pin"><?php echo esc_html(cg_paid_actions_t('SendNewPin')); ?></button>
			</form>
			<div class="cg-paid-actions-auth-message" aria-live="polite"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}

if(!function_exists('cg_pin_auth_render_paid_actions_confirmation')){
	function cg_pin_auth_render_paid_actions_confirmation(){
		$proOptions = cg_pin_auth_get_options();
		$confirmationText = '';
		if(!empty($proOptions) && !empty($proOptions->TextAfterPinConfirmation)){
			$confirmationText = html_entity_decode(stripslashes(nl2br($proOptions->TextAfterPinConfirmation)));
		}
		ob_start();
		?>
		<div class="cg-paid-actions-auth cg-paid-actions-pin-confirmation" role="dialog" aria-modal="true" aria-labelledby="cgPaidActionsPinConfirmationTitle">
			<span class="cg-paid-actions-pin-confirmation-icon" aria-hidden="true">&#10003;</span>
			<div class="cg-paid-actions-modal-kicker"><?php echo esc_html(cg_paid_actions_t('PinConfirmedUpper')); ?></div>
			<h2 id="cgPaidActionsPinConfirmationTitle"><?php echo esc_html(cg_paid_actions_t('SignInSuccessful')); ?></h2>
			<?php if($confirmationText!==''){ ?>
				<div class="cg-paid-actions-pin-confirmation-content"><?php echo $confirmationText; ?></div>
			<?php } ?>
			<button type="button" class="cg-paid-actions-primary cg-paid-actions-pin-confirmation-continue"><?php echo esc_html(cg_paid_actions_t('ContinueCreditCheckout')); ?></button>
		</div>
		<?php
		return ob_get_clean();
	}
}
