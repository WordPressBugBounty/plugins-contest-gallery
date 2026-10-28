<?php

if(!defined('ABSPATH')){exit;}

if(!defined('CG_GALLERY_EXPORT_VOTES_BATCH')){define('CG_GALLERY_EXPORT_VOTES_BATCH',250);}
if(!defined('CG_GALLERY_EXPORT_ENTRIES_BATCH')){define('CG_GALLERY_EXPORT_ENTRIES_BATCH',100);}
if(!defined('CG_GALLERY_EXPORT_ZIP_BATCH')){define('CG_GALLERY_EXPORT_ZIP_BATCH',20);}
if(!defined('CG_GALLERY_EXPORT_ZIP_BYTES')){define('CG_GALLERY_EXPORT_ZIP_BYTES',33554432);}

if(!function_exists('cg_gallery_export_check_db')){
    function cg_gallery_export_check_db(){
        global $wpdb;
        if($wpdb->last_error!==''){throw new Exception('The export data could not be read. Please try again.',500);}
    }
}
if(!function_exists('cg_gallery_export_results')){
    function cg_gallery_export_results($sql){
        global $wpdb;
        $rows = $wpdb->get_results($sql);
        cg_gallery_export_check_db();
        return $rows;
    }
}
if(!function_exists('cg_gallery_export_var')){
    function cg_gallery_export_var($sql){
        global $wpdb;
        $value = $wpdb->get_var($sql);
        cg_gallery_export_check_db();
        return $value;
    }
}
if(!function_exists('cg_gallery_export_objects')){
    function cg_gallery_export_objects($rows){
        $objects = array();
        foreach($rows as $row){$objects[] = (object)$row;}
        return $objects;
    }
}
if(!function_exists('cg_gallery_export_csv_row')){
    function cg_gallery_export_csv_row($fp,$row){
        $written=fputcsv($fp,cg_neutralize_csv_array($row),';');
        if($written===false || $written===0){throw new Exception('The CSV file could not be written.',500);}
    }
}
if(!function_exists('cg_gallery_export_allowed')){
    function cg_gallery_export_allowed($type){
        return is_user_logged_in() && ($type==='files_zip' ? cg_user_has_backend_access() : current_user_can('manage_options'));
    }
}
if(!function_exists('cg_gallery_export_atomic')){
    function cg_gallery_export_atomic($file,$value){
        $json = wp_json_encode($value);
        if($json===false){throw new Exception('The export state could not be encoded.',500);}
        $tmp = $file.'.'.wp_generate_password(12,false,false).'.tmp';
        if(file_put_contents($tmp,$json,LOCK_EX)!==strlen($json)){
            @unlink($tmp);
            throw new Exception('The export state could not be written.',500);
        }
        @chmod($tmp,0600);
        if(!rename($tmp,$file)){
            @unlink($tmp);
            throw new Exception('The export state could not be saved.',500);
        }
    }
}
if(!function_exists('cg_gallery_export_base')){
    function cg_gallery_export_base(){
        global $wpdb;
        $preferredTemp = get_temp_dir();
        $temp = '';
        $contentRoot = defined('WP_CONTENT_DIR') ? realpath(WP_CONTENT_DIR) : false;
        $siteRoot = realpath(ABSPATH);
        $webRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        // CSV parts contain personal data. Never rely on .htaccess alone on nginx.
        foreach(array($preferredTemp,sys_get_temp_dir()) as $candidate){
            $real = realpath($candidate);
            if(!$real){continue;}
            $public = false;
            foreach(array($siteRoot,$webRoot,$contentRoot) as $root){
                if($root && ($real===$root || strpos($real,$root.DIRECTORY_SEPARATOR)===0)){$public=true;}
            }
            if(!$public && is_writable($real)){$temp=$real;break;}
            $temp='';
        }
        if($temp===''){throw new Exception('A private temporary export directory is not available.',500);}
        $dir = trailingslashit($temp).'cg-gallery-export-'.md5(ABSPATH.'|'.$wpdb->prefix);
        if(!is_dir($dir) && !wp_mkdir_p($dir)){throw new Exception('A temporary export directory could not be created.',500);}
        @chmod($dir,0700);
        if(!is_writable($dir)){throw new Exception('The temporary export directory is not writable.',500);}
        foreach(array('index.php'=>"<?php // Silence is golden.\n",'.htaccess'=>"Require all denied\nDeny from all\n",'web.config'=>'<configuration><system.webServer><authorization><deny users="*" /></authorization></system.webServer></configuration>') as $name=>$content){
            if(!file_exists($dir.'/'.$name)){
                file_put_contents($dir.'/'.$name,$content,LOCK_EX);
                @chmod($dir.'/'.$name,0600);
            }
        }
        return $dir;
    }
}
if(!function_exists('cg_gallery_export_dir')){
    function cg_gallery_export_dir($id){
        if(!is_string($id) || !preg_match('/^[0-9]{10,}-[a-zA-Z0-9]{24}$/D',$id)){throw new Exception('Invalid export job.',400);}
        return cg_gallery_export_base().'/'.$id;
    }
}
if(!function_exists('cg_gallery_export_read')){
    function cg_gallery_export_read($file){
        $value = is_file($file) && !is_link($file) ? json_decode(file_get_contents($file),true) : null;
        if(!is_array($value)){throw new Exception('The export job is no longer available. Please start again.',404);}
        return $value;
    }
}
if(!function_exists('cg_gallery_export_state')){
    function cg_gallery_export_state($id){
        $state = cg_gallery_export_read(cg_gallery_export_dir($id).'/state.json');
        if((int)$state['owner_id']!==get_current_user_id() || (int)$state['blog_id']!==get_current_blog_id() || !cg_gallery_export_allowed($state['type'])){
            throw new Exception('You do not have permission to access this export.',403);
        }
        return $state;
    }
}
if(!function_exists('cg_gallery_export_save')){
    function cg_gallery_export_save($state){
        $state['updated_at'] = time();
        cg_gallery_export_atomic(cg_gallery_export_dir($state['job_id']).'/state.json',$state);
    }
}
if(!function_exists('cg_gallery_export_lock')){
    function cg_gallery_export_lock($file){
        $fp = fopen($file,'c');
        if(!$fp || !flock($fp,LOCK_EX|LOCK_NB)){
            if(is_resource($fp)){fclose($fp);}
            throw new Exception('This export is processing another request. Please try again.',409);
        }
        @chmod($file,0600);
        return $fp;
    }
}
if(!function_exists('cg_gallery_export_unlock')){
    function cg_gallery_export_unlock($lock){
        if(is_resource($lock)){flock($lock,LOCK_UN);fclose($lock);}
    }
}
if(!function_exists('cg_gallery_export_zip_target')){
    function cg_gallery_export_zip_target($gallery){
        global $wpdb;
        $uploads = wp_upload_dir();
        $adminEmail = get_option('admin_email');
        $pass = cg_gallery_export_var($wpdb->prepare("SELECT user_pass FROM $wpdb->users WHERE user_email = %s",$adminEmail));
        $code = is_string($pass) && $pass!=='' ? md5($wpdb->base_prefix.$pass) : hash_hmac('sha256','cg-gallery-export|'.$wpdb->prefix.'|'.absint($gallery),wp_salt('auth'));
        $name = $code.'_images_download.zip';
        $relative = '/contest-gallery/gallery-id-'.absint($gallery).'/'.$name;
        return array('path'=>$uploads['basedir'].$relative,'url'=>set_url_scheme($uploads['baseurl'].$relative),'name'=>$name);
    }
}
if(!function_exists('cg_gallery_export_unsafe_zip_name')){
    function cg_gallery_export_unsafe_zip_name(){
        global $wpdb;
        return md5($wpdb->base_prefix).'_images_download.zip';
    }
}
if(!function_exists('cg_gallery_export_migrate_unsafe_zip')){
    function cg_gallery_export_migrate_unsafe_zip($gallery){
        $uploads=wp_upload_dir();
        $folder=$uploads['basedir'].'/contest-gallery/gallery-id-'.absint($gallery);
        $legacy=$folder.'/'.cg_gallery_export_unsafe_zip_name();
        if(!file_exists($legacy) && !is_link($legacy)){return;}
        $base=realpath($uploads['basedir']);$real=realpath($folder);
        if(!$base || !$real || strpos($real,$base.DIRECTORY_SEPARATOR)!==0 || is_link(dirname($folder)) || is_link($folder)){
            throw new Exception('An old ZIP download could not be secured. Check the gallery upload directory.',500);
        }
        $target=cg_gallery_export_zip_target($gallery);
        $lock=cg_gallery_export_lock(cg_gallery_export_base().'/gallery-'.absint($gallery).'.lock');
        try{
            clearstatcache(true,$legacy);
            if(file_exists($legacy) || is_link($legacy)){
                if(!is_file($legacy) || is_link($legacy) || is_link($target['path']) || (file_exists($target['path']) && !is_file($target['path']))){
                    throw new Exception('An old ZIP download could not be secured. Check the gallery upload files.',500);
                }
                // A current safe archive takes precedence over an obsolete public copy.
                $moved=is_file($target['path']) ? @unlink($legacy) : @rename($legacy,$target['path']);
                if(!$moved){throw new Exception('An old ZIP download could not be secured. Check the gallery directory permissions.',500);}
            }
        }catch(Exception $e){cg_gallery_export_unlock($lock);throw $e;}
        cg_gallery_export_unlock($lock);
    }
}
if(!function_exists('cg_gallery_export_migrate_zip_names')){
    function cg_gallery_export_migrate_zip_names(){
        if(get_option('cg_gallery_export_zip_names_migrated_v1')){return;}
        try{
            $uploads=wp_upload_dir();$root=$uploads['basedir'].'/contest-gallery';
            if(!empty($uploads['error']) || is_link($root)){throw new Exception('The gallery upload directory is unavailable.',500);}
            if(is_dir($root)){
                foreach(new DirectoryIterator($root) as $item){
                    if(preg_match('/^gallery-id-([1-9][0-9]*)$/D',$item->getFilename(),$match)){
                        cg_gallery_export_migrate_unsafe_zip($match[1]);
                    }
                }
            }
            update_option('cg_gallery_export_zip_names_migrated_v1',1,false);
        }catch(Exception $e){$GLOBALS['cg_gallery_export_zip_migration_error']=true;}
    }
}
add_action('init','cg_gallery_export_migrate_zip_names');
if(!function_exists('cg_gallery_export_zip_migration_notice')){
    function cg_gallery_export_zip_migration_notice(){
        if(!empty($GLOBALS['cg_gallery_export_zip_migration_error']) && current_user_can('manage_options')){
            echo '<div class="notice notice-error"><p>'.esc_html('Contest Gallery could not secure an old ZIP download. Check the gallery upload directory permissions and reload this page.').'</p></div>';
        }
    }
}
add_action('admin_notices','cg_gallery_export_zip_migration_notice');
if(!function_exists('cg_gallery_export_zip_state_target')){
    function cg_gallery_export_zip_state_target($state){
        if(basename($state['target']['path'])===cg_gallery_export_unsafe_zip_name()){
            cg_gallery_export_migrate_unsafe_zip($state['gallery_id']);
            return cg_gallery_export_zip_target($state['gallery_id']);
        }
        return $state['target'];
    }
}
if(!function_exists('cg_gallery_export_public')){
    function cg_gallery_export_public($state){
        $done = $state['status']==='done';
        $total = (int)$state['total'];
        $processed = (int)$state['processed'];
        $data = array('job_id'=>$state['job_id'],'type'=>$state['type'],'status'=>$state['status'],'phase'=>$state['phase'],'processed'=>$processed,'total'=>$total,'skipped'=>(int)$state['skipped'],'done'=>$done);
        $data['percent'] = $done ? 100 : ($total ? min(99,(int)floor(100*$processed/$total)) : 0);
        if($done){
            if($state['type']==='files_zip'){$state['target']=cg_gallery_export_zip_state_target($state);}
            $data['download_url'] = $state['type']==='files_zip' ? $state['target']['url'] : add_query_arg(array('action'=>'cg_gallery_export_download','job_id'=>$state['job_id'],'download_nonce'=>wp_create_nonce('cg_gallery_export_download_'.$state['job_id'])),admin_url('admin-post.php'));
        }
        return $data;
    }
}
if(!function_exists('cg_gallery_export_context')){
    function cg_gallery_export_context($gallery){
        global $wpdb;
        $p = $wpdb->prefix;
        $options = cg_gallery_export_results($wpdb->prepare("SELECT AllowRating FROM {$p}contest_gal1ery_options WHERE id = %d",$gallery));
        if(!$options){throw new Exception('This gallery no longer exists.',404);}
        $pro = cg_gallery_export_results($wpdb->prepare("SELECT IsModernFiveStar, Manipulate FROM {$p}contest_gal1ery_pro_options WHERE GalleryID = %d",$gallery));
        return array(
            'options'=>(array)$options[0],
            'pro_options'=>$pro ? (array)$pro[0] : array('IsModernFiveStar'=>0,'Manipulate'=>0),
            'categories'=>cg_gallery_export_results($wpdb->prepare("SELECT * FROM {$p}contest_gal1ery_categories WHERE GalleryID = %d ORDER BY Field_Order DESC",$gallery)),
            'fields'=>cg_gallery_export_results($wpdb->prepare("SELECT id, Field_Type, Field_Order, Field_Content FROM {$p}contest_gal1ery_f_input WHERE GalleryID = %d AND Field_Type IN ('fbt-f','fbd-f','url-f','check-f','text-f','radio-f','chk-f','comment-f','email-f','select-f','selectc-f','date-f') ORDER BY Field_Order ASC",$gallery)),
            'max_vote_id'=>absint(cg_gallery_export_var($wpdb->prepare("SELECT MAX(id) FROM {$p}contest_gal1ery_ip WHERE GalleryID = %d",$gallery)))
        );
    }
}
if(!function_exists('cg_gallery_export_vote_condition')){
    function cg_gallery_export_vote_condition($context,$alias='v'){
        // Export all stored votes, including ratings from a previously enabled mode.
        return "($alias.RatingS = 1 OR $alias.Rating BETWEEN 1 AND 10)";
    }
}
if(!function_exists('cg_gallery_export_selection')){
    function cg_gallery_export_selection($state,$count=false){
        global $wpdb;
        $p=$wpdb->prefix;
        $gallery=absint($state['gallery_id']);
        if($state['type']==='votes_csv'){
            $sql=" FROM {$p}contest_gal1ery_ip v INNER JOIN {$p}contest_gal1ery g ON g.id=v.pid AND g.GalleryID=v.GalleryID WHERE v.GalleryID=$gallery AND v.id <= ".absint($state['context']['max_vote_id']).' AND '.cg_gallery_export_vote_condition($state['context']);
            return $count ? 'SELECT COUNT(*)'.$sql : 'SELECT v.id'.$sql.' AND v.id < '.absint($state['cursor']).' ORDER BY v.id DESC LIMIT '.max(1,(int)CG_GALLERY_EXPORT_VOTES_BATCH);
        }
        $sql=" FROM {$p}contest_gal1ery g WHERE g.GalleryID=$gallery AND g.id <= ".absint($state['max_entry_id']);
        if($state['type']==='files_zip'){$sql.=" AND g.WpUpload > 0 AND g.ImgType NOT IN ('ytb','inst','tkt','twt')";}
        return $count ? 'SELECT COUNT(*)'.$sql : 'SELECT g.id, g.WpUpload, g.MultipleFiles'.$sql.' AND g.id < '.absint($state['cursor']).' ORDER BY g.id DESC LIMIT '.max(1,(int)CG_GALLERY_EXPORT_ENTRIES_BATCH);
    }
}
if(!function_exists('cg_gallery_export_write_csv')){
    function cg_gallery_export_write_csv($state,$ids,$file,$headers=false){
        $tmp=$file.'.tmp';
        $fp=fopen($tmp,'wb');
        if(!$fp){throw new Exception('The CSV file could not be created.',500);}
        @chmod($tmp,0600);
        try{
            if($state['type']==='votes_csv'){
                require_once(__DIR__.'/export-votes-all.php');
                cg_votes_csv_write_batch($state['gallery_id'],$fp,$state['context'],$ids,$headers);
            }else{
                require_once(__DIR__.'/export-images-data.php');
                cg_images_data_csv_write_batch($state['gallery_id'],$fp,$state['context'],$ids,$headers);
            }
            if(!fflush($fp)){throw new Exception('The CSV file could not be flushed.',500);}
        }catch(Exception $e){fclose($fp);@unlink($tmp);throw $e;}
        fclose($fp);
        if(!rename($tmp,$file)){@unlink($tmp);throw new Exception('The CSV part could not be saved.',500);}
    }
}
if(!function_exists('cg_gallery_export_start')){
    function cg_gallery_export_start($type,$gallery){
        global $wpdb;
        if(!in_array($type,array('files_zip','votes_csv','fields_csv'),true) || !$gallery){throw new Exception('Invalid export request.',400);}
        if(!cg_gallery_export_allowed($type)){throw new Exception('You do not have permission to create this export.',403);}
        if($type==='files_zip' && !class_exists('ZipArchive')){throw new Exception('The PHP ZIP extension is not available. Please contact your hosting provider.',400);}
        $context=cg_gallery_export_context($gallery);
        $max=absint(cg_gallery_export_var($wpdb->prepare("SELECT MAX(id) FROM {$wpdb->prefix}contest_gal1ery WHERE GalleryID = %d",$gallery)));
        $id=time().'-'.wp_generate_password(24,false,false);
        $dir=cg_gallery_export_dir($id);
        if(!mkdir($dir,0700)){throw new Exception('The export directory could not be created.',500);}
        $state=array('job_id'=>$id,'owner_id'=>get_current_user_id(),'blog_id'=>get_current_blog_id(),'gallery_id'=>$gallery,'type'=>$type,'status'=>'processing','phase'=>$type==='files_zip' ? 'collect' : 'export','created_at'=>time(),'updated_at'=>time(),'context'=>$context,'max_entry_id'=>$max,'cursor'=>($type==='votes_csv' ? $context['max_vote_id'] : $max)+1,'processed'=>0,'total'=>0,'skipped'=>0,'chunks'=>0,'files'=>0,'manifest'=>0,'record'=>0,'publish_offset'=>0);
        wp_schedule_single_event(time()+DAY_IN_SECONDS,'cg_gallery_export_cleanup_event',array($id));
        try{
            $state['total']=absint(cg_gallery_export_var(cg_gallery_export_selection($state,true)));
            if($type==='files_zip'){
                $state['target']=cg_gallery_export_zip_target($gallery);
                $state['staging']=dirname($state['target']['path']).'/.cg-export-'.$id.'.partial';
                if(!mkdir($dir.'/seen',0700) || !mkdir($dir.'/names',0700)){throw new Exception('The file index could not be created.',500);}
            }else{
                cg_gallery_export_write_csv($state,array(),$dir.'/header.csvpart',true);
                if(!$state['total']){$state['status']='done';}
            }
            cg_gallery_export_save($state);
        }catch(Exception $e){wp_clear_scheduled_hook('cg_gallery_export_cleanup_event',array($id));cg_gallery_export_remove_dir($dir);throw $e;}
        return $state;
    }
}
if(!function_exists('cg_gallery_export_csv_step')){
    function cg_gallery_export_csv_step($state){
        $rows=cg_gallery_export_results(cg_gallery_export_selection($state));
        if(!$rows){$state['status']='done';$state['total']=$state['processed'];return $state;}
        $ids=array();
        foreach($rows as $row){$ids[]=absint($row->id);}
        $dir=cg_gallery_export_dir($state['job_id']);
        cg_gallery_export_write_csv($state,$ids,$dir.'/'.sprintf('chunk-%06d.csvpart',$state['chunks']));
        $state['cursor']=min($ids);
        $state['processed']+=count($ids);
        $state['chunks']++;
        return $state;
    }
}
if(!function_exists('cg_gallery_export_original_path')){
    function cg_gallery_export_original_path($attachment,$fallback){
        $uploads=wp_upload_dir();
        $base=realpath($uploads['basedir']);
        $paths=array();
        if($attachment && function_exists('wp_get_original_image_path')){$paths[]=wp_get_original_image_path($attachment);}
        if($attachment){$paths[]=get_attached_file($attachment);}
        foreach($paths as $path){
            $real=$path ? realpath($path) : false;
            if($real && $base && is_file($real) && is_readable($real) && strpos($real,$base.DIRECTORY_SEPARATOR)===0){return $real;}
        }
        if(!$fallback && $attachment){$fallback=get_post_field('guid',$attachment);}
        $urlPath=$fallback ? wp_parse_url($fallback,PHP_URL_PATH) : '';
        $basePath=rtrim((string)wp_parse_url($uploads['baseurl'],PHP_URL_PATH),'/').'/';
        if(!$urlPath || strpos($urlPath,$basePath)!==0){return '';}
        $real=realpath($uploads['basedir'].'/'.rawurldecode(substr($urlPath,strlen($basePath))));
        return $real && $base && is_file($real) && strpos($real,$base.DIRECTORY_SEPARATOR)===0 ? $real : '';
    }
}
if(!function_exists('cg_gallery_export_zip_collect')){
    function cg_gallery_export_zip_collect($state){
        $dir=cg_gallery_export_dir($state['job_id']);
        $manifestFile=$dir.'/'.sprintf('manifest-%06d.json',$state['chunks']);
        if(is_file($manifestFile)){
            $manifest=cg_gallery_export_read($manifestFile);
        }else{
            $rows=cg_gallery_export_results(cg_gallery_export_selection($state));
            if(!$rows){
                $state['phase']='pack';$state['processed']=0;$state['total']=$state['files'];
                return $state;
            }
            $manifest=array('records'=>array(),'cursor'=>$state['cursor'],'entries'=>count($rows),'skipped'=>0);
            $references=array();$attachments=array();
            foreach($rows as $row){
                $manifest['cursor']=min($manifest['cursor'],(int)$row->id);
                $multiple=!empty($row->MultipleFiles) && $row->MultipleFiles!=='""' ? maybe_unserialize($row->MultipleFiles) : array();
                if(!is_array($multiple) || !$multiple){$multiple=array(array('WpUpload'=>$row->WpUpload));}
                foreach($multiple as $file){
                    $attachment=!empty($file['isRealIdSource']) ? absint($row->WpUpload) : (!empty($file['WpUpload']) ? absint($file['WpUpload']) : 0);
                    $references[]=array($attachment,!empty($file['guid']) ? $file['guid'] : '');
                    if($attachment){$attachments[$attachment]=$attachment;}
                }
            }
            foreach(array_chunk(array_values($attachments),100) as $ids){_prime_post_caches($ids,false,true);cg_gallery_export_check_db();}
            foreach($references as $ref){
                $path=cg_gallery_export_original_path($ref[0],$ref[1]);
                if(!$path || !is_readable($path)){$manifest['skipped']++;continue;}
                $key=hash('sha256',$path);
                $seen=$dir.'/seen/'.$key.'.json';
                if(is_file($seen)){
                    $record=cg_gallery_export_read($seen);
                    if((int)$record['chunk']!==(int)$state['chunks']){continue;}
                }else{
                    $name=wp_basename($path);
                    $nameFile=$dir.'/names/'.hash('sha256',strtolower($name)).'.json';
                    if(is_file($nameFile)){
                        $ext=pathinfo($name,PATHINFO_EXTENSION);
                        $stem=$ext!=='' ? substr($name,0,-strlen($ext)-1) : $name;
                        $name=$stem.'-'.substr($key,0,12).($ext!=='' ? '.'.$ext : '');
                    }
                    // Reserve the final archive name, including collision suffixes.
                    while(is_file($dir.'/names/'.hash('sha256',strtolower($name)).'.json')){$name=$key.'-'.$name;}
                    cg_gallery_export_atomic($dir.'/names/'.hash('sha256',strtolower($name)).'.json',array('path'=>$key));
                    $record=array('path'=>$path,'name'=>$name,'size'=>filesize($path),'chunk'=>$state['chunks']);
                    cg_gallery_export_atomic($seen,$record);
                }
                $manifest['records'][$key]=$record;
            }
            $manifest['records']=array_values($manifest['records']);
            cg_gallery_export_atomic($manifestFile,$manifest);
        }
        $state['cursor']=$manifest['cursor'];$state['processed']+=$manifest['entries'];$state['skipped']+=$manifest['skipped'];
        $state['files']+=count($manifest['records']);$state['chunks']++;
        return $state;
    }
}
if(!function_exists('cg_gallery_export_zip_pack')){
    function cg_gallery_export_zip_pack($state){
        $dir=cg_gallery_export_dir($state['job_id']);
        $records=array();$bytes=0;
        while($state['manifest']<$state['chunks'] && count($records)<max(1,(int)CG_GALLERY_EXPORT_ZIP_BATCH)){
            $manifest=cg_gallery_export_read($dir.'/'.sprintf('manifest-%06d.json',$state['manifest']));
            if($state['record']>=count($manifest['records'])){$state['record']=0;$state['manifest']++;continue;}
            $record=$manifest['records'][$state['record']];
            if($records && $bytes+$record['size']>CG_GALLERY_EXPORT_ZIP_BYTES){break;}
            $records[]=$record;$bytes+=$record['size'];$state['record']++;
        }
        if(!$records){
            if(!$state['processed']){throw new Exception('No readable original files were found in this gallery.',400);}
            $state['phase']='publish';return $state;
        }
        $zip=new ZipArchive();
        if($zip->open($dir.'/archive.zip',ZipArchive::CREATE)!==true){throw new Exception('The ZIP archive could not be opened.',500);}
        $failure=null;
        foreach($records as $record){
            // A committed archive entry is reused when its state write/HTTP response was interrupted.
            if($zip->locateName($record['name'])!==false){$state['processed']++;continue;}
            $uploads=wp_upload_dir();$base=realpath($uploads['basedir']);$real=realpath($record['path']);
            if(!$base || !$real || strpos($real,$base.DIRECTORY_SEPARATOR)!==0 || !is_file($real) || !is_readable($real)){
                $state['skipped']++;$state['total']--;continue;
            }
            if(!$zip->addFile($real,$record['name'])){$failure=new Exception('A file could not be added to the ZIP archive.',500);break;}
            $state['processed']++;
        }
        $closed=$zip->close();
        if($failure){throw $failure;}
        if(!$closed){throw new Exception('The ZIP archive could not be saved. Check available disk space and try again.',500);}
        @chmod($dir.'/archive.zip',0600);
        return $state;
    }
}
if(!function_exists('cg_gallery_export_zip_publish')){
    function cg_gallery_export_zip_publish($state){
        $state['target']=cg_gallery_export_zip_state_target($state);
        $dir=cg_gallery_export_dir($state['job_id']);
        $source=$dir.'/archive.zip';
        if(!is_file($source)){throw new Exception('The completed ZIP archive is missing.',500);}
        if(!is_dir(dirname($state['target']['path'])) || !is_writable(dirname($state['target']['path']))){throw new Exception('The gallery download directory is not writable.',500);}
        if(!$state['publish_offset']){
            $zip=new ZipArchive();
            if($zip->open($source,ZipArchive::CHECKCONS)!==true){throw new Exception('The ZIP archive could not be verified.',500);}
            $valid=$zip->numFiles===$state['processed'];$zip->close();
            if(!$valid){throw new Exception('The ZIP archive is incomplete. Please start again.',500);}
        }
        if(!is_file($state['staging'])){$state['publish_offset']=0;}
        $input=fopen($source,'rb');$output=fopen($state['staging'],'c+b');
        if(!$input || !$output){
            if(is_resource($input)){fclose($input);}if(is_resource($output)){fclose($output);}
            throw new Exception('The ZIP download could not be prepared.',500);
        }
        @chmod($state['staging'],0600);
        $ok=fseek($input,$state['publish_offset'])===0 && ftruncate($output,$state['publish_offset']) && fseek($output,$state['publish_offset'])===0;
        $copied=$ok ? stream_copy_to_stream($input,$output,8388608) : false;
        $ok=fflush($output);fclose($input);fclose($output);
        if($copied===false || !$ok || ($copied===0 && $state['publish_offset']<filesize($source))){throw new Exception('The ZIP download could not be written.',500);}
        $state['publish_offset']+=$copied;
        if($state['publish_offset']<filesize($source)){return $state;}
        $lock=cg_gallery_export_lock(cg_gallery_export_base().'/gallery-'.absint($state['gallery_id']).'.lock');
        try{
            if(is_file($dir.'/cancel')){throw new Exception('The export was cancelled.',409);}
            if(!rename($state['staging'],$state['target']['path'])){throw new Exception('The ZIP download could not be published.',500);}
            @chmod($state['target']['path'],0644);
        }catch(Exception $e){cg_gallery_export_unlock($lock);throw $e;}
        cg_gallery_export_unlock($lock);
        $state['status']='done';return $state;
    }
}
if(!function_exists('cg_gallery_export_remove_dir')){
    function cg_gallery_export_remove_dir($dir){
        $base=realpath(cg_gallery_export_base());$real=realpath($dir);
        if(!$base || !$real || dirname($real)!==$base || is_link($dir)){return;}
        $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $item){
            if($item->isDir() && !$item->isLink()){@rmdir($item->getPathname());}else{@unlink($item->getPathname());}
        }
        @rmdir($real);
    }
}
if(!function_exists('cg_gallery_export_cleanup')){
    function cg_gallery_export_cleanup($id){
        $lock=null;
        try{
            $dir=cg_gallery_export_dir($id);
            if(!is_dir($dir)){return;}
            $lock=cg_gallery_export_lock($dir.'/job.lock');
            $state=is_file($dir.'/state.json') ? cg_gallery_export_read($dir.'/state.json') : array('updated_at'=>filemtime($dir));
            if(empty($state['updated_at']) || $state['updated_at']<=time()-DAY_IN_SECONDS){
                if(!empty($state['staging']) && basename($state['staging'])==='.cg-export-'.$id.'.partial'){@unlink($state['staging']);}
                cg_gallery_export_remove_dir($dir);
            }else{wp_schedule_single_event($state['updated_at']+DAY_IN_SECONDS,'cg_gallery_export_cleanup_event',array($id));}
        }catch(Exception $e){wp_schedule_single_event(time()+HOUR_IN_SECONDS,'cg_gallery_export_cleanup_event',array($id));}
        cg_gallery_export_unlock($lock);
    }
}
add_action('cg_gallery_export_cleanup_event','cg_gallery_export_cleanup');

if(!function_exists('cg_gallery_export_ajax')){
    function cg_gallery_export_ajax(){
        if(!defined('DOING_AJAX') || !DOING_AJAX || !is_user_logged_in()){
            wp_send_json_error(array('message'=>'You must be signed in to use gallery exports.'),403);
        }
        $nonce=isset($_POST['cg_nonce']) ? sanitize_text_field(wp_unslash($_POST['cg_nonce'])) : '';
        if(!$nonce || !wp_verify_nonce($nonce,'cg_nonce')){
            wp_send_json_error(array('message'=>'The security token is no longer valid. Please reload this page.'),403);
        }
        // CSV capability and ZIP backend roles are intentionally checked separately.
        $action=isset($_POST['action']) ? sanitize_key($_POST['action']) : '';
        $id=isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
        $lock=null;$error=null;$result=null;
        try{
            if($action==='post_cg_gallery_export_start'){
                $type=isset($_POST['export_type']) ? sanitize_key($_POST['export_type']) : '';
                $gallery=isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
                $result=cg_gallery_export_public(cg_gallery_export_start($type,$gallery));
            }elseif($action==='post_cg_gallery_export_delete_zip'){
                if(!cg_gallery_export_allowed('files_zip')){throw new Exception('You do not have permission to delete ZIP downloads.',403);}
                $gallery=isset($_POST['gallery_id']) ? absint($_POST['gallery_id']) : 0;
                if(!$gallery){throw new Exception('Invalid gallery.',400);}
                $target=cg_gallery_export_zip_target($gallery);
                $lock=cg_gallery_export_lock(cg_gallery_export_base().'/gallery-'.$gallery.'.lock');
                if(is_file($target['path']) && !unlink($target['path'])){throw new Exception('The ZIP download could not be deleted.',500);}
                $result=array('deleted'=>true);
            }else{
                $state=cg_gallery_export_state($id);$dir=cg_gallery_export_dir($id);
                if($action==='post_cg_gallery_export_cancel'){
                    if(file_put_contents($dir.'/cancel','1',LOCK_EX)!==1){throw new Exception('The export could not be cancelled.',500);}
                }
                try{$lock=cg_gallery_export_lock($dir.'/job.lock');}
                catch(Exception $e){
                    if($action!=='post_cg_gallery_export_cancel'){throw $e;}
                    $result=array('job_id'=>$id,'cancelling'=>true);
                }
                if($lock){
                    $state=cg_gallery_export_state($id);
                    if(!is_file($dir.'/cancel') && $action==='post_cg_gallery_export_step' && $state['status']!=='done'){
                        if($state['type']!=='files_zip'){$state=cg_gallery_export_csv_step($state);}
                        elseif($state['phase']==='collect'){$state=cg_gallery_export_zip_collect($state);}
                        elseif($state['phase']==='pack'){$state=cg_gallery_export_zip_pack($state);}
                        else{$state=cg_gallery_export_zip_publish($state);}
                        cg_gallery_export_save($state);
                    }
                    if(is_file($dir.'/cancel')){
                        if(!empty($state['staging'])){@unlink($state['staging']);}
                        wp_clear_scheduled_hook('cg_gallery_export_cleanup_event',array($id));
                        cg_gallery_export_remove_dir($dir);
                        $result=array('job_id'=>$id,'cancelled'=>true);
                    }else{$result=cg_gallery_export_public($state);}
                }
            }
        }catch(Exception $e){$error=$e;}
        cg_gallery_export_unlock($lock);
        if($error){wp_send_json_error(array('message'=>$error->getMessage()),in_array($error->getCode(),array(400,403,404,409,500),true) ? $error->getCode() : 500);}
        wp_send_json_success($result);
    }
}
foreach(array('start','step','status','cancel','delete_zip') as $cgGalleryExportAction){add_action('wp_ajax_post_cg_gallery_export_'.$cgGalleryExportAction,'cg_gallery_export_ajax');}

if(!function_exists('cg_gallery_export_download')){
    function cg_gallery_export_download(){
        $lock=null;
        try{
            $id=isset($_GET['job_id']) ? sanitize_text_field(wp_unslash($_GET['job_id'])) : '';
            check_admin_referer('cg_gallery_export_download_'.$id,'download_nonce');
            $state=cg_gallery_export_state($id);$dir=cg_gallery_export_dir($id);
            $lock=cg_gallery_export_lock($dir.'/job.lock');
            $state=cg_gallery_export_state($id);
            if($state['type']==='files_zip' || $state['status']!=='done' || is_file($dir.'/cancel')){throw new Exception('This CSV export is not available.',409);}
            // Check every part before sending headers. Never silently omit a missing part.
            $length=0;
            for($i=-1;$i<$state['chunks'];$i++){
                $file=$dir.'/'.($i<0 ? 'header.csvpart' : sprintf('chunk-%06d.csvpart',$i));
                if(!is_file($file) || !is_readable($file)){throw new Exception('The CSV export is incomplete. Please start again.',500);}
                $length+=filesize($file);
            }
            $state['updated_at']=time();cg_gallery_export_save($state);
            $filename=($state['type']==='votes_csv' ? 'cg-votes-gallery-id-' : 'cg-images-data-gallery-id-').$state['gallery_id'].'.csv';
        }catch(Exception $e){cg_gallery_export_unlock($lock);wp_die(esc_html($e->getMessage()),'',array('response'=>in_array($e->getCode(),array(400,403,404,409,500),true) ? $e->getCode() : 500));}
        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('X-Content-Type-Options: nosniff');header('X-Accel-Buffering: no');
        header('Content-Length: '.$length);
        if(function_exists('set_time_limit')){@set_time_limit(0);}
        while(ob_get_level()){ob_end_clean();}
        for($i=-1;$i<$state['chunks'];$i++){
            $fp=fopen($dir.'/'.($i<0 ? 'header.csvpart' : sprintf('chunk-%06d.csvpart',$i)),'rb');
            if(!$fp){break;}
            $failed=false;
            while(!feof($fp)){$bytes=fread($fp,1048576);if($bytes===false){$failed=true;break;}echo $bytes;flush();}
            fclose($fp);
            if($failed){break;}
        }
        cg_gallery_export_unlock($lock);exit;
    }
}
add_action('admin_post_cg_gallery_export_download','cg_gallery_export_download');

if(!function_exists('cg_gallery_export_legacy_redirect')){
    function cg_gallery_export_legacy_redirect($type){
        if(!cg_gallery_export_allowed($type)){wp_die('You do not have permission to create this export.','',array('response'=>403));}
        cg_check_nonce();
        $gallery=isset($_POST['cg_option_id']) ? absint($_POST['cg_option_id']) : (isset($_GET['option_id']) ? absint($_GET['option_id']) : 0);
        wp_safe_redirect(add_query_arg(array('page'=>cg_get_version().'/index.php','option_id'=>$gallery,'edit_gallery'=>'true','cg_export_type'=>$type,'cg_nonce'=>wp_create_nonce('cg_nonce')),admin_url('admin.php')));
        exit;
    }
}
