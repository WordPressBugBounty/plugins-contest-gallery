<?php
if(!defined('ABSPATH')){exit;}

// Site settings and historical registration receipts are deliberately separate.
if(!function_exists('cg_registration_agreements_settings')){
function cg_registration_agreements_settings($forOptions = false){
	global $wpdb;
	$raw = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",'cg_registration_agreements'));
	$stored = is_string($raw) ? maybe_unserialize($raw) : array();
	$agreements = array();
	foreach(array(1,2,3) as $slot){
		$row = isset($stored[$slot]) && is_array($stored[$slot]) ? $stored[$slot] : array();
		$html = isset($row['html']) && is_string($row['html']) ? trim(wp_kses_post($row['html'])) : '';
		$text = html_entity_decode(wp_strip_all_tags($html),ENT_QUOTES|ENT_HTML5,'UTF-8');
		$text = trim(str_replace("\xc2\xa0",'',$text));
		if($text===''){$html = '';}
		// Only unconfigured empty slots get the editor default. Preserve saved choices.
		$required = array_key_exists('required',$row) ? !empty($row['required']) : $html==='';
		$agreements[$slot] = array('html'=>$html,'required'=>($forOptions || $html!=='') && $required);
	}
	return $agreements;
}
}

if(!function_exists('cg_registration_agreements_active')){
function cg_registration_agreements_active($settings = null){
	if($settings===null){$settings = cg_registration_agreements_settings();}
	return array_filter($settings,'cg_registration_agreement_has_text');
}
}
if(!function_exists('cg_registration_agreement_has_text')){
function cg_registration_agreement_has_text($row){return !empty($row['html']);}
}
if(!function_exists('cg_registration_agreements_revision')){
function cg_registration_agreements_revision($settings){return hash('sha256',wp_json_encode($settings));}
}
if(!function_exists('cg_registration_agreements_meta_key')){
function cg_registration_agreements_meta_key(){global $wpdb;return $wpdb->get_blog_prefix().'cg_registration_agreements';}
}

if(!function_exists('cg_registration_agreements_save')){
function cg_registration_agreements_save(){
	if(!current_user_can('manage_options') || empty($_POST['cg_registration_agreements_present'])){return;}
	$settings = array();
	foreach(array(1=>'One',2=>'Two',3=>'Three') as $slot=>$name){
		$field = 'RegistrationAgreement'.$name;
		$html = isset($_POST[$field]) && is_string($_POST[$field]) ? trim(wp_kses_post(wp_unslash($_POST[$field]))) : '';
		$settings[$slot] = array('html'=>$html,'required'=>!empty($_POST[$field.'Required']));
	}
	update_option('cg_registration_agreements',$settings,false);
}
}

if(!function_exists('cg_registration_agreements_render_options')){
function cg_registration_agreements_render_options(){
	$settings = cg_registration_agreements_settings(true);
	$disabled = current_user_can('manage_options') ? '' : ' disabled';
	echo '<div class="cg_view_options_rows_container cg_go_to_target" data-cg-go-to-target="RegistrationAgreementsOptions"><p class="cg_view_options_rows_container_title">Registration agreements</p>';
	echo '<ul class="cg_registration_help"><li><strong>Agreements:</strong> Up to 3, required or optional.</li><li><strong>Applies to:</strong> New accounts via quick registration or Google sign-in.</li><li><strong>Newsletter:</strong> Use optional consent. Manage subscribers in your newsletter service.</li></ul>';
	if(!$disabled){echo '<input type="hidden" name="cg_registration_agreements_present" value="1">';}
	foreach(array(1=>'One',2=>'Two',3=>'Three') as $slot=>$name){
		$field = 'RegistrationAgreement'.$name;
		$title = array(1=>'First',2=>'Second',3=>'Third');
		echo '<div class="cg-registration-agreement-options-island cg_margin_bottom_30">';
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_full_width cg_border_border_top_left_radius_8_px cg_border_border_top_right_radius_8_px" id="wp-'.esc_attr($field).'-wrap-Container"><div class="cg_view_option_title"><p>'.esc_html($title[$slot]).' registration agreement<br><span class="cg_view_option_title_note">Shown before a new account is created. Leave empty to hide this agreement.</span></p></div><div class="cg_view_option_html"><textarea class="cg-wp-editor-template" id="'.esc_attr($field).'" name="'.esc_attr($field).'"'.$disabled.'>'.esc_textarea($settings[$slot]['html']).'</textarea></div></div></div>';
		echo '<div class="cg_view_options_row"><div class="cg_view_option cg_view_option_100_percent cg_border_top_none cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px"><div class="cg_view_option_title"><p>Required to create an account<br><span class="cg_view_option_title_note">Users must accept this agreement to create an account. Leave unchecked for optional consent.</span></p></div><div class="cg_view_option_checkbox"><input type="checkbox" name="'.esc_attr($field).'Required" value="1"'.(!empty($settings[$slot]['required']) ? ' checked' : '').$disabled.'></div></div></div>';
		echo '</div>';
	}
	echo '</div>';
}
}

if(!function_exists('cg_registration_agreements_render_panel')){
function cg_registration_agreements_render_panel($key,$state){
	ob_start();
	echo '<form class="cg-registration-agreements-form" novalidate data-cg-registration-key="'.esc_attr($key).'" data-cg-revision="'.esc_attr($state['revision']).'">';
	echo '<h2>'.esc_html(cg_pin_auth_t('Create your account')).'</h2><p>'.esc_html(cg_pin_auth_t('Review the following agreements before creating your account.')).'</p>';
	foreach(cg_registration_agreements_active($state['agreements']) as $slot=>$row){
		echo '<div class="cg-registration-agreement"><label><input type="checkbox" name="agreements['.absint($slot).']" value="1" data-cg-agreement-slot="'.absint($slot).'"'.(!empty($row['required']) ? ' required' : '').'><span class="cg-registration-agreement-copy">'.$row['html'];
		if(empty($row['required'])){echo '<span class="cg-registration-agreement-optional">'.esc_html(cg_pin_auth_t('optional')).'</span>';}
		echo '</span></label><div class="cg-registration-agreement-error cg_hide" role="alert">'.esc_html(cg_pin_auth_t('You have to check this agreement')).'</div></div>';
	}
	echo '<div class="cg-registration-agreements-message" role="status" aria-live="polite"></div><button type="submit" class="cg-paid-actions-primary">'.esc_html(cg_pin_auth_t('Create account')).'</button><button type="button" class="cg-registration-agreements-cancel">'.esc_html(cg_pin_auth_t('Cancel')).'</button></form>';
	return ob_get_clean();
}
}

if(!function_exists('cg_registration_agreements_response')){
function cg_registration_agreements_response($key,$state){
	return array('nextStep'=>'agreements','registrationKey'=>$key,'revision'=>$state['revision'],'agreementsHtml'=>cg_registration_agreements_render_panel($key,$state));
}
}

if(!function_exists('cg_registration_agreements_begin')){
function cg_registration_agreements_begin($identity,$source,$context){
	$agreements = cg_registration_agreements_settings();
	if(!cg_registration_agreements_active($agreements)){return false;}
	$binding = cg1l_get_registry_activation_browser_binding(true);
	if(!$binding){return new WP_Error('cg_registration_browser',cg_pin_auth_t('Please allow cookies to sign in.'));}
	// Repeated verified Google callbacks must not create an unbounded set of proofs.
	$index = hash('sha256',wp_json_encode(array($source,$identity['email'],isset($identity['sub']) ? $identity['sub'] : '',$binding,$context)));
	$previousKey = cg_security_state_read('registration-index',$index);
	$previous = $previousKey ? cg_security_state_read('registration',$previousKey) : false;
	if($previous && isset($previous['indexSubject']) && $previous['indexSubject']===$index){
		$previous['agreements'] = $agreements;
		$previous['revision'] = cg_registration_agreements_revision($agreements);
		if(!cg_security_state_write('registration',$previousKey,$previous,max(1,$previous['verifiedAt']+900-time()))){return cg_pin_auth_busy_error();}
		return cg_registration_agreements_response($previousKey,$previous);
	}
	$key = wp_generate_password(48,false,false);
	$state = array('identity'=>$identity,'source'=>$source,'context'=>$context,'browserBinding'=>$binding,'siteId'=>get_current_blog_id(),'indexSubject'=>$index,
		'agreements'=>$agreements,'revision'=>cg_registration_agreements_revision($agreements),'verifiedAt'=>time());
	if(!cg_security_state_write('registration',$key,$state,900)){return cg_pin_auth_busy_error();}
	if(!cg_security_state_write('registration-index',$index,$key,900)){cg_security_state_delete('registration',$key);return cg_pin_auth_busy_error();}
	return cg_registration_agreements_response($key,$state);
}
}

if(!function_exists('cg_registration_agreements_consume')){
function cg_registration_agreements_consume($key,$state){
	if(!cg_security_state_delete('registration',$key)){return false;}
	if(!empty($state['indexSubject']) && cg_security_state_read('registration-index',$state['indexSubject'])===$key){cg_security_state_delete('registration-index',$state['indexSubject']);}
	return true;
}
}

if(!function_exists('cg_registration_agreements_google_begin')){
function cg_registration_agreements_google_begin($payload){
	if(!cg_registration_agreements_active()){return false;}
	$email = strtolower($payload['email']);
	$lock = cg_pin_auth_acquire_lock('rate',$email);
	if(!$lock){return cg_pin_auth_busy_error();}
	try{
		global $wpdb;
		$linked = $wpdb->get_var($wpdb->prepare("SELECT u.ID FROM {$wpdb->prefix}contest_gal1ery_google_users g INNER JOIN {$wpdb->users} u ON u.ID=g.WpUserId WHERE g.GoogleId=%s",$payload['sub']));
		if($linked || email_exists(str_replace('@googlemail.com','@gmail.com',$email)) || email_exists(str_replace('@gmail.com','@googlemail.com',$email))){return false;}
		$identity = array('email'=>$email);
		foreach(array('sub','name','given_name','family_name','picture','hd','email_verified') as $field){if(isset($payload[$field])){$identity[$field]=$payload[$field];}}
		$context = array();
		$token = isset($_POST['cg_registration_context_token']) && is_string($_POST['cg_registration_context_token']) ? $_POST['cg_registration_context_token'] : '';
		$runtime = $token ? cg1l_verify_runtime_context_token($token) : false;
		if($runtime){$context['galleryId']=absint($runtime['realGid']);}
		$forward = $wpdb->get_row("SELECT ForwardAfterLoginUrl, ForwardAfterLoginUrlCheck FROM {$wpdb->prefix}contest_gal1ery_pro_options WHERE GeneralID=1");
		if($forward && !empty($forward->ForwardAfterLoginUrlCheck)){$context['redirect']=esc_url_raw(html_entity_decode(stripslashes($forward->ForwardAfterLoginUrl)));}
		return cg_registration_agreements_begin($identity,'google',$context);
	}finally{cg_pin_auth_release_lock($lock);}
}
}

if(!function_exists('cg_registration_agreements_context_valid')){
function cg_registration_agreements_context_valid($state){
	$context = $state['context'];
	if($state['source']==='gallery-member'){
		if(empty($context['galleryId']) || empty($context['shortcodeName'])){return false;}
		return $context['shortcodeName']==='cg_users_upload' ? cg_member_upload_auth_enabled($context['galleryId']) : cg_member_enabled($context['galleryId'],$context['shortcodeName']);
	}
	if($state['source']==='paid-actions'){
		return !empty($context['galleryId']) && cg_paid_actions_gallery_is_supported($context['galleryId']);
	}
	return in_array($state['source'],array('google','users-pin-access'),true);
}
}

// Called while holding the same email lock as PIN verification and Google creation.
if(!function_exists('cg_registration_agreements_complete_locked')){
function cg_registration_agreements_complete_locked($key,$state,$decisions,$revision){
	if(!cg_registration_agreements_context_valid($state)){return new WP_Error('cg_registration_context',cg_pin_auth_t('This gallery is not available.'));}
	$settings = cg_registration_agreements_settings();
	$currentRevision = cg_registration_agreements_revision($settings);
	if($state['revision']!==$currentRevision || $revision!==$state['revision']){
		$state['agreements'] = $settings;
		$state['revision'] = $currentRevision;
		if(!cg_security_state_write('registration',$key,$state,max(1,$state['verifiedAt']+900-time()))){return cg_pin_auth_busy_error();}
		return cg_registration_agreements_response($key,$state);
	}
	$accepted = array();
	foreach(cg_registration_agreements_active($state['agreements']) as $slot=>$row){
		$value = isset($decisions[$slot]) && is_scalar($decisions[$slot]) && (string)$decisions[$slot]==='1';
		if($row['required'] && !$value){return new WP_Error('cg_registration_required',cg_pin_auth_t('You have to check this agreement'),array('slot'=>$slot));}
		$accepted[$slot] = array('html'=>$row['html'],'required'=>$row['required'],'accepted'=>$value);
	}
	$email = $state['identity']['email'];
	$existing = email_exists($email);
	if(!$existing && $state['source']==='google'){$existing = email_exists(str_replace('@googlemail.com','@gmail.com',$email));}
	if(!$existing && $state['source']==='google'){$existing = email_exists(str_replace('@gmail.com','@googlemail.com',$email));}
	// A parallel registration never receives a receipt from this uncompleted flow.
	if($existing){
		global $wpdb;
		$accountEmail = $state['source']==='google' ? $wpdb->get_var($wpdb->prepare("SELECT user_email FROM {$wpdb->users} WHERE ID=%d",$existing)) : $email;
		if($state['source']==='google' && (!cg_google_sign_in_is_existing_wp_email_auto_link_allowed($state['identity']) || !cg_google_sign_in_link_wp_user($state['identity'],$existing))){return new WP_Error('cg_registration_link',cg_pin_auth_t('Please sign in with your existing account.'));}
		if(!cg1l_auth_eligible_user($existing,$accountEmail)){return new WP_Error('cg_registration_user',cg_pin_auth_t('Please sign in with your existing account.'));}
		if(!cg_registration_agreements_consume($key,$state)){return cg_pin_auth_busy_error();}
		return array('userId'=>absint($existing),'email'=>$accountEmail,'source'=>$state['source'],'context'=>$state['context']);
	}
	if($state['source']==='google'){
		$createdUserId = 0;
		$created = cg_create_wp_user_from_google_user($state['identity'],false,true,$createdUserId);
		$userId = $created ? $createdUserId : false;
		if(!$created && $createdUserId){cg_registration_agreements_rollback_user($createdUserId);}
	}else{$userId = cg_pin_auth_create_user($email,true);}
	if(!$userId || is_wp_error($userId)){return is_wp_error($userId) ? $userId : new WP_Error('cg_registration_create',cg_pin_auth_t('The user account could not be created.'));}
	$receipt = array('version'=>1,'recordedAt'=>gmdate('Y-m-d\TH:i:s\Z'),'source'=>$state['source'],
		'galleryId'=>!empty($state['context']['galleryId']) ? absint($state['context']['galleryId']) : 0,'revision'=>$state['revision'],'agreements'=>$accepted);
	$metaId = add_user_meta($userId,cg_registration_agreements_meta_key(),wp_slash($receipt),true);
	global $wpdb;
	$stored = $metaId ? $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE umeta_id=%d AND user_id=%d",$metaId,$userId)) : null;
	if(!$metaId || maybe_unserialize($stored)!==$receipt || !cg_registration_agreements_consume($key,$state)){
		cg_registration_agreements_rollback_user($userId);
		return new WP_Error('cg_registration_save',cg_pin_auth_t('The registration could not be saved. Please try again.'));
	}
	cg1l_delete_unconfirmed_user($email);
	return array('userId'=>absint($userId),'email'=>$email,'source'=>$state['source'],'context'=>$state['context']);
}
}

if(!function_exists('cg_registration_agreements_rollback_user')){
function cg_registration_agreements_rollback_user($userId){
	global $wpdb;
	$wpdb->delete($wpdb->prefix.'contest_gal1ery_google_users',array('WpUserId'=>absint($userId)),array('%d'));
	if(is_multisite()){
		require_once ABSPATH.'wp-admin/includes/ms.php';
		wpmu_delete_user($userId);
	}else{
		require_once ABSPATH.'wp-admin/includes/user.php';
		wp_delete_user($userId);
	}
}
}

if(!function_exists('cg_registration_agreements_complete')){
function cg_registration_agreements_complete($key,$decisions,$revision){
	$error = new WP_Error('cg_registration_expired',cg_pin_auth_t('Your registration has expired. Please sign in again.'));
	$state = cg_security_state_read('registration',$key);
	$binding = cg1l_get_registry_activation_browser_binding(false);
	if(!$state || !$binding || empty($state['browserBinding']) || !cg1l_hash_equals($state['browserBinding'],$binding) || (int)$state['siteId']!==get_current_blog_id()){return $error;}
	$lock = cg_pin_auth_acquire_lock('rate',$state['identity']['email']);
	if(!$lock){return cg_pin_auth_busy_error();}
	try{
		$state = cg_security_state_read('registration',$key);
		if(!$state || !cg1l_hash_equals($state['browserBinding'],$binding) || (int)$state['siteId']!==get_current_blog_id()){return $error;}
		return cg_registration_agreements_complete_locked($key,$state,$decisions,$revision);
	}finally{cg_pin_auth_release_lock($lock);}
}
}

if(!function_exists('post_cg_registration_agreements_complete')){
function post_cg_registration_agreements_complete(){
	nocache_headers();
	if(!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD']!=='POST'){wp_send_json_error(array('message'=>cg_pin_auth_t('Please try again.')),405);}
	cg1l_ajax_reject_bad_origin();
	cg_check_frontend_nonce();
	if(is_user_logged_in()){wp_send_json_success(array('reload'=>true,'sessionChanged'=>true));}
	$key = isset($_POST['registrationKey']) && is_string($_POST['registrationKey']) ? sanitize_text_field(wp_unslash($_POST['registrationKey'])) : '';
	$revision = isset($_POST['revision']) && is_string($_POST['revision']) ? sanitize_text_field(wp_unslash($_POST['revision'])) : '';
	$decisions = isset($_POST['agreements']) && is_array($_POST['agreements']) ? $_POST['agreements'] : array();
	$result = cg_registration_agreements_complete($key,$decisions,$revision);
	if(is_wp_error($result)){
		$data = $result->get_error_data();
		wp_send_json_error(array('message'=>$result->get_error_message(),'code'=>$result->get_error_code(),'slot'=>is_array($data) && isset($data['slot']) ? absint($data['slot']) : 0),400);
	}
	if(!empty($result['nextStep'])){wp_send_json_success($result);}
	if($result['source']==='paid-actions'){cg_paid_actions_pin_login_response($result);}
	if(!cg1l_auth_eligible_user($result['userId'],$result['email'])){wp_send_json_error(array('message'=>cg_pin_auth_t('Please sign in again.')),400);}
	wp_set_current_user($result['userId']);
	if($result['source']==='google'){
		if(!cg_google_sign_in_set_auth_cookie($result['userId'])){wp_send_json_error(array('message'=>cg_pin_auth_t('Please sign in again.')),400);}
	}else{wp_set_auth_cookie($result['userId'],true,is_ssl());}
	$response = array('reload'=>true);
	if($result['source']==='google' && !empty($result['context']['redirect'])){$response['redirect']=$result['context']['redirect'];}
	wp_send_json_success($response);
}
}
add_action('wp_ajax_post_cg_registration_agreements_complete','post_cg_registration_agreements_complete');
add_action('wp_ajax_nopriv_post_cg_registration_agreements_complete','post_cg_registration_agreements_complete');

if(!function_exists('cg_registration_agreements_csv_headers')){
function cg_registration_agreements_csv_headers(){
	$headers = array();
	foreach(array(1,2,3) as $slot){foreach(array('accepted','recorded at (UTC)','text (HTML)','required') as $label){$headers[] = 'Registration agreement '.$slot.' '.$label;}}
	return array_merge($headers,array('Registration source','Registration gallery id'));
}
}
if(!function_exists('cg_registration_agreements_csv_values')){
function cg_registration_agreements_csv_values($receipt){
	$values = array();
	foreach(array(1,2,3) as $slot){
		$row = isset($receipt['agreements'][$slot]) && is_array($receipt['agreements'][$slot]) ? $receipt['agreements'][$slot] : null;
		$values[] = $row ? (!empty($row['accepted']) ? '1' : '0') : '';
		$values[] = $row && isset($receipt['recordedAt']) ? $receipt['recordedAt'] : '';
		$values[] = $row && isset($row['html']) ? $row['html'] : '';
		$values[] = $row ? (!empty($row['required']) ? '1' : '0') : '';
	}
	$values[] = isset($receipt['source']) ? $receipt['source'] : '';
	$values[] = !empty($receipt['galleryId']) ? absint($receipt['galleryId']) : '';
	return $values;
}
}
