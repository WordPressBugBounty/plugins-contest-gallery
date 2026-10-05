<?php
if(!defined('ABSPATH')){exit;}

// The outer renderer has already checked access and rendered the public gallery hero.
cg_member_render_header($galeryID,$galeryIDuserForJs,$shortcode_name,$options,'','',$WpUserIdsData);
$cgParticipantContext = array('realGid'=>$galeryID,'gid'=>$galeryIDuserForJs,'shortcodeName'=>$shortcode_name);
$cgParticipantState = array(
    'view'=>$cgParticipantInitial['cgl_view'],
    'listPage'=>!empty($cgParticipantInitial['cgl_participants_page']) ? $cgParticipantInitial['cgl_participants_page'] : 1,
    'page'=>1,'user'=>0,'profileGid'=>'',
    'search'=>isset($cgParticipantInitial['cgl_participants_search']) ? $cgParticipantInitial['cgl_participants_search'] : '',
    'sort'=>isset($cgParticipantInitial['cgl_participants_sort']) ? $cgParticipantInitial['cgl_participants_sort'] : cg_participants_default_sort($options)
);
$cgParticipantMessage = '';
$cgParticipantControls = '';
if($cgParticipantState['view']==='profile'){
    $cgParticipantData = cg_participants_profile_data($cgParticipantContext,array(
        'userId'=>isset($cgParticipantInitial['cgl_profile']) ? $cgParticipantInitial['cgl_profile'] : 0,
        'page'=>isset($cgParticipantInitial['cgl_profile_page']) ? $cgParticipantInitial['cgl_profile_page'] : 1,
        'listPage'=>$cgParticipantState['listPage']
    ));
    if(!empty($cgParticipantData['unavailable'])){
        $cgParticipantState['view'] = 'participants';
        $cgParticipantMessage = $cgParticipantData['message'];
    }else{
        $cgParticipantState['user'] = $cgParticipantData['userId'];
        $cgParticipantState['page'] = $cgParticipantData['page'];
        $cgParticipantState['profileGid'] = $cgParticipantData['gid'];
        $cgParticipantHtml = $cgParticipantData['profileHtml'].$cgParticipantData['html'];
    }
}
if($cgParticipantState['view']==='participants'){
    $cgParticipantData = cg_participants_list_data($cgParticipantContext,$options,array('page'=>$cgParticipantState['listPage'],'search'=>$cgParticipantState['search'],'sort'=>$cgParticipantState['sort']));
    $cgParticipantHtml = $cgParticipantData['html'];
    $cgParticipantControls = $cgParticipantData['controlsHtml'];
    $cgParticipantState['listPage'] = $cgParticipantData['page'];
    $cgParticipantState['sort'] = $cgParticipantData['sort'];
    $cgParticipantState['search'] = $cgParticipantData['search'];
}

if($cgParticipantControls && !empty($options['visual']['EnableSwitchStyleGalleryButton'])){
    $cgParticipantTooltip = $cgFeControlsStyle==='cg_fe_controls_style_black' ? $language_BrightStyle : $language_DarkStyle;
    $cgParticipantControls = str_replace('<div class="cg-participants-theme-slot"></div>','<div class="cg-participants-theme-slot"><div class="cg_switch_colors cg_gallery_control_element cg_hover_effect" role="button" tabindex="0" data-cg-tooltip="'.esc_attr($cgParticipantTooltip).'"></div></div>',$cgParticipantControls);
}

// Bootstrap only the existing auth, options and language context. No hidden entry gallery.
$variablesGallery['participantInitial'] = $cgParticipantState;
echo '<div class="mainCGallery cg-participants-runtime" hidden data-cg-gid="'.esc_attr($galeryIDuserForJs).'" data-cg-real-gid="'.absint($galeryID).'" data-cg-entry-id="0">';
foreach(array('options'=>$options,'variables-gallery'=>$variablesGallery,'variables-general'=>$variablesGeneral,'language-names'=>$languageNames) as $cgParticipantKey=>$cgParticipantValue){
    echo '<textarea class="cg1l-data-'.esc_attr($cgParticipantKey).'" hidden>'.esc_textarea(base64_encode(rawurlencode(wp_json_encode($cgParticipantValue)))).'</textarea>';
}
echo '</div>';
echo '<div class="cg-participants-controls-slot">'.$cgParticipantControls.'</div>';
echo '<div class="cg-participants-status" role="status" aria-live="polite">'.esc_html($cgParticipantMessage).'</div>';
echo '<div class="cg-participants-body">'.$cgParticipantHtml.'</div>';
