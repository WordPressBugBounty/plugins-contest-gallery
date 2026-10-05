<?php
if(!defined('ABSPATH')){exit;}

// Form schemas are site-local, while WordPress usermeta is shared by a network.
// Never fall back to ambiguous, unprefixed legacy values on multisite.
function cg_profile_meta_prefix(){
	global $wpdb;
	return (is_multisite() ? $wpdb->prefix : '').'cg_custom_field_id_';
}

function cg_profile_meta_key($fieldId){
	return cg_profile_meta_prefix().absint($fieldId);
}

function cg_profile_meta_field_id($key){
	return preg_match('/^'.preg_quote(cg_profile_meta_prefix(),'/').'([1-9][0-9]*)$/D',$key,$matches) ? absint($matches[1]) : 0;
}

function cg_member_profile_choices($field){
	$content = isset($field->Field_Content) ? (string)$field->Field_Content : '';
	$lines = preg_split('/\r\n|\r|\n/',html_entity_decode(stripslashes($content),ENT_QUOTES,'UTF-8'));
	return array_values(array_unique(array_filter(array_map('sanitize_text_field',$lines),'strlen')));
}

// Old registration values were HTML-encoded. Prefer an exact choice before decoding.
function cg_profile_choice_value($value,$choices){
	if(!is_scalar($value)){return '';}
	$value = (string)$value;
	if(in_array($value,$choices,true)){return $value;}
	$decoded = html_entity_decode($value,ENT_QUOTES,'UTF-8');
	return in_array($decoded,$choices,true) ? $decoded : $value;
}

// Legacy comma lists have no escaping. Prefer a complete choice, then the longest match.
// New saves use arrays and therefore never need this ambiguous legacy recovery.
function cg_profile_legacy_check_values($value,$choices,$offset,&$memo){
	if(array_key_exists($offset,$memo)){return $memo[$offset];}
	$rest = substr($value,$offset);
	foreach($choices as $choice){
		if($rest===$choice){return array($choice);}
	}
	$matches = array();
	foreach($choices as $choice){
		$length = strlen($choice);
		if($length && substr($rest,0,$length)===$choice && preg_match('/^\s*,\s*/',substr($rest,$length),$delimiter)){
			$matches[$choice] = $length+strlen($delimiter[0]);
		}
	}
	arsort($matches);
	foreach($matches as $choice=>$length){
		$tail = cg_profile_legacy_check_values($value,$choices,$offset+$length,$memo);
		if($tail!==false){return array_merge(array((string)$choice),$tail);}
	}
	$memo[$offset] = false;
	return false;
}

function cg_profile_field_value($field,$value){
	if($field->Field_Type==='user-check-field'){
		$choices = cg_member_profile_choices($field);
		if(is_array($value)){
			$result = array();
			foreach($value as $item){if(is_string($item)){$result[] = cg_profile_choice_value($item,$choices);}}
			return array_values(array_unique($result));
		}
		if(!is_scalar($value) || trim((string)$value)===''){return array();}
		$value = cg_profile_choice_value($value,$choices);
		foreach(array_unique(array($value,html_entity_decode($value,ENT_QUOTES,'UTF-8'))) as $candidate){
			$memo = array();
			$selected = cg_profile_legacy_check_values($candidate,$choices,0,$memo);
			if($selected!==false){return $selected;}
		}
		return array_map('trim',explode(',',html_entity_decode($value,ENT_QUOTES,'UTF-8')));
	}
	if(!is_scalar($value)){return '';}
	if(in_array($field->Field_Type,array('user-select-field','user-radio-field'),true)){
		return cg_profile_choice_value($value,cg_member_profile_choices($field));
	}
	return (string)$value;
}

function cg_profile_field_text($value){
	if(is_array($value)){return implode(', ',array_filter($value,'is_scalar'));}
	return is_scalar($value) ? (string)$value : '';
}

function cg_profile_field_length_attributes($field){
	$attributes = '';
	foreach(array('Min_Char'=>'min','Max_Char'=>'max') as $column=>$kind){
		if(!empty($field->$column)){
			$limit = absint($field->$column);
			$message = cg_member_t($kind==='min' ? 'Min amount of characters' : 'Max amount of characters').': '.$limit;
			$attributes .= ' data-'.$kind.'-length="'.$limit.'" data-'.$kind.'-message="'.esc_attr($message).'"';
		}
	}
	return $attributes;
}

function cg_member_profile_validate_fields($posted,$fields){
	if(!is_array($posted)){return new WP_Error('fields','Please choose a valid option.');}
	$values = array();
	$errors = array();
	$hasRequiredError = false;
	foreach($fields as $field){
		$key = (string)$field->id;
		$raw = isset($posted[$key]) ? $posted[$key] : '';
		unset($posted[$key]);
		$value = '';
		$invalid = false;
		if($field->Field_Type==='user-check-field'){
			$raw = $raw==='' ? array() : $raw;
			if(!is_array($raw)){$invalid = true;}
			else{foreach($raw as $item){if(!is_string($item)){$invalid = true;break;}}}
			if(!$invalid){
				$value = array_values(array_unique($raw));
				$invalid = (bool)array_diff($value,cg_member_profile_choices($field));
			}
		}elseif($field->Field_Type==='user-url-field'){
			$urlError = cg_registry_url_field_error($raw,$field);
			if($urlError){$errors[$key] = $urlError; if($urlError==='Required'){$hasRequiredError = true;} continue;}
			// The raw URL is already validated. Text sanitizers remove percent-encoded bytes.
			$value = trim($raw);
		}elseif(!is_string($raw)){$invalid = true;}
		elseif(in_array($field->Field_Type,array('user-select-field','user-radio-field'),true)){
			$value = $raw;
			$invalid = $value!=='' && !in_array($value,cg_member_profile_choices($field),true);
		}else{
			$value = $field->Field_Type==='user-comment-field' && function_exists('sanitize_textarea_field') ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
		}
		if($invalid){$errors[$key] = 'Please choose a valid option.';continue;}
		$empty = is_array($value) ? !$value : trim($value)==='';
		if($field->Required && $empty){$errors[$key] = 'Required';$hasRequiredError = true;continue;}
		if(!$empty && in_array($field->Field_Type,array('user-text-field','user-url-field','user-comment-field'),true)){
			$length = mb_strlen($value,'UTF-8');
			if(!empty($field->Min_Char) && $length<(int)$field->Min_Char){$errors[$key] = cg_member_t('Min amount of characters').': '.absint($field->Min_Char);continue;}
			if(!empty($field->Max_Char) && $length>(int)$field->Max_Char){$errors[$key] = cg_member_t('Max amount of characters').': '.absint($field->Max_Char);continue;}
		}
		$values[cg_profile_meta_key($key)] = $value;
	}
	if($posted){return new WP_Error('fields','You cannot edit these profile fields.');}
	if($errors){return new WP_Error($hasRequiredError ? 'required' : 'fields',$hasRequiredError ? 'Please complete the required fields.' : reset($errors),array('fieldErrors'=>$errors));}
	return $values;
}

// Called only after the registry schema/type/value checks have succeeded.
function cg_registry_field_storage_content($field){
    $value = isset($field['Field_Content']) ? $field['Field_Content'] : '';
    if($field['Field_Type']==='user-url-field'){return trim($value);}
    if($field['Field_Type']==='user-check-field'){return wp_json_encode(array('cg_choices'=>$value));}
    return cg1l_sanitize_method($value);
}

function cg_profile_registry_check_value($value){
	$decoded = is_string($value) ? json_decode($value,true) : null;
	if(is_array($decoded) && array_keys($decoded)===array('cg_choices') && is_array($decoded['cg_choices'])){
		foreach($decoded['cg_choices'] as $choice){if(!is_string($choice)){return $value;}}
		return $decoded['cg_choices'];
	}
	return $value;
}
