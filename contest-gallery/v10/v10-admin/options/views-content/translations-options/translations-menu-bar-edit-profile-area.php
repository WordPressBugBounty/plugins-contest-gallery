<?php

$beforeSinceV14Disabled = '';
$beforeSinceV14Explanation = '';

if(intval($galleryDbVersion)<14){
    $beforeSinceV14Disabled = 'cg_disabled';
    $beforeSinceV14Explanation =  <<<HEREDOC
        <br><br><strong><span class="cg_color_red">NOTE:</span> available only for galleries created or copied in plugin version 14 or higher</strong>
HEREDOC;
}

echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
         <p class='cg_view_options_rows_container_title'>Logged in user menu bar and edit profile area$beforeSinceV14Explanation</p>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_Account$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_Account]" maxlength="100" value="$translations[$l_Account]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_EditProfile$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_EditProfile]" maxlength="100" value="$translations[$l_EditProfile]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_RemoveProfileImage$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_RemoveProfileImage]" maxlength="100" value="$translations[$l_RemoveProfileImage]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_LogOut$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_LogOut]" maxlength="100" value="$translations[$l_LogOut]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_BackToGallery$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_BackToGallery]" maxlength="100" value="$translations[$l_BackToGallery]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_BackToGalleries$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_BackToGalleries]" maxlength="100" value="$translations[$l_BackToGalleries]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_NoGalleryEntries$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_NoGalleryEntries]" maxlength="100" value="$translations[$l_NoGalleryEntries]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_CGProfileInformation$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_CGProfileInformation]" maxlength="100" value="$translations[$l_CGProfileInformation]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_ThisFileTypeIsNotAllowed$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_ThisFileTypeIsNotAllowed]" maxlength="100" value="$translations[$l_ThisFileTypeIsNotAllowed]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_TheFileYouChoosedIsToBigMaxAllowedSize$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_TheFileYouChoosedIsToBigMaxAllowedSize]" maxlength="100" value="$translations[$l_TheFileYouChoosedIsToBigMaxAllowedSize]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_ChooseYourImage$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_ChooseYourImage]" maxlength="100" value="$translations[$l_ChooseYourImage]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_Required$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_Required]" maxlength="100" value="$translations[$l_Required]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none $beforeSinceV14Disabled' >
                <div class='cg_view_option_title'>
                    <p>$language_Remove$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_Remove]" maxlength="100" value="$translations[$l_Remove]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;


echo <<<HEREDOC
<div class='cg_view_options_rows_container'>
         <p class='cg_view_options_rows_container_title'>Social embed translations</p>
HEREDOC;

echo <<<HEREDOC
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width' >
                <div class='cg_view_option_title'>
                    <p>$language_contentBlocked$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_contentBlocked]" maxlength="100" value="$translations[$l_contentBlocked]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ToViewTheContentYouMustAgreePrivacyPolicy$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_ToViewTheContentYouMustAgreePrivacyPolicy]" maxlength="100" value="$translations[$l_ToViewTheContentYouMustAgreePrivacyPolicy]">
                    </div>
                </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_IAgree$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_IAgree]" maxlength="100" value="$translations[$l_IAgree]">
                    </div>
                </div>
      </div>
     <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_ByAgreeingAllContentUnblocked$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_ByAgreeingAllContentUnblocked]" maxlength="100" value="$translations[$l_ByAgreeingAllContentUnblocked]">
                    </div>
                </div>
      </div>
     <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_content$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_content]" maxlength="100" value="$translations[$l_content]">
                </div>
            </div>
      </div>
      <div class='cg_view_options_row' >
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_UntilCurrentTabGetsClosed$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_UntilCurrentTabGetsClosed]" maxlength="100" value="$translations[$l_UntilCurrentTabGetsClosed]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
        <div class='cg_view_options_row cg_go_to_target' data-cg-go-to-target="l_BackToGallery">
            <div class='cg_view_option cg_view_option_full_width cg_border_top_none' >
                <div class='cg_view_option_title'>
                    <p>$language_BackToGallery$cgShortcodeCopy</span></p>
                </div>
                <div class='cg_view_option_input'>
                    <input type="text" name="translations[general][$l_BackToGallery]" maxlength="100" value="$translations[$l_BackToGallery]">
                    </div>
                </div>
      </div>
HEREDOC;

echo <<<HEREDOC
</div>
HEREDOC;
// take care next row has to be after HEREDOC in file end
