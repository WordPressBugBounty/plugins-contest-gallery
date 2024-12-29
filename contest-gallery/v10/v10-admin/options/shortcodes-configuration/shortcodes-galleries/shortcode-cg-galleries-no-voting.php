<?php


echo <<<HEREDOC
<div class="cg_view cgGalleriesOptions cg_short_code_galleries_configuration_cg_gallery_no_voting_container cg_short_code_galleries_configuration_container cgViewHelper1 cg_hide">
<div class='cg_view_container'>
HEREDOC;
echo <<<HEREDOC
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width   cg_border_bottom_none'>
	        <div class='cg_view_option_title'>
	        <p>Number of galleries per screen<br><span class="cg_view_option_title_note">Pagination</span></p>
	        </div>
	        <div class='cg_view_option_input'>
	        <input type="text" name="cg_galleries[nv][PicsPerSite]" class="PicsPerSite" maxlength="3" value="{$galleriesOptions['nv']['PicsPerSite']}" style="max-width: 50px; text-align: center;">
	        </div>
	    </div>
</div>
HEREDOC;

$PreviewLastAdded = (!empty($galleriesOptions['nv']['PreviewLastAdded'])) ? 'checked' : '';
$PreviewHighestRated = (!empty($galleriesOptions['nv']['PreviewHighestRated'])) ? 'checked' : '';
$PreviewMostCommented = (!empty($galleriesOptions['nv']['PreviewMostCommented'])) ? 'checked' : '';

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
                        <input type="radio" name="cg_galleries[nv][PreviewLastAdded]" class="BlogLookFullWindow cg_view_option_radio_multiple_input_field"  $PreviewLastAdded  />
                    </div>
                </div>
                <div class='cg_view_option_radio_multiple_container SliderFullWindowContainer $cgProFalse'  style="width:33.33%;height:100%;">
                    <div class='cg_view_option_radio_multiple_title'>
                        Show highest rated entry
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[nv][PreviewHighestRated]" class="SliderFullWindow cg_view_option_radio_multiple_input_field"  $PreviewHighestRated   />
                    </div>
                </div>
                 <div class='cg_view_option_radio_multiple_container ForwardToWpPageEntryContainer $cgProFalse'  style="width:33.33%;height:100%;">
			        <div class='cg_view_option_radio_multiple_title'>
			            Show most commented entry
			        </div>
			        <div class='cg_view_option_radio_multiple_input'>
			            <input type="radio" name="cg_galleries[nv][PreviewMostCommented]" class="ForwardToWpPageEntry cg_view_option_radio_multiple_input_field"  $PreviewMostCommented  />
			        </div>
				</div>
        </div>
    </div>
</div>
HEREDOC;


$FeControlsStyleWhiteChecked = ($galleriesOptions['nv']['FeControlsStyle']=='white') ? 'checked' : '';
$FeControlsStyleBlackChecked = ($galleriesOptions['nv']['FeControlsStyle']=='black') ? 'checked' : '';

echo <<<HEREDOC
<div class="cg_view_options_row">
        <div class="cg_view_option cg_view_option_100_percent" id="BorderRadiusContainer">
            <div class="cg_view_option_title">
                <p>Round borders for all control elements and containers</p>
            </div>
            <div class="cg_view_option_checkbox cg_view_option_checked">
                <input type="checkbox" name="cg_galleries[nv][BorderRadius]" class="cg_shortcode_checkbox BorderRadius" checked="{$galleriesOptions['nv']['BorderRadius']}">
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
                        <input type="radio" name="cg_galleries[nv][FeControlsStyle]" class="FeControlsStyleWhite cg_view_option_radio_multiple_input_field" $FeControlsStyleWhiteChecked value="white"/>
                    </div>
                </div>
                <div class='cg_view_option_radio_multiple_container'>
                    <div class='cg_view_option_radio_multiple_title'>
                        Dark style
                    </div>
                    <div class='cg_view_option_radio_multiple_input'>
                        <input type="radio" name="cg_galleries[nv][FeControlsStyle]" class="FeControlsStyleBlack cg_view_option_radio_multiple_input_field" $FeControlsStyleBlackChecked value="black">
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
	        <input type="text" maxlength="3" name="cg_galleries[nv][WidthThumb]" class="WidthThumb" value="{$galleriesOptions['nv']['WidthThumb']}" >
	     </div>
	</div>
	<div class='cg_view_option cg_view_option_50_percent'>
	    <div class='cg_view_option_title'>
	            <p>Height thumbs (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="3" name="cg_galleries[nv][HeightThumb]" class="HeightThumb" value="{$galleriesOptions['nv']['HeightThumb']}" >
	     </div>
	</div>
</div>
<div class='cg_view_options_row hide'>
	<div class='cg_view_option cg_view_option_50_percent cg_border_top_right_none DistancePicsContainer'>
	    <div class='cg_view_option_title'>
	            <p>Distance between thumbs horizontal (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="2" name="cg_galleries[nv][DistancePics]" class="DistancePics" value="{$galleriesOptions['nv']['DistancePics']}">
	     </div>
	</div>
	<div class='cg_view_option cg_view_option_50_percent cg_border_top_none DistancePicsVContainer cg_border_border_bottom_right_radius_8_px'>
	    <div class='cg_view_option_title'>
	            <p>Distance between thumbs vertical (px)</p>
	     </div>
	     <div  class='cg_view_option_input'>
	        <input type="text" maxlength="2" name="cg_galleries[nv][DistancePicsV]" class="DistancePicsV" value="{$galleriesOptions['nv']['DistancePicsV']}">
	     </div>
	</div>
</div>
HEREDOC;

if($galleryDbVersion>=24){
	$galleriesOptions['nv']['GalleriesPageRedirectURL'] = contest_gal1ery_convert_for_html_output_without_nl2br($galleriesOptions['nv']['GalleriesPageRedirectURL']);

	$slugName = (!empty($CgEntriesOwnSlugNameGalleriesNoVoting)) ? $CgEntriesOwnSlugNameGalleriesNoVoting : 'contest-galleries-no-voting';

	$page = get_page_by_path( $slugName, OBJECT, 'page');
	$pageGalleries = (!empty($page)) ? get_permalink($page->ID) : '';

	echo <<<HEREDOC
    <div class='cg_view_options_row'>
        <div class='cg_view_option cg_view_option_full_width cg_border_top_none  '>
            <div class='cg_view_option_title '>
                <p>Redirect URL for cg_galleries_no_voting page of Contest Gallery<br><span class="cg_view_option_title_note">The galleries page for cg_galleries_no_voting shortcode is<br><a target="_blank"  href="$pageGalleries">$pageGalleries</a><br>(But you can place the shortcode also on any other page)<br>If Redirect URL is set, then HTTP 301 redirect will be executed if the above URL gets called<br><span class="cg_font_weight_500">NOTE: </span> has to start with <span class="cg_font_weight_500">http://</span> or <span class="cg_font_weight_500">https://</span>, like https://www.example.com</span></p>
            </div>
            <div class='cg_view_option_input '>
                <input type="text" name='cg_galleries[nv][GalleriesPageRedirectURL]' class="GalleriesPageRedirectURL"  value="{$galleriesOptions['nv']['GalleriesPageRedirectURL']}"  >
            </div>
        </div>
    </div>
HEREDOC;
}


echo <<<HEREDOC
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title' style="margin-top: 10px;">
	        	<p>Main title cg_galleries_no_voting view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="MainTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title'>
	        	<p>Sub title cg_galleries_no_voting view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="SubTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
<div class="cg_view_options_row">
	    <div class='cg_view_option cg_view_option_full_width  cg_border_top_none'>
	        <div class='cg_view_option_title'>
	        	<p>Third title cg_galleries_no_voting view for this gallery<br><span class="cg_view_option_title_note">Can be configured <a class="cg_go_to_link cg_no_outline_and_shadow_on_focus" href="#" data-cg-go-to-link="ThirdTitleGalleriesViewArea">here</a></span></p>
	        </div>
	    </div>
</div>
HEREDOC;

echo <<<HEREDOC
</div>
</div>
HEREDOC;

