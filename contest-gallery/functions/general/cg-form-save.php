<?php
if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_form_save_check')){
    function cg_form_save_check($result){
        if(is_wp_error($result)){throw new Exception($result->get_error_message());}
        if($result === false){throw new Exception('The form could not be saved. Please try again.');}
        return $result;
    }
}
if(!function_exists('cg_form_save_state')){
    function cg_form_save_state($kind, $gallery, $general){
        global $wpdb;
        $table = $wpdb->prefix.'contest_gal1ery_'.($kind==='upload' ? 'f_input' : 'create_user_form');
        $scope = $kind==='registry' && $general ? 'GeneralID=1' : 'GalleryID='.(int)$gallery;
        // A legacy gallery form must never include general-form fields.
        if($kind==='registry' && !$general){$scope .= ' AND GeneralID=0';}
        $fields = $wpdb->get_results("SELECT * FROM $table WHERE $scope ORDER BY id", ARRAY_A);
        if($wpdb->last_error || !is_array($fields)){throw new Exception('The form could not be read. Reload the editor.');}
        $state = array('fields'=>$fields,'captcha'=>array());
        foreach($fields as $field){
            if(in_array($field['Field_Type'],array('caRoRe-f','user-robot-recaptcha-field'),true)){
                $record=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",cg_captcha_option($kind,$field['id'])));
                if($wpdb->last_error){throw new Exception('The CAPTCHA settings could not be read.');}
                $state['captcha'][$field['id']]=$record;
            }
        }
        if($kind==='upload'){
            foreach(array('categories','options_visual','pro_options','f_output') as $suffix){
                $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest_gal1ery_$suffix WHERE GalleryID=%d ORDER BY id",$gallery),ARRAY_A);
                if($wpdb->last_error || !is_array($rows)){throw new Exception('The form settings could not be read.');}
                $state[$suffix] = $rows;
            }
        }
        $state['revision'] = hash('sha256',serialize(array($wpdb->prefix,$kind,$general ? 'general' : $gallery,$state)));
        return $state;
    }
}
if(!function_exists('cg_form_editor_fields')){
    function cg_form_editor_fields($rows){
        usort($rows,function($a,$b){
            if((int)$a['Field_Order']===(int)$b['Field_Order']){return $a['id']==$b['id'] ? 0 : ($a['id']<$b['id'] ? -1 : 1);}
            return (int)$a['Field_Order']<(int)$b['Field_Order'] ? -1 : 1;
        });
        return array_map(function($row){return (object)$row;},$rows);
    }
}
if(!function_exists('cg_form_save_hidden')){
    function cg_form_save_hidden($kind,$gallery,$general=false,$state=null){
        try{
            if($state===null){$state = cg_form_save_state($kind,$gallery,$general);}
            echo '<input type="hidden" name="cg_form_revision" value="'.esc_attr($state['revision']).'">';
            echo '<input type="hidden" name="cg_form_kind" value="'.esc_attr($kind).'">';
        }catch(Exception $error){echo '<p role="alert">'.esc_html($error->getMessage()).'</p>';}
    }
}
if(!function_exists('cg_form_save_require_parts')){
    function cg_form_save_require_parts($field,$parts){
        foreach($parts as $part){
            if(!array_key_exists($part,$field) || !is_scalar($field[$part])){throw new Exception('Incomplete form data. Reload the editor before saving.');}
        }
    }
}
if(!function_exists('cg_form_save_validate_layouts')){
    function cg_form_save_validate_layouts($fields){
        $rows=array();$slots=array();
        $layouts=array('1'=>1,'1-1'=>2,'2-1'=>2,'1-2'=>2,'1-1-1'=>3);
        foreach($fields as $field){
            cg_form_save_require_parts($field,array('RowNumber','ColNumber','RowCols','RowLayout'));
            foreach(array('RowNumber','ColNumber','RowCols') as $part){
                if(!ctype_digit((string)$field[$part]) || (int)$field[$part]<1 || (int)$field[$part]>100000){throw new Exception('Invalid form field position.');}
            }
            $row=(int)$field['RowNumber'];$col=(int)$field['ColNumber'];$cols=(int)$field['RowCols'];$layout=$field['RowLayout'];
            if(!isset($layouts[$layout]) || $layouts[$layout]!==$cols || $col>$cols){throw new Exception('Invalid form row layout.');}
            if(isset($rows[$row]) && $rows[$row]!==$layout){throw new Exception('Conflicting form row layouts.');}
            if(isset($slots[$row.':'.$col])){throw new Exception('Two fields cannot occupy the same position.');}
            $rows[$row]=$layout;$slots[$row.':'.$col]=true;
        }
    }
}
if(!function_exists('cg_form_save_validate')){
    function cg_form_save_validate($kind,$state,$general,$version,$isPro){
        foreach(array('isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized','option_id','submit') as $part){if(isset($_POST[$part]) && !is_scalar($_POST[$part])){throw new Exception('Invalid form request.');}}
        $stored=array();foreach($state['fields'] as $field){$stored[(int)$field['id']]=$field;}
        $deleted=array();$seen=array();$fields=array();$counts=array();
        if(!empty($_POST['deleteFieldnumber'])){
            $raw=$_POST['deleteFieldnumber'];
            if(!is_array($raw)){throw new Exception('Invalid field deletion.');}
            if($kind==='upload'){
                $raw=array();
                if(isset($_POST['deleteFieldnumber']['normalFields'])){
                    if(!is_array($_POST['deleteFieldnumber']['normalFields'])){throw new Exception('Invalid field deletion.');}
                    $raw=$_POST['deleteFieldnumber']['normalFields'];
                }
                if(isset($_POST['deleteFieldnumber']['deleteCategoryField'])){$raw[]=$_POST['deleteFieldnumber']['deleteCategoryField'];}
            }
            foreach($raw as $id){
                if(!is_scalar($id) || !ctype_digit((string)$id) || !isset($stored[(int)$id]) || isset($deleted[(int)$id])){throw new Exception('Invalid field deletion.');}
                if(in_array($stored[(int)$id]['Field_Type'],array('image-f','main-user-name','main-nick-name','main-mail','password','password-confirm'),true)){throw new Exception('This required field cannot be deleted.');}
                if($kind==='upload' && (($stored[(int)$id]['Field_Type']==='selectc-f') !== (isset($_POST['deleteFieldnumber']['deleteCategoryField']) && (string)$_POST['deleteFieldnumber']['deleteCategoryField']===(string)$id))){throw new Exception('Invalid category field deletion.');}
                $deleted[(int)$id]=true;
            }
        }
        if($kind==='registry'){
            $parts=array('Field_Type','Field_Id','Field_Order','Field_Name','Field_Content','Min_Char','Max_Char','RowNumber','ColNumber','RowCols','RowLayout','Necessary','Hide','PinField','PublicProfile');
            foreach($parts as $part){
                if(isset($_POST[$part]) && !is_array($_POST[$part])){throw new Exception('Invalid registration fields.');}
                if(!isset($_POST[$part])){$_POST[$part]=array();}
                foreach($_POST[$part] as $index=>$value){
                    if(!ctype_digit((string)$index) || !(int)$index || !is_scalar($value)){throw new Exception('Invalid registration field data.');}
                }
            }
            foreach($parts as $part){foreach($_POST[$part] as $index=>$unused){if(!isset($_POST['Field_Type'][$index])){throw new Exception('Invalid registration field index.');}}}
            foreach($_POST['Field_Type'] as $index=>$type){
                $field=array();foreach($parts as $part){if(isset($_POST[$part][$index])){$field[$part]=$_POST[$part][$index];}}
                $field['type']=$type;$field['id']=isset($field['Field_Id']) ? $field['Field_Id'] : '';
                $fields[$index]=$field;
            }
            $types=array('user-text-field','user-url-field','user-comment-field','user-select-field','user-check-field','user-radio-field','user-html-field','user-check-agreement-field','user-robot-field','user-robot-recaptcha-field','profile-image','wpfn','wpln','main-user-name','main-nick-name','main-mail','password','password-confirm');
            $proTypes=array('user-select-field','user-check-field','user-radio-field','user-html-field','user-check-agreement-field','user-robot-recaptcha-field');
            foreach(array('ReCaKey','ReCaLang','ReCaSecret') as $part){if(isset($_POST[$part]) && !is_scalar($_POST[$part])){throw new Exception('Invalid CAPTCHA settings.');}}
        }else{
            if(empty($_POST['upload']) || !is_array($_POST['upload'])){throw new Exception('Incomplete upload form.');}
            $types=array('bh'=>'image-f','nf'=>'text-f','kf'=>'comment-f','fbt'=>'fbt-f','fbd'=>'fbd-f','dt'=>'date-f','url'=>'url-f','ef'=>'email-f','ht'=>'html-f','cb'=>'check-f','caRo'=>'caRo-f','caRoRe'=>'caRoRe-f','chk'=>'chk-f','ra'=>'radio-f','se'=>'select-f','sec'=>'selectc-f');
            $proTypes=array('dt','ef','ht','cb','chk','ra');if((float)$version>=27){$proTypes=array_merge($proTypes,array('url','se','caRoRe'));}
            foreach($_POST['upload'] as $index=>$field){
                if($index==='new-0'){continue;}
                if(!is_array($field)){throw new Exception('Invalid upload field.');}
                foreach($field as $value){if(!is_scalar($value)){throw new Exception('Invalid upload field settings.');}}
                if(preg_match('/^new-[1-9][0-9]*$/',(string)$index)){$field['id']='';$_POST['upload'][$index]['new']='true';}
                elseif(ctype_digit((string)$index) && (int)$index>0 && empty($field['new'])){$field['id']=$index;}
                else{throw new Exception('Invalid upload field ID.');}
                $fields[$index]=$field;
            }
        }
        if(!$fields){throw new Exception('Incomplete form data.');}
        foreach($fields as $index=>$field){
            cg_form_save_require_parts($field,array('type','id'));
            $type=$field['type'];$id=$field['id'];
            if(($kind==='registry' && !in_array($type,$types,true)) || ($kind==='upload' && !isset($types[$type]))){throw new Exception('Unsupported form field type.');}
            $dbType=$kind==='registry' ? $type : $types[$type];
            if($id!==''){
                if(!ctype_digit((string)$id) || !isset($stored[(int)$id]) || $stored[(int)$id]['Field_Type']!==$dbType || isset($seen[(int)$id]) || isset($deleted[(int)$id])){throw new Exception('The field does not belong to this form.');}
                $seen[(int)$id]=true;
            }elseif(in_array($dbType,array('image-f','fbt-f','fbd-f','main-user-name','main-nick-name','main-mail','password','password-confirm'),true) || (!$isPro && in_array($type,$proTypes,true))){throw new Exception('This field type cannot be added.');}
            if($kind==='registry' && !$general && in_array($type,array('wpfn','wpln','user-url-field'),true)){throw new Exception('This field requires the general registration form.');}
            $required=array('RowNumber','ColNumber','RowCols','RowLayout');
            if($kind==='registry'){
                if($type!=='user-robot-recaptcha-field'){$required[]='Field_Name';}
                if(!in_array($type,array('profile-image','user-robot-field','user-robot-recaptcha-field'),true)){$required[]='Field_Content';}
                if(in_array($type,array('user-text-field','user-url-field','user-comment-field','wpfn','wpln','main-user-name','main-nick-name','password','password-confirm'),true)){$required[]= 'Min_Char';$required[]='Max_Char';}
            }else{
                $required[]='order';if($type!=='caRoRe'){$required[]='title';}
                if(in_array($type,array('nf','kf','fbt','fbd','url','ef','ht','cb','chk','ra','se'),true)){$required[]='content';}
                if(in_array($type,array('nf','kf','fbt','fbd'),true)){$required[]='min-char';$required[]='max-char';}
                if($type==='dt'){$required[]='format';}
                if(in_array($type,array('nf','kf','dt','url','chk','ra','se','sec'),true)){$required[]='FieldTitleGallery';}
            }
            cg_form_save_require_parts($field,$required);
            $counts[$type]=isset($counts[$type]) ? $counts[$type]+1 : 1;
            // Only restrict new additions, so existing older forms remain editable.
            $single=in_array($type,array('bh','sec','caRo','caRoRe','profile-image','wpfn','wpln','user-robot-field','user-robot-recaptcha-field'),true);
            if($id==='' && $single){foreach($stored as $old){if($old['Field_Type']===$dbType && !isset($deleted[$old['id']])){throw new Exception('This field can be added only once.');}}}
        }
        foreach($counts as $type=>$count){if($count>1 && in_array($type,array('bh','sec','caRo','caRoRe','profile-image','wpfn','wpln','user-robot-field','user-robot-recaptcha-field'),true)){throw new Exception('Duplicate unique form field.');}}
        foreach($stored as $id=>$field){if(!isset($seen[$id]) && !isset($deleted[$id])){throw new Exception('The request is missing existing fields. Reload the editor.');}}
        cg_form_save_validate_layouts($fields);
        if($kind==='upload'){
            foreach($fields as $index=>$field){if($field['type']==='bh' && ((int)$field['RowNumber']!==1 || (int)$field['ColNumber']!==1 || $field['RowLayout']!=='1')){throw new Exception('The file field must remain in the first full-width row.');}}
            cg_form_save_validate_categories($state,$deleted);
            // Never trust stale left-panel ordering.
            $positions=array();foreach($fields as $index=>$field){$positions[$index]=(int)$field['RowNumber']*100+(int)$field['ColNumber'];}
            asort($positions,SORT_NUMERIC);$rank=0;foreach($positions as $index=>$position){$_POST['upload'][$index]['order']=++$rank;}
        }
        return true;
    }
}
if(!function_exists('cg_form_save_validate_categories')){
    function cg_form_save_validate_categories($state,$deletedFields){
        $ids=array();foreach($state['categories'] as $row){$ids[(int)$row['id']]=true;}
        $seen=array();$deleted=array();
        foreach(array('cg_category','deleteCategory') as $part){if(isset($_POST[$part]) && !is_array($_POST[$part])){throw new Exception('Invalid category data.');}}
        foreach(isset($_POST['deleteCategory']) ? $_POST['deleteCategory'] : array() as $id){
            if(!is_scalar($id) || !ctype_digit((string)$id) || !isset($ids[(int)$id]) || isset($deleted[(int)$id])){throw new Exception('Invalid category deletion.');}$deleted[(int)$id]=true;
        }
        foreach(isset($_POST['cg_category']) ? $_POST['cg_category'] : array() as $value){
            if(is_array($value)){
                if(count($value)!==1){throw new Exception('Invalid category assignment.');}
                foreach($value as $id=>$name){
                    if(!ctype_digit((string)$id) || !isset($ids[(int)$id]) || isset($seen[(int)$id]) || isset($deleted[(int)$id]) || !is_string($name)){throw new Exception('Invalid category assignment.');}$seen[(int)$id]=true;
                }
            }elseif(!is_string($value)){throw new Exception('Invalid category name.');}
        }
        $categoryFieldDeleted=false;foreach($state['fields'] as $field){if($field['Field_Type']==='selectc-f' && isset($deletedFields[$field['id']])){$categoryFieldDeleted=true;}}
        if(!$categoryFieldDeleted){foreach($ids as $id=>$unused){if(!isset($seen[$id]) && !isset($deleted[$id])){throw new Exception('The request is missing existing categories.');}}}
        if($categoryFieldDeleted && !empty($_POST['cg_category'])){
            // A newly added category field can clone the old option labels. Their
            // old IDs are about to be deleted, so insert these validated labels anew.
            foreach($_POST['cg_category'] as $index=>$value){
                if(is_array($value)){$_POST['cg_category'][$index]=reset($value);}
            }
        }
    }
}
if(!function_exists('cg_form_save_is_transactional')){
    function cg_form_save_is_transactional($tables){
        global $wpdb;
        $transactional=true;
        $suppress=$wpdb->suppress_errors(true);
        try{
            foreach($tables as $table){
                $engine=$wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',$table));
                if($wpdb->last_error || !$engine){
                    // Some hosts restrict information_schema. Do not require it to save a form.
                    $status=$wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS WHERE Name=%s',$table),ARRAY_A);
                    $engine=(!$wpdb->last_error && isset($status['Engine'])) ? $status['Engine'] : '';
                    if(!$engine){
                        // Distinguish unavailable engine metadata from missing/inaccessible tables.
                        $identifier='`'.str_replace('`','``',$table).'`';
                        if($wpdb->query('SELECT 1 FROM '.$identifier.' LIMIT 0')===false){
                            throw new Exception('The form tables could not be read. No changes were saved.');
                        }
                    }
                }
                if(strtoupper((string)$engine)!=='INNODB'){$transactional=false;}
            }
        }finally{$wpdb->suppress_errors($suppress);}
        return $transactional;
    }
}
if(!function_exists('cg_form_save_begin')){
    function cg_form_save_begin(&$context,$kind,$gallery,$general,$version,$isPro){
        global $wpdb;
        $context=array('lock'=>'','transaction'=>false,'committed'=>false,'non_transactional'=>false,'writes_started'=>false,'options'=>array(),'users'=>array());
        if(!isset($_POST['cg_form_complete'],$_POST['cg_form_kind'],$_POST['cg_form_revision']) || $_POST['cg_form_complete']!=='1' || $_POST['cg_form_kind']!==$kind || !is_string($_POST['cg_form_revision'])){throw new Exception('Incomplete form request. Reload the editor before saving.');}
        $lock='cg-form-'.md5((defined('DB_NAME') ? DB_NAME : '').$wpdb->prefix.$kind.($general ? 'general' : $gallery));
        if((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$lock))!=='1'){throw new Exception('This form is being saved in another tab. Please try again.');}
        $context['lock']=$lock;
        register_shutdown_function(function() use (&$context){cg_form_save_release($context);});
        $cgGalleryVersion=$wpdb->get_var($wpdb->prepare("SELECT Version FROM {$wpdb->prefix}contest_gal1ery_options WHERE id=%d",$gallery));
        if($wpdb->last_error || $cgGalleryVersion===null || ($kind==='registry' && ((int)$cgGalleryVersion>=14)!==(bool)$general)){throw new Exception('The form gallery could not be read. Reload the editor.');}
        $state=cg_form_save_state($kind,$gallery,$general);
        if(!hash_equals($state['revision'],$_POST['cg_form_revision'])){throw new Exception('This form has changed in another tab. Reload it before saving. Your current input has been kept.');}
        cg_form_save_validate($kind,$state,$general,$version,$isPro);
        $context['fields']=array();foreach($state['fields'] as $field){$context['fields'][$field['id']]=$field;}
        $context['categories']=array();if(isset($state['categories'])){foreach($state['categories'] as $category){$context['categories'][$category['id']]=$category;}}
        $suffixes=$kind==='upload' ? array('','_f_input','_f_output','_entries','_categories','_options_visual','_pro_options') : array('_create_user_form','_create_user_entries');
        $tables=array($wpdb->options);foreach($suffixes as $suffix){$tables[]=$wpdb->prefix.'contest_gal1ery'.$suffix;}
        if($kind==='registry' && $general){$tables[]=$wpdb->usermeta;}
        $context['non_transactional']=!cg_form_save_is_transactional($tables);
        if(!$context['non_transactional']){
            cg_form_save_check($wpdb->query('START TRANSACTION'));$context['transaction']=true;
        }
    }
}
if(!function_exists('cg_form_save_commit')){
    function cg_form_save_commit(&$context){
        global $wpdb;
        if((string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)=CONNECTION_ID()',$context['lock']))!=='1'){throw new Exception('The database connection was interrupted. Reload the form.');}
        if(!empty($context['transaction'])){cg_form_save_check($wpdb->query('COMMIT'));}
        $context['transaction']=false;$context['committed']=true;
    }
}
if(!function_exists('cg_form_save_prepare_url_refresh')){
    function cg_form_save_prepare_url_refresh(&$context,$gallery,$requested=false){
        global $wpdb;
        $name='cg_upload_form_url_refresh_'.(int)$gallery;
        $before=array();
        foreach($context['fields'] as $field){if(!empty($field['IsForWpPageTitle'])){$before[]=(int)$field['id'];}}
        $after=$wpdb->get_col($wpdb->prepare("SELECT id FROM {$wpdb->prefix}contest_gal1ery_f_input WHERE GalleryID=%d AND IsForWpPageTitle=1 ORDER BY id",$gallery));
        if($wpdb->last_error || !is_array($after)){throw new Exception('The entry URL settings could not be read.');}
        $after=array_map('intval',$after);sort($before,SORT_NUMERIC);sort($after,SORT_NUMERIC);
        $pending=$wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s",$name));
        if($wpdb->last_error){throw new Exception('The pending entry URL update could not be read.');}
        if(!$requested && $before===$after && $pending!=='1'){return false;}
        // This write shares the form transaction. A failed file refresh must not
        // lose the work when the browser replaces the editor after the commit.
        cg_form_save_check($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,'1','no') ON DUPLICATE KEY UPDATE option_value='1',autoload='no'",$name)));
        $context['options'][]=$name;
        return true;
    }
}
if(!function_exists('cg_form_save_finish_url_refresh')){
    function cg_form_save_finish_url_refresh(&$context,$gallery){
        global $wpdb;
        // A reconnect must not clear a newer worker's pending update.
        if((string)$wpdb->get_var($wpdb->prepare('SELECT IS_USED_LOCK(%s)=CONNECTION_ID()',$context['lock']))!=='1'){throw new Exception('The database connection was interrupted. Reload the form.');}
        $name='cg_upload_form_url_refresh_'.(int)$gallery;
        cg_form_save_check($wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name=%s",$name)));
        $context['options'][]=$name;
    }
}
if(!function_exists('cg_form_save_release')){
    function cg_form_save_release(&$context){
        global $wpdb;
        if(!$context){return;}
        if(!empty($context['transaction'])){$wpdb->query('ROLLBACK');$context['transaction']=false;}
        if(!empty($context['committed']) || (!empty($context['non_transactional']) && !empty($context['writes_started']))){foreach($context['users'] as $userId){wp_cache_delete($userId,'user_meta');}}
        foreach($context['options'] as $name){wp_cache_delete($name,'options');}
        if($context['options']){wp_cache_delete('alloptions','options');wp_cache_delete('notoptions','options');}
        if(!empty($context['lock'])){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$context['lock']));$context['lock']='';}
    }
}
if(!function_exists('cg_form_save_captcha')){
    function cg_form_save_captcha(&$context,$kind,$id,$record){
        global $wpdb;
        if(!is_array($record) || !$id){return;}
        $name=cg_captcha_option($kind,$id);
        cg_form_save_check($wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->options} (option_name,option_value,autoload) VALUES (%s,%s,'no') ON DUPLICATE KEY UPDATE option_value=VALUES(option_value)",$name,maybe_serialize($record))));
        $context['options'][]=$name;
    }
}
if(!function_exists('cg_form_save_response')){
    function cg_form_save_response($status,$message=''){
        echo '<div class="cg_form_save_response" data-cg-form-save-status="'.esc_attr($status).'"'.($status==='saved' ? ' hidden' : ' role="alert"').'>'.esc_html($message).'</div>';
    }
}
if(!function_exists('cg_form_save_failure')){
    function cg_form_save_failure($context,$error){
        if(!empty($context['committed'])){cg_form_save_response('committed_refresh_failed','The form was saved, but the frontend files could not be refreshed. The editor now shows the saved form. Save again to retry.');}
        elseif(!empty($context['non_transactional']) && !empty($context['writes_started'])){cg_form_save_response('partial_save_failed','The form could not be saved completely. Some changes may already have been saved. Reload the editor and review the form before saving again.');}
        else{cg_form_save_response('rejected',$error->getMessage());}
    }
}
if(!function_exists('cg_form_atomic_file')){
    function cg_form_atomic_file($file,$payload){
        if(!is_string($payload)){return false;}
        $dir=dirname($file);if(!is_dir($dir) && !wp_mkdir_p($dir)){return false;}
        $temporary=@tempnam($dir,'cg-form-');
        if(!$temporary || dirname($temporary)!==realpath($dir)){if($temporary){@unlink($temporary);}return false;}
        $written=@file_put_contents($temporary,$payload,LOCK_EX);
        if($written!==strlen($payload) || !@rename($temporary,$file)){@unlink($temporary);return false;}
        clearstatcache(true,$file);return true;
    }
}
if(!function_exists('cg_form_write_json')){
    function cg_form_write_json($file,$data){return cg_form_atomic_file($file,json_encode($data));}
}
if(!function_exists('cg_form_refresh_frontend_cache')){
    function cg_form_refresh_frontend_cache($gallery){
        $uploads=wp_upload_dir();
        $base=$uploads['basedir'].'/contest-gallery/gallery-id-'.(int)$gallery.'/json/segments';
        // Repair lists inflated by an earlier bulk form save. Keep the newest
        // real entry markers within the same limits as normal frontend updates.
        foreach(array('image-main-data-last-update'=>10,'image-info-data-last-update'=>20) as $segment=>$limit){
            $files=glob($base.'/'.$segment.'/*.txt');
            if($files===false){return false;}
            $times=array();
            foreach($files as $file){
                $mtime=@filemtime($file);
                if($mtime===false){if(file_exists($file)){return false;}continue;}
                $times[$file]=$mtime;
            }
            asort($times,SORT_NUMERIC);
            $remove=count($times)-$limit;
            foreach($times as $file=>$mtime){
                if($remove--<=0){break;}
                if(!@unlink($file) && file_exists($file)){return false;}
            }
        }
        // A timestamp alone leaves freshly built gzip files usable for three
        // seconds. Remove them only after all replacement source files exist.
        foreach(array('main','info','urls') as $type){
            $file=$base.'/images-'.$type.'-data.json.gz.php';
            if(file_exists($file) && !@unlink($file)){return false;}
            clearstatcache(true,$file);
            $marker=$base.'/image-'.$type.'-data-last-update.txt';
            $previous=file_exists($marker) ? @file_get_contents($marker) : '0';
            if($previous===false){return false;}
            // IndexedDB refreshes only for a strictly newer marker, including
            // when two form saves finish in the same second.
            $timestamp=max(time(),(int)$previous+1);
            if(!cg_form_atomic_file($marker,(string)$timestamp)){return false;}
        }
        return true;
    }
}
