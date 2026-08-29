<?php

if(!defined('ABSPATH')){exit;}

$is_frontend = false;
include(__DIR__.'/../../../../check-language-paid-actions.php');

echo "<div class='cg_view_container cg_view_container_translations'>";
echo "<div class='cg_view_options_rows_container'><p class='cg_view_options_rows_container_title' style='line-height:25px;'><strong>Translations here will replace language file translations for Paid Actions.</strong><br>HTML tags can not be used in translations. Keep placeholders such as {count}, {type}, {provider}, {seconds}, {amount} and {currency} unchanged.</p></div>";

$paidActionsGroups = array(
	'Credit bar and access messages'=>array('Uploads','Votes','Comments','UploadsLower','VotesLower','CommentsLower','PrivatePaymentPreview','SandboxCheckoutTest','YourGalleryCredits','YourUploadCredits','UploadCreditUsage','PreviewBarDescription','SandboxBarDescription','LiveBarDescription','AddTestCredits','SandboxCheckout','GetCredits','BuyCreditsInSandbox','BuyGalleryCredits','ResetTestActions','UsedHere','GetCreditsSummary','CreditsReadyGallery','SandboxLinkUnavailable','SandboxLinkUnavailableDescription','CreditsTemporarilyUnavailable','PleaseTryAgainMoment','PurchasingTemporarilyUnavailable'),
	'Credit requirements'=>array('CreditRequired','TestCreditsUsed','UploadCreditRequired','VotingCreditRequired','CommentCreditRequired','UploadCreditRequiredDescription','VotingCreditRequiredDescription','CommentCreditRequiredDescription','AllTestUploadCreditsUsed','AllTestVotingCreditsUsed','AllTestCommentCreditsUsed','AllTestUploadCreditsUsedDescription','AllTestVotingCreditsUsedDescription','AllTestCommentCreditsUsedDescription','AddMoreTestCredits','BuyCredits','UploadCreditsUpper','UploadAccessUpper','CheckingUploadCredits','CheckingUploadCreditsDescription','SignInToUpload','SignInToUploadDescription','SignInToContinue','UploadCreditsUnavailable','UploadCreditsUnavailableDescription','MoreUploadCreditsRequired','MoreUploadCreditsRequiredDescription','CommentCreditsStatus','CheckingCommentCredits','CheckingCommentCreditsDescription','CommentCreditsUnavailable','CommentCreditsUnavailableDescription','NotNow','TryAgain'),
	'Credit selection and checkout'=>array('UploadCredits','VotingCredits','CommentCredits','UploadCredit','VotingCredit','CommentCredit','CreditsForTypeGallery','Credit','Credits','Quantity','Each','TotalPrice','CurrentTestCredits','CurrentCredits','BalanceAvailable','BalanceSelected','BalanceSelectedTest','ChooseCreditToContinue','SecureTestCheckout','ChooseFreeTestCredits','TestCheckoutDescription','PrivatePreview','VisibleOnlyToYou','NoRealCharge','PaymentDataNeverRequested','PersistentCredits','AvailableAfterReload','ChooseTestPaymentMethod','TestPaymentMethodDescription','SimulateSecurePayment','TestModeNotCharged','RealSandboxCheckout','SecureCheckout','ChoosePersistentTestCredits','ChooseGalleryCredits','LiveCheckoutDescription'),
	'Private preview and reset'=>array('PrivatePaymentPreviewUpper','ResetSavedTestActions','ResetDescription','TestActionsInGallery','TestUpload','TestUploads','TestVote','TestVotes','TestComment','TestComments','SavedTestActionRemoved','SavedTestActionsRemoved','TestCreditRestored','TestCreditsRestored','ResetWarning','Cancel','AddedTestCredits','TestPaymentApproved','TestCreditsReady','TestCreditAdded','TestCreditsAdded','TestSuccessNote','ContinueToGallery','ApprovingTestPayment','TestPaymentFailed'),
	'Sign-in and PIN checkout'=>array('SandboxCheckoutUpper','SignInSandboxCheckout','SignInSandboxDescription','SignInBuyCredits','SignInBuyDescription','SignInBuyDescriptionEmailOnly','Or','EmailAddress','EmailMePin','SixDigitPin','PinDigit','ConfirmPin','SendNewPin','SendNewPinCountdown','PinConfirmedPreparing','SignInSuccessfulReloading','SignInFormUnavailable','GoogleSignInUnavailable','PinConfirmedUpper','SignInSuccessful','ContinueCreditCheckout','WaitBeforeAnotherPin','Second','Seconds','TooManyPins','PinSendFailed','ValidEmailRequired','PinInvalidExpired','TooManyPinAttempts','PinIncorrect','EnterSixPinDigits','NewPinSendFailed','PinRequestUnavailable'),
	'Payment return and errors'=>array('SandboxPayment','SecurePayment','FinalizingSandboxCredits','FinalizingCredits','FinalizingSandboxDescription','FinalizingDescription','FinalizingSandboxSecureDescription','FinalizingSecureDescription','DoNotStartPayment','TryFinalizingAgain','ReturnToGallery','CreditsReady','CreditsAddedReturning','PaymentNeedsAttention','CouldNotFinishCredits','PaymentFinalizeFailed','CheckoutCouldNotBeStarted','Processing','ProcessingPayment','PaymentRedirectNotice','BrowserSavedCheckoutUnavailable','SavedCheckoutOpenFailed','SavedCheckoutBlocked','SavedCheckoutReadFailed','TestPaymentApprovedCreatingOrder','PaymentApprovedCreatingOrder','SavedCheckoutNotFound','PaymentProcessingRetry','CheckoutChanged','GalleryUnavailable','GalleryUnsupported','PreviewExpired','PreviewEnded','ActivateActionBeforeTestCredits','TestCreditsUpdating','DemoCreditsAddFailed','PreviewActionsResetting','DemoCreditsReserveFailed','NotEnoughDemoCredits','ActionPurchaseUnavailable','SignInPurchaseCredits','InvalidCreditReservation','PaidActionUnavailable','ActionDoesNotRequireCredits','CreditsUpdating','CreditsReserveFailed','NotEnoughCredits','SandboxLinkInvalid','PreviewActionRunning','PreviewUploadsRemoveFailed','PreviewResetComplete','SixDigitPinSent','NewSixDigitPinSent','PreviewBusy','PreviewResetFailed','PreviewResetSuccess','PreviewGrantFailed','Resetting','CodeCouldNotBeSent','CodeInvalid','GalleryNumber','PurchaseCreditContinue','Loading')
);

foreach($paidActionsGroups as $groupTitle=>$keys){
	echo "<div class='cg_view_options_rows_container'><p class='cg_view_options_rows_container_title'>".esc_html($groupTitle)."</p>";
	foreach($keys as $key){
		if(empty($paidActionsDefinitions[$key])){continue;}
		$default = $paidActionsDefinitions[$key]['default'];
		$language = cg_paid_actions_t($key,array(),false);
		$value = isset($translations[$default]) ? $translations[$default] : '';
		echo "<div class='cg_view_options_row'><div class='cg_view_option cg_view_option_full_width cg_border_top_none'><div class='cg_view_option_title'><p>".esc_html($language)."</p></div><div class='cg_view_option_input'><input type='text' name='translations[paidActions][".esc_attr($default)."]' maxlength='1000' value='".esc_attr($value)."'></div></div></div>";
	}
	echo '</div>';
}

echo '</div>';
