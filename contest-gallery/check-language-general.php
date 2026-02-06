<?php
if(!defined('ABSPATH')){exit;}
$is_admin = is_admin();
//$is_frontend = (!$is_admin) ? true : false;
if(empty($is_frontend)){
    $is_frontend = false;
}

$domainDefault = 'default';
$domain = 'contest-gallery';
$domainBackend = 'contest-gallery';

$wp_upload_dir = wp_upload_dir();

if(!is_dir($wp_upload_dir['basedir'].'/contest-gallery/gallery-general/json')){
    mkdir($wp_upload_dir['basedir'].'/contest-gallery/gallery-general/json',0755,true);
}

$translationsFile = $wp_upload_dir['basedir'].'/contest-gallery/gallery-general/json/translations.json';

// translations might already exits from check-language.php
if(empty($translations)){
    $translations = array();
}

if(file_exists($translationsFile)){
    $fp = fopen($translationsFile, 'r');
    $translationsFromFile =json_decode(fread($fp,filesize($translationsFile)),true);
    fclose($fp);
    if(count($translationsFromFile)){
        foreach($translationsFromFile as $translationKey => $translation) {
            $translations[$translationKey] = contest_gal1ery_convert_for_html_output_without_nl2br($translation);
        }
    }else{
        $translations = $translationsFromFile;
    }
}

// PROFILE translations for since v14 contest gallery user group
__('Account');$l_Account = "Account";$language_Account = (!empty($translations[$l_Account]) && $is_frontend) ? $translations[$l_Account] : ((empty(trim(__($l_Account,$domain)))) ? __($l_Account,$domainDefault) : __($l_Account,$domain)); if(empty($translations[$l_Account])){$translations[$l_Account]='';}

__('Edit profile');$l_EditProfile = "Edit profile";$language_EditProfile = (!empty($translations[$l_EditProfile]) && $is_frontend) ? $translations[$l_EditProfile] : ((empty(trim(__($l_EditProfile,$domain)))) ? __($l_EditProfile,$domainDefault) : __($l_EditProfile,$domain)); if(empty($translations[$l_EditProfile])){$translations[$l_EditProfile]='';}

__('Log out');$l_LogOut = "Log out";$language_LogOut = (!empty($translations[$l_LogOut]) && $is_frontend) ? $translations[$l_LogOut] : ((empty(trim(__($l_LogOut,$domain)))) ? __($l_LogOut,$domainDefault) : __($l_LogOut,$domain)); if(empty($translations[$l_LogOut])){$translations[$l_LogOut]='';}

__('This nickname is already taken');$l_ThisNicknameAlreadyExistsGeneral= "This nickname is already taken";$language_ThisNicknameAlreadyExistsGeneral = (!empty($translations[$l_ThisNicknameAlreadyExistsGeneral]) && $is_frontend) ? $translations[$l_ThisNicknameAlreadyExistsGeneral] : ((empty(trim(__($l_ThisNicknameAlreadyExistsGeneral,$domain)))) ? __($l_ThisNicknameAlreadyExistsGeneral,$domainDefault) : __($l_ThisNicknameAlreadyExistsGeneral,$domain)); if(empty($translations[$l_ThisNicknameAlreadyExistsGeneral])){$translations[$l_ThisNicknameAlreadyExistsGeneral]='';}

__('Contest Gallery profile information');$l_CGProfileInformation = "Contest Gallery profile information";$language_CGProfileInformation = (!empty($translations[$l_CGProfileInformation]) && $is_frontend) ? $translations[$l_CGProfileInformation] : ((empty(trim(__($l_CGProfileInformation,$domain)))) ? __($l_CGProfileInformation,$domainDefault) : __($l_CGProfileInformation,$domain)); if(empty($translations[$l_CGProfileInformation])){$translations[$l_CGProfileInformation]='';}

__('Remove profile image');$l_RemoveProfileImage = "Remove profile image";$language_RemoveProfileImage = (!empty($translations[$l_RemoveProfileImage]) && $is_frontend) ? $translations[$l_RemoveProfileImage] : ((empty(trim(__($l_RemoveProfileImage,$domain)))) ? __($l_RemoveProfileImage,$domainDefault) : __($l_RemoveProfileImage,$domain)); if(empty($translations[$l_RemoveProfileImage])){$translations[$l_RemoveProfileImage]='';}

__('This file type is not allowed');$l_ThisFileTypeIsNotAllowed="This file type is not allowed";$language_ThisFileTypeIsNotAllowed = (!empty($translations[$l_ThisFileTypeIsNotAllowed]) && $is_frontend) ? $translations[$l_ThisFileTypeIsNotAllowed] : ((empty(trim(__($l_ThisFileTypeIsNotAllowed,$domain)))) ? __($l_ThisFileTypeIsNotAllowed,$domainDefault) : __($l_ThisFileTypeIsNotAllowed,$domain)); if(empty($translations[$l_ThisFileTypeIsNotAllowed])){$translations[$l_ThisFileTypeIsNotAllowed]='';}

__('The selected file is too large, max allowed size');$l_TheFileYouChoosedIsToBigMaxAllowedSize= "The selected file is too large, max allowed size";$language_TheFileYouChoosedIsToBigMaxAllowedSize = (!empty($translations[$l_TheFileYouChoosedIsToBigMaxAllowedSize]) && $is_frontend) ? $translations[$l_TheFileYouChoosedIsToBigMaxAllowedSize] : ((empty(trim(__($l_TheFileYouChoosedIsToBigMaxAllowedSize,$domain)))) ? __($l_TheFileYouChoosedIsToBigMaxAllowedSize,$domainDefault) : __($l_TheFileYouChoosedIsToBigMaxAllowedSize,$domain)); if(empty($translations[$l_TheFileYouChoosedIsToBigMaxAllowedSize])){$translations[$l_TheFileYouChoosedIsToBigMaxAllowedSize]='';}

__('Select your image');$l_ChooseYourImage= "Select your image";$language_ChooseYourImage = (!empty($translations[$l_ChooseYourImage]) && $is_frontend) ? $translations[$l_ChooseYourImage] : ((empty(trim(__($l_ChooseYourImage,$domain)))) ? __($l_ChooseYourImage,$domainDefault) : __($l_ChooseYourImage,$domain)); if(empty($translations[$l_ChooseYourImage])){$translations[$l_ChooseYourImage]='';}

__('Required');$l_Required= "Required";$language_Required = (!empty($translations[$l_Required]) && $is_frontend) ? $translations[$l_Required] : ((empty(trim(__($l_Required,$domain)))) ? __($l_Required,$domainDefault) : __($l_Required,$domain)); if(empty($translations[$l_Required])){$translations[$l_Required]='';}

__('Remove');$l_Remove = "Remove";$language_Remove = (!empty($translations[$l_Remove]) && $is_frontend) ? $translations[$l_Remove] : ((empty(trim(__($l_Remove,$domain)))) ? __($l_Remove,$domainDefault) : __($l_Remove,$domain)); if(empty($translations[$l_Remove])){$translations[$l_Remove]='';}

__('content blocked');$l_contentBlocked = "content blocked";$language_contentBlocked = (!empty($translations[$l_contentBlocked]) && $is_frontend) ? $translations[$l_contentBlocked] : ((empty(trim(__($l_contentBlocked,$domain)))) ? __($l_contentBlocked,$domainDefault) : __($l_contentBlocked,$domain)); if(empty($translations[$l_contentBlocked])){$translations[$l_contentBlocked]='';}

__('To view the content you must agree privacy policy');$l_ToViewTheContentYouMustAgreePrivacyPolicy = "To view the content you must agree privacy policy";$language_ToViewTheContentYouMustAgreePrivacyPolicy = (!empty($translations[$l_ToViewTheContentYouMustAgreePrivacyPolicy]) && $is_frontend) ? $translations[$l_ToViewTheContentYouMustAgreePrivacyPolicy] : ((empty(trim(__($l_ToViewTheContentYouMustAgreePrivacyPolicy,$domain)))) ? __($l_ToViewTheContentYouMustAgreePrivacyPolicy,$domainDefault) : __($l_ToViewTheContentYouMustAgreePrivacyPolicy,$domain)); if(empty($translations[$l_ToViewTheContentYouMustAgreePrivacyPolicy])){$translations[$l_ToViewTheContentYouMustAgreePrivacyPolicy]='';}

__('I Agree');$l_IAgree = "I Agree";$language_IAgree = (!empty($translations[$l_IAgree]) && $is_frontend) ? $translations[$l_IAgree] : ((empty(trim(__($l_IAgree,$domain)))) ? __($l_IAgree,$domainDefault) : __($l_IAgree,$domain)); if(empty($translations[$l_IAgree])){$translations[$l_IAgree]='';}

__('By agreeing all content on this page gets unblocked during current session');$l_ByAgreeingAllContentUnblocked = "By agreeing all content on this page gets unblocked during current session";$language_ByAgreeingAllContentUnblocked = (!empty($translations[$l_ByAgreeingAllContentUnblocked]) && $is_frontend) ? $translations[$l_ByAgreeingAllContentUnblocked] : ((empty(trim(__($l_ByAgreeingAllContentUnblocked,$domain)))) ? __($l_ByAgreeingAllContentUnblocked,$domainDefault) : __($l_ByAgreeingAllContentUnblocked,$domain)); if(empty($translations[$l_ByAgreeingAllContentUnblocked])){$translations[$l_ByAgreeingAllContentUnblocked]='';}

__('content');$l_content = "content";$language_content = (!empty($translations[$l_content]) && $is_frontend) ? $translations[$l_content] : ((empty(trim(__($l_content,$domain)))) ? __($l_content,$domainDefault) : __($l_content,$domain)); if(empty($translations[$l_content])){$translations[$l_content]='';}

__('Until current tab gets closed');$l_UntilCurrentTabGetsClosed = "Until current tab gets closed";$language_UntilCurrentTabGetsClosed = (!empty($translations[$l_UntilCurrentTabGetsClosed]) && $is_frontend) ? $translations[$l_UntilCurrentTabGetsClosed] : ((empty(trim(__($l_UntilCurrentTabGetsClosed,$domain)))) ? __($l_UntilCurrentTabGetsClosed,$domainDefault) : __($l_UntilCurrentTabGetsClosed,$domain)); if(empty($translations[$l_UntilCurrentTabGetsClosed])){$translations[$l_UntilCurrentTabGetsClosed]='';}

__('Back to gallery');$l_BackToGallery = "Back to gallery";$language_BackToGallery = (!empty($translations[$l_BackToGallery]) && $is_frontend) ? $translations[$l_BackToGallery] : ((empty(trim(__($l_BackToGallery,$domain)))) ? __($l_BackToGallery,$domainDefault) : __($l_BackToGallery,$domain)); if(empty($translations[$l_BackToGallery])){$translations[$l_BackToGallery]='';}

__('Back to galleries');$l_BackToGalleries = "Back to galleries";$language_BackToGalleries = (!empty($translations[$l_BackToGalleries]) && $is_frontend) ? $translations[$l_BackToGalleries] : ((empty(trim(__($l_BackToGalleries,$domain)))) ? __($l_BackToGalleries,$domainDefault) : __($l_BackToGalleries,$domain)); if(empty($translations[$l_BackToGalleries])){$translations[$l_BackToGalleries]='';}

__('No gallery entries');$l_NoGalleryEntries = "No gallery entries";$language_NoGalleryEntries = (!empty($translations[$l_NoGalleryEntries]) && $is_frontend) ? $translations[$l_NoGalleryEntries] : ((empty(trim(__($l_NoGalleryEntries,$domain)))) ? __($l_NoGalleryEntries,$domainDefault) : __($l_NoGalleryEntries,$domain)); if(empty($translations[$l_NoGalleryEntries])){$translations[$l_NoGalleryEntries]='';}

__('Resend confirmation email');$l_ResendConfirmationEmail = "Resend confirmation email";$language_ResendConfirmationEmail = (!empty($translations[$l_ResendConfirmationEmail]) && $is_frontend) ? $translations[$l_ResendConfirmationEmail] : ((empty(trim(__($l_ResendConfirmationEmail,$domain)))) ? __($l_ResendConfirmationEmail,$domainDefault) : __($l_ResendConfirmationEmail,$domain)); if(empty($translations[$l_ResendConfirmationEmail])){$translations[$l_ResendConfirmationEmail]='';}

__('Confirmation link expired');$l_ConfirmationLinkExpired = 'Confirmation link expired';$language_ConfirmationLinkExpired = (!empty($translations[$l_ConfirmationLinkExpired]) && $is_frontend) ? $translations[$l_ConfirmationLinkExpired] : ((empty(trim(__($l_ConfirmationLinkExpired,$domain)))) ? __($l_ConfirmationLinkExpired,$domainDefault) : __($l_ConfirmationLinkExpired,$domain)); if(empty($translations[$l_ConfirmationLinkExpired])){$translations[$l_ConfirmationLinkExpired]='';}

__('E-mail sent');$l_EmailSent = "E-mail sent";$language_EmailSent = (!empty($translations[$l_EmailSent]) && $is_frontend) ? $translations[$l_EmailSent] : ((empty(trim(__($l_EmailSent,$domain)))) ? __($l_EmailSent,$domainDefault) : __($l_EmailSent,$domain)); if(empty($translations[$l_EmailSent])){$translations[$l_EmailSent]='';}

__('Activation key not found');$l_ActivationKeyNotFound = "Activation key not found";$language_ActivationKeyNotFound = (!empty($translations[$l_ActivationKeyNotFound]) && $is_frontend) ? $translations[$l_ActivationKeyNotFound] : ((empty(trim(__($l_ActivationKeyNotFound,$domain)))) ? __($l_ActivationKeyNotFound,$domainDefault) : __($l_ActivationKeyNotFound,$domain)); if(empty($translations[$l_ActivationKeyNotFound])){$translations[$l_ActivationKeyNotFound]='';}

__('A PIN has been sent to your email');$l_PinSentToYourEmail = "A PIN has been sent to your email";$language_PinSentToYourEmail = (!empty($translations[$l_PinSentToYourEmail]) && $is_frontend) ? $translations[$l_PinSentToYourEmail] : ((empty(trim(__($l_PinSentToYourEmail,$domain)))) ? __($l_PinSentToYourEmail,$domainDefault) : __($l_PinSentToYourEmail,$domain)); if(empty($translations[$l_PinSentToYourEmail])){$translations[$l_PinSentToYourEmail]='';}

__('Enter PIN');$l_EnterPin = "Enter PIN";$language_EnterPin = (!empty($translations[$l_EnterPin]) && $is_frontend) ? $translations[$l_EnterPin] : ((empty(trim(__($l_EnterPin,$domain)))) ? __($l_EnterPin,$domainDefault) : __($l_EnterPin,$domain)); if(empty($translations[$l_EnterPin])){$translations[$l_EnterPin]='';}

__('Send PIN');$l_SendPin = "Send PIN";$language_SendPin = (!empty($translations[$l_SendPin]) && $is_frontend) ? $translations[$l_SendPin] : ((empty(trim(__($l_SendPin,$domain)))) ? __($l_SendPin,$domainDefault) : __($l_SendPin,$domain)); if(empty($translations[$l_SendPin])){$translations[$l_SendPin]='';}

__('Confirm PIN');$l_ConfirmPin = "Confirm PIN";$language_ConfirmPin = (!empty($translations[$l_ConfirmPin]) && $is_frontend) ? $translations[$l_ConfirmPin] : ((empty(trim(__($l_ConfirmPin,$domain)))) ? __($l_ConfirmPin,$domainDefault) : __($l_ConfirmPin,$domain)); if(empty($translations[$l_ConfirmPin])){$translations[$l_ConfirmPin]='';}

__('Send new PIN');$l_SendNewPin = "Send new PIN";$language_SendNewPin = (!empty($translations[$l_SendNewPin]) && $is_frontend) ? $translations[$l_SendNewPin] : ((empty(trim(__($l_SendNewPin,$domain)))) ? __($l_SendNewPin,$domainDefault) : __($l_SendNewPin,$domain)); if(empty($translations[$l_SendNewPin])){$translations[$l_SendNewPin]='';}

__('Incorrect PIN. Please try again.');$l_IncorrectPinPleaseTryAgain = "Incorrect PIN. Please try again.";$language_IncorrectPinPleaseTryAgain = (!empty($translations[$l_IncorrectPinPleaseTryAgain]) && $is_frontend) ? $translations[$l_IncorrectPinPleaseTryAgain] : ((empty(trim(__($l_IncorrectPinPleaseTryAgain,$domain)))) ? __($l_IncorrectPinPleaseTryAgain,$domainDefault) : __($l_IncorrectPinPleaseTryAgain,$domain)); if(empty($translations[$l_IncorrectPinPleaseTryAgain])){$translations[$l_IncorrectPinPleaseTryAgain]='';}

__('PIN has expired. Please request a new one.');$l_PinExpiredRequestNewOne = "PIN has expired. Please request a new one.";$language_PinExpiredRequestNewOne = (!empty($translations[$l_PinExpiredRequestNewOne]) && $is_frontend) ? $translations[$l_PinExpiredRequestNewOne] : ((empty(trim(__($l_PinExpiredRequestNewOne,$domain)))) ? __($l_PinExpiredRequestNewOne,$domainDefault) : __($l_PinExpiredRequestNewOne,$domain)); if(empty($translations[$l_PinExpiredRequestNewOne])){$translations[$l_PinExpiredRequestNewOne]='';}

__('Only jury members can vote');$l_OnlyJuryMembersCanVote = "Only jury members can vote";$language_OnlyJuryMembersCanVote = (!empty($translations[$l_OnlyJuryMembersCanVote]) && $is_frontend) ? $translations[$l_OnlyJuryMembersCanVote] : ((empty(trim(__($l_OnlyJuryMembersCanVote,$domain)))) ? __($l_OnlyJuryMembersCanVote,$domainDefault) : __($l_OnlyJuryMembersCanVote,$domain)); if(empty($translations[$l_OnlyJuryMembersCanVote])){$translations[$l_OnlyJuryMembersCanVote]='';}

?>