<?php

echo <<<HEREDOC

<div class='cg_view_container'>

HEREDOC;

echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
    <p class='cg_view_options_rows_container_title'>For all <span class="cg_font_weight_bold">cg_gallery...</span> shortcodes</p>
</div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_margin_bottom_30'>
            <div class='cg_view_option cg_view_option_100_percent cg_border_radius_8_px $cgProFalse cg_border_border_bottom_left_radius_unset cg_border_border_bottom_right_radius_unset ' id="ConsentYoutubeContainer">
                <div class='cg_view_option_title' >
                    <p>Ask users for consent (GDPR) to show YouTube entries<br><span class="cg_view_option_title_note">Users will have to agree YouTube's privacy policy</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="ConsentYoutube" id="ConsentYoutube" $ConsentYoutube>
                </div>
            </div>
            <div class='cg_view_option cg_border_top_none   cg_view_option_100_percent  $cgProFalse' id="ConsentTwitterContainer">
                <div class='cg_view_option_title' >
                    <p>Ask users for consent (GDPR) to show Twitter (X) entries<br><span class="cg_view_option_title_note">Users will have to agree Twitter's (X's) privacy policy</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="ConsentTwitter" id="ConsentTwitter" $ConsentTwitter>
                </div>
            </div>
            <div class='cg_view_option cg_border_top_none  cg_view_option_100_percent  $cgProFalse' id="ConsentInstagramContainer">
                <div class='cg_view_option_title' >
                    <p>Ask users for consent (GDPR) to show Instagram entries<br><span class="cg_view_option_title_note">Users will have to agree Instagram privacy policy</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="ConsentInstagram" id="ConsentInstagram" $ConsentInstagram>
                </div>
            </div>
            <div class='cg_view_option cg_border_top_none cg_border_border_top_left_radius_unset cg_border_border_top_right_radius_unset cg_view_option_100_percent cg_border_radius_8_px $cgProFalse' id="ConsentTikTokContainer">
                <div class='cg_view_option_title' >
                    <p>Ask users for consent (GDPR) to show TikTok entries<br><span class="cg_view_option_title_note">Users will have to agree TikTok privacy policy</span></p>
                </div>
                <div class='cg_view_option_checkbox'>
                    <input type="checkbox" name="ConsentTikTok" id="ConsentTikTok" $ConsentTikTok>
                </div>
            </div>
        </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


