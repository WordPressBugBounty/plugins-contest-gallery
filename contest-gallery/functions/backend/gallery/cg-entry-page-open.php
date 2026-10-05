<?php
if(!defined('ABSPATH')){exit;}

function cg_backend_entry_page_open_url($entryId,$shortcode,$test = false){
	return add_query_arg(array('action'=>'cg_open_current_entry','cgRealId'=>absint($entryId),'shortcode'=>$shortcode,'test'=>$test ? 1 : 0,'cg_nonce'=>wp_create_nonce('cg_nonce')),admin_url('admin-post.php'));
}

add_action('admin_post_cg_open_current_entry','cg_backend_entry_page_open');
add_action('admin_post_nopriv_cg_open_current_entry','cg_backend_entry_page_open');
function cg_backend_entry_page_open(){
	nocache_headers();
	header('Content-Type: text/html; charset='.get_bloginfo('charset'));
	header('X-Robots-Tag: noindex, nofollow');
	$entryId = isset($_GET['cgRealId']) ? absint($_GET['cgRealId']) : 0;
	$shortcode = isset($_GET['shortcode']) && is_string($_GET['shortcode']) ? sanitize_key($_GET['shortcode']) : '';
	$nonce = isset($_GET['cg_nonce']) && is_string($_GET['cg_nonce']) ? sanitize_text_field(wp_unslash($_GET['cg_nonce'])) : '';
	$fields = cg_entry_page_fields();
	$error = '';
	if(!is_user_logged_in() || !cg_user_has_backend_access()){
		$error = 'Please sign in with an account that can access the gallery backend and reopen this link.';
	}elseif(!wp_verify_nonce($nonce,'cg_nonce')){
		$error = 'This link has expired. Reload the gallery in the backend and open the entry again.';
	}elseif(!$entryId || !isset($fields[$shortcode])){
		$error = 'This entry link is not available. Reload the gallery and try again.';
	}
	if($error){status_header(403);}
	$config = array('ajaxUrl'=>admin_url('admin-ajax.php'),'siteUrl'=>home_url('/'),'entryId'=>$entryId,'shortcode'=>$shortcode,'test'=>isset($_GET['test']) && $_GET['test']==='1','nonce'=>$error ? '' : wp_create_nonce('cg_nonce'),'error'=>$error);
	include_once __DIR__.'/../render/cg-entry-page-open.php';
	cg_backend_render_entry_page_open($config);
	exit;
}
