<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_member_replacement_page_snapshot')){
function cg_member_replacement_page_snapshot($row){
    global $wpdb;
    $pages=array();
    foreach(cg_entry_page_fields() as $field){
        if(empty($row->$field)){continue;}
        $post=$wpdb->get_row($wpdb->prepare("SELECT ID,post_title,post_name,guid,post_modified,post_modified_gmt FROM {$wpdb->posts} WHERE ID=%d",$row->$field),ARRAY_A);
        if($post){$pages[$post['ID']]=$post;}
    }
    return $pages;
}
}

// Keep the original media association usable even when SQL ROLLBACK is a no-op.
// Conditional writes must not replace a newer association from another request.
if(!function_exists('cg_member_replacement_restore')){
function cg_member_replacement_restore($recovery,$table,$pid){
    global $wpdb;
    $restored=true;
    if(!empty($recovery['updated'])){
        $before=array_intersect_key((array)$recovery['row'],$recovery['changes']);
        $current=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$table.' WHERE id=%d',$pid),ARRAY_A);
        if(!$current){return false;}
        foreach($before as $key=>$value){if((string)$current[$key]!== (string)$value){$restored=false;break;}}
        if(!$restored){
            $where=array_merge(array('id'=>$pid,'WpUserId'=>$recovery['row']->WpUserId),$recovery['changes']);
            $restored=$wpdb->update($table,$before,$where)===1;
        }
    }
    if(!$restored){error_log('Contest Gallery: could not restore a failed file replacement. Its media were retained.');return false;}
    foreach($recovery['pages'] as $id=>$before){
        if(empty($recovery['pagesAfter'][$id])){continue;}
        $after=$recovery['pagesAfter'][$id];unset($before['ID']);
        if($wpdb->update($wpdb->posts,$before,$after)===false){error_log('Contest Gallery: could not restore an entry URL after a failed replacement.');}
        clean_post_cache($id);
    }
    return true;
}
}

// Keep this finally in a separate function. PHP 5.6 can corrupt a pending
// return value when another try/finally runs inside its finally block.
if(!function_exists('cg_member_replacement_cleanup')){
function cg_member_replacement_cleanup($attachment,$preview,$cachePath,$cacheBackup,$restore,$statsLock){
	global $wpdb;
	try{
		if($restore && $cachePath && $cacheBackup!==null && $cacheBackup!==false){cg1l_write_atomic_file_payload($cachePath,$cacheBackup);}
		if($restore && !cg_upload_job_phase('finalize')){
			if(!cg_upload_job_attachment_referenced($attachment,false)){
				$wpdb->delete($wpdb->prefix.'contest_gal1ery_pdf_previews',array('WpUpload'=>$attachment));
				if($preview && !is_wp_error($preview) && !cg_upload_job_attachment_referenced($preview,false)){wp_delete_attachment($preview,true);}
				wp_delete_attachment($attachment,true);
			}
		}
	}finally{cg1l_release_stats_lock($statsLock);}
}
}
