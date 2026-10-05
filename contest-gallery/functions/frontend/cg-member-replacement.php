<?php
if(!defined('ABSPATH')){exit;}

// Gallery-wide rules deliberately live outside the shortcode-specific variants.
function cg_member_replacement_source($gid){
	$upload = wp_upload_dir();
	$path = $upload['basedir'].'/contest-gallery/gallery-id-'.absint($gid).'/json/'.absint($gid).'-options.json';
	$data = is_file($path) ? json_decode(file_get_contents($path),true) : array();
	return is_array($data) ? $data : array();
}

function cg_member_replacement_options($gid){
	$data = cg_member_replacement_source($gid);
	$rules = array_merge(array('mode'=>'unlimited','duration'=>24,'unit'=>'hours','deadline'=>0,'noVotes'=>1),!empty($data['memberReplacement']) && is_array($data['memberReplacement']) ? $data['memberReplacement'] : array());
	// The former upload mode had the same policy as unlimited. Keep saved galleries compatible.
	if($rules['mode']==='upload'){$rules['mode'] = 'unlimited';}
	return $rules;
}

function cg_member_replacement_timezone(){
	if(function_exists('wp_timezone')){return wp_timezone();}
	$name = get_option('timezone_string');
	if(!$name){
		$offset = (float)get_option('gmt_offset');
		$name = sprintf('%s%02d:%02d',$offset<0 ? '-' : '+',abs((int)$offset),abs((int)round($offset*60))%60);
	}
	return new DateTimeZone($name);
}

function cg_member_replacement_save_options(&$options,$gid,$input){
	$rules = cg_member_replacement_options($gid);
	if(cg_member_features_available($gid) && is_array($input)){
		$rules['mode'] = isset($input['mode']) && in_array($input['mode'],array('off','upload','duration','date','unlimited'),true) ? $input['mode'] : 'off';
		if($rules['mode']==='upload'){$rules['mode'] = 'unlimited';}
		$rules['duration'] = max(1,min(525600,isset($input['duration']) ? absint($input['duration']) : 24));
		$rules['unit'] = isset($input['unit']) && in_array($input['unit'],array('minutes','hours','days'),true) ? $input['unit'] : 'hours';
		$rules['noVotes'] = empty($input['noVotes']) ? 0 : 1;
		$rules['deadline'] = 0;
		if(!empty($input['date']) && is_string($input['date'])){
			$date = DateTime::createFromFormat('!Y-m-d\TH:i',$input['date'],cg_member_replacement_timezone());
			if($date && $date->format('Y-m-d\TH:i')===$input['date']){$rules['deadline'] = $date->getTimestamp()+59;}
		}
	}
	$options['memberReplacement'] = $rules;
}

function cg_member_replacement_render_options($gid){
	$selectStyle = cg_member_features_available($gid) ? '' : ' style="background-repeat:no-repeat;background-position:right 8px center;background-size:16px 16px;"';
	$disabledClass = cg_member_enabled($gid,'cg_gallery') ? '' : ' cg_disabled';
	$rules = cg_member_replacement_options($gid);
	$bottomRadius = ' cg_border_border_bottom_left_radius_8_px cg_border_border_bottom_right_radius_8_px';
	$modeBottomBorder = $rules['mode']!=='off' ? ' cg_border_bottom_none' : '';
	$date = '';
	if(!empty($rules['deadline'])){$dt = new DateTime('@'.absint($rules['deadline'])); $dt->setTimezone(cg_member_replacement_timezone()); $date = $dt->format('Y-m-d\TH:i');}
	echo '<div class="cg_member_replace_options"'.($disabledClass ? ' inert aria-disabled="true"' : '').'>';
	echo '<div class="cg_view_options_row cg_member_replace_mode_row"><div class="cg_view_option cg_view_option_full_width cg_border_top_none'.$disabledClass.($rules['mode']==='off' ? $bottomRadius : '').$modeBottomBorder.'"><div class="cg_view_option_title"><p>Allow members to replace images<br><span class="cg_view_option_title_note">Applies to all personal views of this gallery, including entries with multiple files. Title and description editing and deletion are not affected.</span></p></div><div class="cg_view_option_input"><select name="cg_member_replace[mode]"'.$selectStyle.'>';
	foreach(array('unlimited'=>'No time limit','duration'=>'For a duration after the original upload','date'=>'Until a specific date and time','off'=>'Do not allow') as $value=>$label){echo '<option value="'.esc_attr($value).'"'.selected($rules['mode'],$value,false).'>'.esc_html($label).'</option>';}
	echo '</select></div>';
	if(contest_gal1ery_key_check()){
		echo '<div class="cg_view_option_title cg_member_replace_interval_note"><p><span class="cg_view_option_title_note"><span class="cg_note_label">NOTE:</span> <span class="cg_note_text">If upload interval restriction is enabled, images can only be replaced during the allowed upload times.<br>This also applies to “No time limit”. <a href="#" class="cg_member_replace_upload_times cg_no_outline_and_shadow_on_focus">Configure upload times...</a></span></span></p></div>';
	}
	echo '</div></div>';
	echo '<div class="cg_view_options_row cg_member_replace_duration_row'.($rules['mode']==='duration' ? '' : ' cg_hide').'"><div class="cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none'.$disabledClass.'"><div class="cg_view_option_title"><p>Duration after the original upload<br><span class="cg_view_option_title_note">Used only for the duration option. Replacing an image does not restart this period.</span></p></div><div class="cg_view_option_input"><input type="number" min="1" max="525600" name="cg_member_replace[duration]" value="'.absint($rules['duration']).'"><select name="cg_member_replace[unit]"'.$selectStyle.'>';
	foreach(array('minutes'=>'Minutes','hours'=>'Hours','days'=>'Days') as $value=>$label){echo '<option value="'.$value.'"'.selected($rules['unit'],$value,false).'>'.$label.'</option>';}
	echo '</select></div></div></div>';
	echo '<div class="cg_view_options_row cg_member_replace_date_row'.($rules['mode']==='date' ? '' : ' cg_hide').'"><div class="cg_view_option cg_view_option_full_width cg_border_top_none cg_border_bottom_none'.$disabledClass.'"><div class="cg_view_option_title"><p>Replacement deadline<br><span class="cg_view_option_title_note">Used only for the specific date option. WordPress timezone: '.esc_html(cg_member_replacement_timezone()->getName()).'. An empty deadline does not allow replacement.</span></p></div><div class="cg_view_option_input"><input type="datetime-local" name="cg_member_replace[date]" value="'.esc_attr($date).'"></div></div></div>';
	echo '<div class="cg_view_options_row cg_member_replace_votes_row'.($rules['mode']==='off' ? ' cg_hide' : '').'"><div class="cg_view_option cg_view_option_100_percent cg_border_top_none'.$disabledClass.$bottomRadius.'"><div class="cg_view_option_title"><p>Only allow replacement before the first vote</p></div><div class="cg_view_option_checkbox"><input type="hidden" name="cg_member_replace[noVotes]" value="0"><input type="checkbox" name="cg_member_replace[noVotes]" value="1"'.checked(!empty($rules['noVotes']),true,false).'></div></div></div>';
	echo '</div>';
}

function cg_member_replacement_vote_columns(){
	$columns = array('CountS','CountR','Rating','addCountS');
	for($i=1;$i<=10;$i++){$columns[] = 'CountR'.$i; $columns[] = 'addCountR'.$i;}
	return $columns;
}

// Return untranslated keys so the same policy can be tested without a locale.
function cg_member_replacement_policy($row,$rules,$source,$now = null){
	if($now===null){$now=time();}
	if($rules['mode']==='off'){return 'Image replacement is not allowed.';}
	if(!empty($row->EcommerceEntry)){return 'Entries offered for sale cannot be changed here.';}
	if(!empty($rules['noVotes'])){
		foreach(cg_member_replacement_vote_columns() as $column){if(!empty($row->$column)){return 'This entry already has votes. Its images cannot be replaced.';}}
	}
	if($rules['mode']==='duration'){
		$units = array('minutes'=>60,'hours'=>3600,'days'=>86400);
		$seconds = isset($units[$rules['unit']]) ? $units[$rules['unit']] : 3600;
		if(empty($row->Timestamp) || $now>=(int)$row->Timestamp+(int)$rules['duration']*$seconds){return 'The time allowed for replacing this image has ended.';}
	}
	if($rules['mode']==='date' && (empty($rules['deadline']) || $now>(int)$rules['deadline'])){return 'The time allowed for replacing this image has ended.';}
	// All replacements honor an explicitly closed upload schedule, even in unlimited mode.
	$interval = cg_shortcode_interval_check($row->GalleryID,$source,'cg_users_upload');
	if(!$interval['shortcodeIsActive']){return 'The upload period is closed.';}
	return '';
}

require_once __DIR__.'/cg-member-entry-actions.php';
