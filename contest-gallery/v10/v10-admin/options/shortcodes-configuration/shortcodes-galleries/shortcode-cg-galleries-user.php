<?php

echo <<<HEREDOC
<div class="cg_view cgGalleriesOptions cg_short_code_galleries_configuration_cg_gallery_user_container cg_short_code_galleries_configuration_container cg_hide cgViewHelper1">
<div class='cg_view_container'>
HEREDOC;
echo <<<HEREDOC
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width   cg_border_bottom_none'>
	        <div class='cg_view_option_title'>
	        <p>Number of galleries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
	        </div>
	        <div class='cg_view_option_input'>
	        <input type="text" name="cg_galleries[u][PicsPerSite]" class="PicsPerSite" maxlength="3" value="{$galleriesOptions['u']['PicsPerSite']}" style="max-width: 50px; text-align: center;">
	        </div>
	    </div>
</div>
HEREDOC;

$PreviewLastAdded = (!empty($galleriesOptions['u']['PreviewLastAdded'])) ? 'checked' : '';
$PreviewHighestRated = (!empty($galleriesOptions['u']['PreviewHighestRated'])) ? 'checked' : '';
$PreviewMostCommented = (!empty($galleriesOptions['u']['PreviewMostCommented'])) ? 'checked' : '';

echo <<<HEREDOC
<div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_bottom_none cg_border_radius_unset' style="padding: 15px  0 0 0;">
            <div class='cg_view_option_title'>
                <p>Entry preview to display</p>
            </div>
            <div class='cg_view_option_radio_multiple' style="height: 50px;">
                <div class='cg_view_option_radio_multiple_container BlogLookFullWindowContainer' style="width:33.33%;height:100%;">
                    <div class='cg_view_option_radio_multiple_title'>
                        Show last added entry of a gallery
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[u][PreviewLastAdded]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $PreviewLastAdded  />
                    </div>
                </div>
                <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer $cgProFalse'  style="width:33.33%;height:100%;">
                    <div class='cg_view_option_radio_multiple_title'>
                        Show highest rated entry
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[u][PreviewHighestRated]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $PreviewHighestRated   />
                    </div>
                </div>
                 <div class='cg_view_option_radio_multiple_container ForwardToWpPageEntryContainer $cgProFalse'  style="width:33.33%;height:100%;">
			        <div class='cg_view_option_radio_multiple_title'>
			            Show most commented entry
			        </div>
			        <div class='cg_view_option_radio_multiple_input'>
			            <input type="radio" name="cg_galleries[u][PreviewMostCommented]" class="ForwardToWpPageEntry cg_view_option_radio_multiple_input_field"  $PreviewMostCommented  />
			        </div>
				</div>
        </div>
    </div>
</div>
HEREDOC;

$FeControlsStyleWhiteChecked = ($galleriesOptions['u']['FeControlsStyle']=='white') ? 'checked' : '';
$FeControlsStyleBlackChecked = ($galleriesOptions['u']['FeControlsStyle']=='black') ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option cg_view_option_100_percent" id="BorderRadiusContainer">
            <div class="cg_view_option_title">
                <p>Round borders for all control elements and containers</p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="cg_galleries[u][BorderRadius]" class="cg_shortcode_checkbox BorderRadius" checked="{$galleriesOptions['u']['BorderRadius']}">
            </div>
        </div>
</div>
<div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none cg_border_top_bottom_none'>
            <div class='cg_view_option_title'>
            <p>Gallery color style</p>
            </div>
            <div class='cg_view_option_radio_multiple'>
                <div class='cg_view_option_radio_multiple_container'>
                    <div class='cg_view_option_radio_multiple_title'>
                        Bright style
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[u][FeControlsStyle]" class="FeControlsStyleWhite cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteChecked value="white"/>
                    </div>
                </div>
                <div class='cg_view_option_radio_multiple_container'>
                    <div class='cg_view_option_radio_multiple_title'>
                        Dark style
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[u][FeControlsStyle]" class="FeControlsStyleBlack cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackChecked value="black">
                    </div>
                </div>
        </div>
    </div>
</div>
HEREDOC;

echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option cg_view_option_100_percent" id="BorderRadiusContainer">
            <div class="cg_view_option_title">
                <p>Masonry view is always activated for cg_galleries... shortcodes</p>
            </div>
        </div>
</div>
<div class='cg_view_options_row hide'>
	<div class='cg_view_option cg_view_option_50_percent cg_border_right_none'>
	    <div class='cg_view_option_title'>
	            <p>Width thumbs (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="3" name="cg_galleries[u][WidthThumb]" class="WidthThumb" value="{$galleriesOptions['u']['WidthThumb']}" >
	     </div>
	</div>
	<div class='cg_view_option cg_view_option_50_percent'>
	    <div class='cg_view_option_title'>
	            <p>Height thumbs (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="3" name="cg_galleries[u][HeightThumb]" class="HeightThumb" value="{$galleriesOptions['u']['HeightThumb']}" >
	     </div>
	</div>
</div>
<div class='cg_view_options_row hide'>
	<div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none DistancePicsContainer'>
	    <div class='cg_view_option_title'>
	            <p>Distance between thumbs horizontal (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="2" name="cg_galleries[u][DistancePics]" class="DistancePics" value="{$galleriesOptions['u']['DistancePics']}">
	     </div>
	</div>
	<div class='cg_view_option cg_view_option_50_percent cg_border_top_none DistancePicsVContainer cg_border_border_bottom_right_radius_8_px'>
	    <div class='cg_view_option_title'>
	            <p>Distance between thumbs vertical (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="2" name="cg_galleries[u][DistancePicsV]" class="DistancePicsV" value="{$galleriesOptions['u']['DistancePicsV']}">
	     </div>
	</div>
</div>
HEREDOC;

if($galleryDbVersion>=24){
	$galleriesOptions['u']['GalleriesPageRedirectURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['u']['GalleriesPageRedirectURL']);

	$slugName = (!empty($CgEntriesOwnSlugNameGalleriesUser)) ? $CgEntriesOwnSlugNameGalleriesUser : 'contest-galleries-user';

	$page = get_page_by_path( $slugName, OBJECT, 'page');
	$pageGalleries = (!empty($page)) ? get_permalink($page->ID) : '';

	echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  '>
            <div class='cg_view_option_title '>
                <p>Redirect URL for cg_galleries_user page of Contest Gallery<br><span class="cg_view_option_title_note">The galleries page for cg_galleries_user shortcode is<br><a target="_blank"  href="$pageGalleries">$pageGalleries</a><br>(But you can place the shortcode also on any other page)<br>If Redirect URL is set, then HTTP 301 redirect will be executed if the above URL gets called<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='cg_galleries[u][GalleriesPageRedirectURL]' class="GalleriesPageRedirectURL"  value="{$galleriesOptions['u']['GalleriesPageRedirectURL']}"  >
            </div>
        </div>
    </div>
HEREDOC;
}


// only json option, not in database available
if(!isset($galleriesOptions['u']['HeaderWpPageGalleries'])){
    $HeaderWpPageGalleries = "";
}else{
    $HeaderWpPageGalleries = contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['u']['HeaderWpPageGalleries']);
}

echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width  cg_border_top_none '>
            <div class='cg_view_option_title '>
                <p>Header tracking code on galleries landing page<br><span class="cg_view_option_title_note">Paste your tracking scripts here —<br>for example Google Tag Manager, Google Analytics, or Meta Pixel.<br>The code will be added inside the &lt;head&gt; section of galleries landing page.</span></p>
            </div>
            <div class='cg_view_option_textarea' >
                <textarea type="text" name="cg_galleries[u][HeaderWpPageGalleries]" rows="7" style="width:100%;" class="HeaderWpPageGalleries"  >$HeaderWpPageGalleries</textarea>
            </div>
        </div>
    </div>
HEREDOC;


// only json option, not in database available
if(!isset($galleriesOptions['u']['TextBeforeWpPageGalleries'])){
    $TextBeforeWpPageGalleries = "";
}else{
    $TextBeforeWpPageGalleries = contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['u']['TextBeforeWpPageGalleries']);
}

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextBeforeWpPageGalleriesUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on galleries landing page before galleries<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_galleries... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='cg_galleries[u][TextBeforeWpPageGalleries]'  id='TextBeforeWpPageGalleriesUser'>$TextBeforeWpPageGalleries</textarea>
        </div>
    </div>
</div>
HEREDOC;


// only json option, not in database available
if(!isset($galleriesOptions['u']['TextAfterWpPageGalleries'])){
    $TextAfterWpPageGalleries = "";
}else{
    $TextAfterWpPageGalleries = contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['u']['TextAfterWpPageGalleries']);
}

echo <<<HEREDOC
<div class='cg_view_options_row'>
    <div class='cg_view_option cg_view_option_full_width cg_border_top_none' id="wp-TextAfterWpPageGalleriesUser-wrap-Container">
        <div class='cg_view_option_title'>
            <p>General text on galleries landing page after galleries<br><span class="cg_view_option_title_note">Add general text or tracking code. &lt;noscript&gt; tags are also supported.<br>The code will be inserted inside the &lt;body&gt; section of gallery landing page.<br><span class="cg_font_weight_500">NOTE: </span>appears only on gallery landing page, not if cg_galleries... shortcode is used on another page.</span></p>
        </div>
        <div class='cg_view_option_html'>
            <textarea class='cg-wp-editor-template' name='cg_galleries[u][TextAfterWpPageGalleries]'  id='TextAfterWpPageGalleriesUser'>$TextAfterWpPageGalleries</textarea>
        </div>
    </div>
</div>
HEREDOC;

if(!empty($galleriesOptions['u']['GalleriesPagesNoIndex'])){
	$GalleriesPagesNoIndex = 'checked';
}else{
	$GalleriesPagesNoIndex = '';
}
//<meta name="robots" content="noindex, nofollow">

echo <<<HEREDOC
       <div class="cg_view_options_row cg_border_top_none">
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="GalleriesPagesNoIndexContainer">
                <div class="cg_view_option_title">
                    <p style="margin-right: -30px;">
                    	Allow search engines like Google to index the /contest-galleries... pages and subpages
                    	<br><span class="cg_view_option_title_note"><b>NOTE:</b> if unchecked ...meta name="robots" content="noindex"... is set<br><b>NOTE:</b> noindex tells a robot to not index a page, it is used to keep pages out of search results</span>
                    </p>
                </div>
                <div class="cg_view_option_checkbox cg_view_option_checked">
                    <input type="checkbox" name="cg_galleries[u][GalleriesPagesNoIndex]" id="GalleriesPagesNoIndex" $GalleriesPagesNoIndex>
                </div>
            </div>
    </div>
HEREDOC;

if(!empty($galleriesOptions['u']['GalleriesPagesNoFollow'])){
	$GalleriesPagesNoFollow = 'checked';
}else{
	$GalleriesPagesNoFollow = '';
}
//<meta name="robots" content="noindex, nofollow">

echo <<<HEREDOC
       <div class="cg_view_options_row cg_border_top_none">
            <div class="cg_view_option cg_view_option_100_percent cg_border_top_none" id="GalleriesPagesNoIndexContainer">
                <div class="cg_view_option_title">
                    <p style="margin-right: -30px;">
                    	Allow search engines like Google to follow links on the /contest-galleries... pages and subpages
                    	<br><span class="cg_view_option_title_note"><b>NOTE:</b> if unchecked ...meta name="robots" content="nofollow"... is set<br><b>NOTE:</b> nofollow tells a robot not follow links on a page</span>
                    </p>
                </div>
                <div class="cg_view_option_checkbox cg_view_option_checked">
                    <input type="checkbox" name="cg_galleries[u][GalleriesPagesNoFollow]" id="GalleriesPagesNoFollow" $GalleriesPagesNoFollow>
                </div>
            </div>
    </div>
HEREDOC;


echo <<<HEREDOC
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title' style="margin-top: 10px;">
	        	<p>Main title cg_galleries_user view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="MainTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title'>
	        	<p>Sub title cg_galleries_user view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="SubTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title'>
	        	<p>Third title cg_galleries_user view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="ThirdTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
HEREDOC;


echo <<<HEREDOC
</div>
</div>
HEREDOC;
