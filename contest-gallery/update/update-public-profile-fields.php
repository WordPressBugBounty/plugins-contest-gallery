<?php
if(!defined('ABSPATH')){exit;}
require_once __DIR__.'/../functions/general/registry/cg-public-profile-fields.php';

// Retain the old option until every eligible field has been transferred successfully.
function cg_migrate_public_profile_fields($table,$isJustCheck = false){
	if($isJustCheck){return true;}
	global $wpdb;
	$quotedTable = '`'.str_replace('`','``',$table).'`';
	if(!$wpdb->get_var("SHOW COLUMNS FROM $quotedTable LIKE 'PublicProfile'")){return false;}
	$lock = 'cg_public_fields_'.md5($table);
	if(!$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 5)',$lock))){return false;}
	try{
		// Read under the lock, including when another request just completed migration.
		wp_cache_delete('cg_public_profile_fields','options');
		$settings = get_option('cg_public_profile_fields',false);
		if($settings===false){return true;}
		if($wpdb->query('START TRANSACTION')===false){return false;}
		foreach(is_array($settings) ? $settings : array() as $id=>$entry){
			if(!ctype_digit((string)$id) || !$id || !is_array($entry)){continue;}
			$row = $wpdb->get_row($wpdb->prepare("SELECT id,Field_Type FROM $quotedTable WHERE id=%d AND GeneralID=1",$id));
			if($wpdb->last_error){$wpdb->query('ROLLBACK');return false;}
			if(!$row || !in_array($row->Field_Type,cg_public_profile_field_types(),true)){continue;}
			$data = array('PublicProfile'=>!empty($entry['public']) ? 1 : 0);
			if($row->Field_Type==='user-text-field' && isset($entry['display']) && $entry['display']==='link' && cg_registry_url_fields_enabled()){$data['Field_Type'] = 'user-url-field';}
			if($wpdb->update($table,$data,array('id'=>$row->id,'GeneralID'=>1))===false){$wpdb->query('ROLLBACK');return false;}
		}
		if($wpdb->query('COMMIT')===false){$wpdb->query('ROLLBACK');return false;}
		return delete_option('cg_public_profile_fields');
	}finally{
		$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock));
	}
}
