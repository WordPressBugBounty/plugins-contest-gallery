<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_paid_actions_language_definitions')){
	function cg_paid_actions_language_definitions(){
		return array(
			'Uploads'=>array('default'=>'Uploads','translated'=>__('Uploads','contest-gallery')),
			'Votes'=>array('default'=>'Votes','translated'=>__('Votes','contest-gallery')),
			'Comments'=>array('default'=>'Comments','translated'=>__('Comments','contest-gallery')),
			'UploadsLower'=>array('default'=>'uploads','translated'=>__('uploads','contest-gallery')),
			'VotesLower'=>array('default'=>'votes','translated'=>__('votes','contest-gallery')),
			'CommentsLower'=>array('default'=>'comments','translated'=>__('comments','contest-gallery')),
			'UploadCredits'=>array('default'=>'Upload credits','translated'=>__('Upload credits','contest-gallery')),
			'VotingCredits'=>array('default'=>'Voting credits','translated'=>__('Voting credits','contest-gallery')),
			'CommentCredits'=>array('default'=>'Comment credits','translated'=>__('Comment credits','contest-gallery')),
			'UploadCredit'=>array('default'=>'upload credit','translated'=>__('upload credit','contest-gallery')),
			'VotingCredit'=>array('default'=>'voting credit','translated'=>__('voting credit','contest-gallery')),
			'CommentCredit'=>array('default'=>'comment credit','translated'=>__('comment credit','contest-gallery')),
			'CreditsForTypeGallery'=>array('default'=>'Credits for {type} in gallery {number}','translated'=>__('Credits for {type} in gallery {number}','contest-gallery')),
			'Credit'=>array('default'=>'credit','translated'=>__('credit','contest-gallery')),
			'Credits'=>array('default'=>'credits','translated'=>__('credits','contest-gallery')),
			'Quantity'=>array('default'=>'Quantity','translated'=>__('Quantity','contest-gallery')),
			'Each'=>array('default'=>'each','translated'=>__('each','contest-gallery')),
			'TotalPrice'=>array('default'=>'Total price','translated'=>__('Total price','contest-gallery')),
			'Loading'=>array('default'=>'Loading','translated'=>__('Loading','contest-gallery')),
			'Or'=>array('default'=>'or','translated'=>__('or','contest-gallery')),
			'Cancel'=>array('default'=>'Cancel','translated'=>__('Cancel','contest-gallery')),
			'NotNow'=>array('default'=>'Not now','translated'=>__('Not now','contest-gallery')),
			'TryAgain'=>array('default'=>'Try again','translated'=>__('Try again','contest-gallery')),
			'ContinueToGallery'=>array('default'=>'Continue to gallery','translated'=>__('Continue to gallery','contest-gallery')),
			'ReturnToGallery'=>array('default'=>'Return to gallery','translated'=>__('Return to gallery','contest-gallery')),
			'PrivatePaymentPreview'=>array('default'=>'Private payment preview','translated'=>__('Private payment preview','contest-gallery')),
			'SandboxCheckoutTest'=>array('default'=>'Sandbox checkout test','translated'=>__('Sandbox checkout test','contest-gallery')),
			'YourGalleryCredits'=>array('default'=>'Your gallery credits','translated'=>__('Your gallery credits','contest-gallery')),
			'YourUploadCredits'=>array('default'=>'Your upload credits','translated'=>__('Your upload credits','contest-gallery')),
			'UploadCreditUsage'=>array('default'=>'One credit is used for each submitted entry.','translated'=>__('One credit is used for each submitted entry.','contest-gallery')),
			'PreviewBarDescription'=>array('default'=>'Real test actions are saved and visible until reset','translated'=>__('Real test actions are saved and visible until reset','contest-gallery')),
			'SandboxBarDescription'=>array('default'=>'Real sandbox orders grant persistent gallery credits','translated'=>__('Real sandbox orders grant persistent gallery credits','contest-gallery')),
			'LiveBarDescription'=>array('default'=>'Buy only what you want to use','translated'=>__('Buy only what you want to use','contest-gallery')),
			'AddTestCredits'=>array('default'=>'Add test credits','translated'=>__('Add test credits','contest-gallery')),
			'SandboxCheckout'=>array('default'=>'Sandbox checkout','translated'=>__('Sandbox checkout','contest-gallery')),
			'GetCredits'=>array('default'=>'Get credits','translated'=>__('Get credits','contest-gallery')),
			'BuyCreditsInSandbox'=>array('default'=>'Buy credits in sandbox','translated'=>__('Buy credits in sandbox','contest-gallery')),
			'BuyGalleryCredits'=>array('default'=>'Buy gallery credits','translated'=>__('Buy gallery credits','contest-gallery')),
			'ResetTestActions'=>array('default'=>'Reset test actions','translated'=>__('Reset test actions','contest-gallery')),
			'UsedHere'=>array('default'=>'{count} used here','translated'=>__('{count} used here','contest-gallery')),
			'GetCreditsSummary'=>array('default'=>'Get credits: {credits}','translated'=>__('Get credits: {credits}','contest-gallery')),
			'CreditsReadyGallery'=>array('default'=>'Your credits are ready. You can continue in the gallery.','translated'=>__('Your credits are ready. You can continue in the gallery.','contest-gallery')),
			'SandboxLinkUnavailable'=>array('default'=>'Sandbox test link unavailable','translated'=>__('Sandbox test link unavailable','contest-gallery')),
			'SandboxLinkUnavailableDescription'=>array('default'=>'This link has expired, is invalid, or belongs to another test user. Create a new sandbox test link in the gallery settings.','translated'=>__('This link has expired, is invalid, or belongs to another test user. Create a new sandbox test link in the gallery settings.','contest-gallery')),
			'CreditsTemporarilyUnavailable'=>array('default'=>'Credits temporarily unavailable','translated'=>__('Credits temporarily unavailable','contest-gallery')),
			'PleaseTryAgainMoment'=>array('default'=>'Please try again in a moment','translated'=>__('Please try again in a moment','contest-gallery')),
			'PurchasingTemporarilyUnavailable'=>array('default'=>'Purchasing credits is temporarily unavailable. Please contact the gallery administrator.','translated'=>__('Purchasing credits is temporarily unavailable. Please contact the gallery administrator.','contest-gallery')),
			'CreditRequired'=>array('default'=>'CREDIT REQUIRED','translated'=>__('CREDIT REQUIRED','contest-gallery')),
			'TestCreditsUsed'=>array('default'=>'TEST CREDITS USED','translated'=>__('TEST CREDITS USED','contest-gallery')),
			'UploadCreditRequired'=>array('default'=>'Upload credit required','translated'=>__('Upload credit required','contest-gallery')),
			'VotingCreditRequired'=>array('default'=>'Voting credit required','translated'=>__('Voting credit required','contest-gallery')),
			'CommentCreditRequired'=>array('default'=>'Comment credit required','translated'=>__('Comment credit required','contest-gallery')),
			'UploadCreditRequiredDescription'=>array('default'=>'Buy upload credits to upload an entry to this gallery.','translated'=>__('Buy upload credits to upload an entry to this gallery.','contest-gallery')),
			'VotingCreditRequiredDescription'=>array('default'=>'Buy voting credits to vote in this gallery.','translated'=>__('Buy voting credits to vote in this gallery.','contest-gallery')),
			'CommentCreditRequiredDescription'=>array('default'=>'Buy comment credits to comment in this gallery.','translated'=>__('Buy comment credits to comment in this gallery.','contest-gallery')),
			'AllTestUploadCreditsUsed'=>array('default'=>'All test upload credits used','translated'=>__('All test upload credits used','contest-gallery')),
			'AllTestVotingCreditsUsed'=>array('default'=>'All test voting credits used','translated'=>__('All test voting credits used','contest-gallery')),
			'AllTestCommentCreditsUsed'=>array('default'=>'All test comment credits used','translated'=>__('All test comment credits used','contest-gallery')),
			'AllTestUploadCreditsUsedDescription'=>array('default'=>'You have used all test upload credits for this gallery. Add more to keep testing.','translated'=>__('You have used all test upload credits for this gallery. Add more to keep testing.','contest-gallery')),
			'AllTestVotingCreditsUsedDescription'=>array('default'=>'You have used all test voting credits for this gallery. Add more to keep testing.','translated'=>__('You have used all test voting credits for this gallery. Add more to keep testing.','contest-gallery')),
			'AllTestCommentCreditsUsedDescription'=>array('default'=>'You have used all test comment credits for this gallery. Add more to keep testing.','translated'=>__('You have used all test comment credits for this gallery. Add more to keep testing.','contest-gallery')),
			'AddMoreTestCredits'=>array('default'=>'Add more test credits','translated'=>__('Add more test credits','contest-gallery')),
			'BuyCredits'=>array('default'=>'Buy credits','translated'=>__('Buy credits','contest-gallery')),
			'CommentCreditsStatus'=>array('default'=>'COMMENT CREDITS','translated'=>__('COMMENT CREDITS','contest-gallery')),
			'CheckingCommentCredits'=>array('default'=>'Checking comment credits','translated'=>__('Checking comment credits','contest-gallery')),
			'CheckingCommentCreditsDescription'=>array('default'=>'Please wait while your comment credits are checked.','translated'=>__('Please wait while your comment credits are checked.','contest-gallery')),
			'CommentCreditsUnavailable'=>array('default'=>'Comment credits temporarily unavailable','translated'=>__('Comment credits temporarily unavailable','contest-gallery')),
			'CommentCreditsUnavailableDescription'=>array('default'=>'Your comment credits could not be checked. Please try again.','translated'=>__('Your comment credits could not be checked. Please try again.','contest-gallery')),
			'UploadCreditsUpper'=>array('default'=>'UPLOAD CREDITS','translated'=>__('UPLOAD CREDITS','contest-gallery')),
			'UploadAccessUpper'=>array('default'=>'UPLOAD ACCESS','translated'=>__('UPLOAD ACCESS','contest-gallery')),
			'CheckingUploadCredits'=>array('default'=>'Checking upload credits','translated'=>__('Checking upload credits','contest-gallery')),
			'CheckingUploadCreditsDescription'=>array('default'=>'Please wait while your upload credits are checked.','translated'=>__('Please wait while your upload credits are checked.','contest-gallery')),
			'SignInToUpload'=>array('default'=>'Sign in to upload','translated'=>__('Sign in to upload','contest-gallery')),
			'SignInToUploadDescription'=>array('default'=>'Sign in to buy upload credits and submit an entry to this gallery.','translated'=>__('Sign in to buy upload credits and submit an entry to this gallery.','contest-gallery')),
			'SignInToContinue'=>array('default'=>'Sign in to continue','translated'=>__('Sign in to continue','contest-gallery')),
			'UploadCreditsUnavailable'=>array('default'=>'Upload credits temporarily unavailable','translated'=>__('Upload credits temporarily unavailable','contest-gallery')),
			'UploadCreditsUnavailableDescription'=>array('default'=>'Your upload credits could not be checked. Please try again.','translated'=>__('Your upload credits could not be checked. Please try again.','contest-gallery')),
			'MoreUploadCreditsRequired'=>array('default'=>'More upload credits required','translated'=>__('More upload credits required','contest-gallery')),
			'MoreUploadCreditsRequiredDescription'=>array('default'=>'This selection needs {required} upload credits. You currently have {available}.','translated'=>__('This selection needs {required} upload credits. You currently have {available}.','contest-gallery')),
			'CurrentTestCredits'=>array('default'=>'YOUR CURRENT TEST CREDITS','translated'=>__('YOUR CURRENT TEST CREDITS','contest-gallery')),
			'CurrentCredits'=>array('default'=>'YOUR CURRENT CREDITS','translated'=>__('YOUR CURRENT CREDITS','contest-gallery')),
			'BalanceAvailable'=>array('default'=>'{count} {type} available','translated'=>__('{count} {type} available','contest-gallery')),
			'BalanceSelected'=>array('default'=>'{count} {type} available, {selected} selected for checkout','translated'=>__('{count} {type} available, {selected} selected for checkout','contest-gallery')),
			'BalanceSelectedTest'=>array('default'=>'{count} {type} available, {selected} selected for test checkout','translated'=>__('{count} {type} available, {selected} selected for test checkout','contest-gallery')),
			'PrivatePaymentPreviewUpper'=>array('default'=>'PRIVATE PAYMENT PREVIEW','translated'=>__('PRIVATE PAYMENT PREVIEW','contest-gallery')),
			'ResetSavedTestActions'=>array('default'=>'Reset saved test actions?','translated'=>__('Reset saved test actions?','contest-gallery')),
			'ResetDescription'=>array('default'=>'This removes every paid preview action in this gallery, including tests from other preview tabs and administrators.','translated'=>__('This removes every paid preview action in this gallery, including tests from other preview tabs and administrators.','contest-gallery')),
			'TestActionsInGallery'=>array('default'=>'TEST ACTIONS IN THIS GALLERY','translated'=>__('TEST ACTIONS IN THIS GALLERY','contest-gallery')),
			'TestUpload'=>array('default'=>'Test upload','translated'=>__('Test upload','contest-gallery')),
			'TestUploads'=>array('default'=>'Test uploads','translated'=>__('Test uploads','contest-gallery')),
			'TestVote'=>array('default'=>'Test vote','translated'=>__('Test vote','contest-gallery')),
			'TestVotes'=>array('default'=>'Test votes','translated'=>__('Test votes','contest-gallery')),
			'TestComment'=>array('default'=>'Test comment','translated'=>__('Test comment','contest-gallery')),
			'TestComments'=>array('default'=>'Test comments','translated'=>__('Test comments','contest-gallery')),
			'SavedTestActionRemoved'=>array('default'=>'saved test action will be removed','translated'=>__('saved test action will be removed','contest-gallery')),
			'SavedTestActionsRemoved'=>array('default'=>'saved test actions will be removed','translated'=>__('saved test actions will be removed','contest-gallery')),
			'TestCreditRestored'=>array('default'=>'test credit will be restored to its original credit type','translated'=>__('test credit will be restored to its original credit type','contest-gallery')),
			'TestCreditsRestored'=>array('default'=>'test credits will be restored to their original credit types','translated'=>__('test credits will be restored to their original credit types','contest-gallery')),
			'ResetWarning'=>array('default'=>'Preview uploads and their files are deleted completely. Everything attached to those entries is removed as well. Test credits used in this gallery are restored.','translated'=>__('Preview uploads and their files are deleted completely. Everything attached to those entries is removed as well. Test credits used in this gallery are restored.','contest-gallery')),
			'SecureTestCheckout'=>array('default'=>'SECURE TEST CHECKOUT','translated'=>__('SECURE TEST CHECKOUT','contest-gallery')),
			'ChooseFreeTestCredits'=>array('default'=>'Choose your free test credits','translated'=>__('Choose your free test credits','contest-gallery')),
			'TestCheckoutDescription'=>array('default'=>'Set the quantity directly in each checkout item. No money or payment details are used.','translated'=>__('Set the quantity directly in each checkout item. No money or payment details are used.','contest-gallery')),
			'PrivatePreview'=>array('default'=>'PRIVATE PREVIEW','translated'=>__('PRIVATE PREVIEW','contest-gallery')),
			'VisibleOnlyToYou'=>array('default'=>'Visible only to you','translated'=>__('Visible only to you','contest-gallery')),
			'NoRealCharge'=>array('default'=>'No real charge','translated'=>__('No real charge','contest-gallery')),
			'PaymentDataNeverRequested'=>array('default'=>'Payment data is never requested','translated'=>__('Payment data is never requested','contest-gallery')),
			'PersistentCredits'=>array('default'=>'Persistent credits','translated'=>__('Persistent credits','contest-gallery')),
			'AvailableAfterReload'=>array('default'=>'Available after reload','translated'=>__('Available after reload','contest-gallery')),
			'ChooseCreditToContinue'=>array('default'=>'Choose a quantity above to continue.','translated'=>__('Choose a quantity above to continue.','contest-gallery')),
			'ChooseTestPaymentMethod'=>array('default'=>'Choose a test payment method','translated'=>__('Choose a test payment method','contest-gallery')),
			'TestPaymentMethodDescription'=>array('default'=>'Clicking a method simulates approval and adds the selected credits.','translated'=>__('Clicking a method simulates approval and adds the selected credits.','contest-gallery')),
			'SimulateSecurePayment'=>array('default'=>'Simulate secure payment','translated'=>__('Simulate secure payment','contest-gallery')),
			'TestModeNotCharged'=>array('default'=>'TEST MODE · {amount} {currency} will not be charged','translated'=>__('TEST MODE · {amount} {currency} will not be charged','contest-gallery')),
			'AddedTestCredits'=>array('default'=>'Added test credits','translated'=>__('Added test credits','contest-gallery')),
			'TestPaymentApproved'=>array('default'=>'TEST PAYMENT APPROVED','translated'=>__('TEST PAYMENT APPROVED','contest-gallery')),
			'TestCreditsReady'=>array('default'=>'Your test credits are ready','translated'=>__('Your test credits are ready','contest-gallery')),
			'TestCreditAdded'=>array('default'=>'{count} credit was added after the simulated {provider} approval.','translated'=>__('{count} credit was added after the simulated {provider} approval.','contest-gallery')),
			'TestCreditsAdded'=>array('default'=>'{count} credits were added after the simulated {provider} approval.','translated'=>__('{count} credits were added after the simulated {provider} approval.','contest-gallery')),
			'TestSuccessNote'=>array('default'=>'No real order or payment was created. The credits remain available after reloading this gallery.','translated'=>__('No real order or payment was created. The credits remain available after reloading this gallery.','contest-gallery')),
			'ApprovingTestPayment'=>array('default'=>'Approving secure {provider} test payment…','translated'=>__('Approving secure {provider} test payment…','contest-gallery')),
			'TestPaymentFailed'=>array('default'=>'The test payment could not be approved.','translated'=>__('The test payment could not be approved.','contest-gallery')),
			'RealSandboxCheckout'=>array('default'=>'REAL SANDBOX CHECKOUT','translated'=>__('REAL SANDBOX CHECKOUT','contest-gallery')),
			'SecureCheckout'=>array('default'=>'SECURE CHECKOUT','translated'=>__('SECURE CHECKOUT','contest-gallery')),
			'ChoosePersistentTestCredits'=>array('default'=>'Choose persistent test credits','translated'=>__('Choose persistent test credits','contest-gallery')),
			'ChooseGalleryCredits'=>array('default'=>'Choose your gallery credits','translated'=>__('Choose your gallery credits','contest-gallery')),
			'LiveCheckoutDescription'=>array('default'=>'Set the quantity directly in each checkout item. The total updates immediately.','translated'=>__('Set the quantity directly in each checkout item. The total updates immediately.','contest-gallery')),
			'SandboxCheckoutUpper'=>array('default'=>'SANDBOX CHECKOUT','translated'=>__('SANDBOX CHECKOUT','contest-gallery')),
			'SignInSandboxCheckout'=>array('default'=>'Sign in to test buying credits','translated'=>__('Sign in to test buying credits','contest-gallery')),
			'SignInSandboxDescription'=>array('default'=>'Sign in to continue the sandbox checkout. You can test buying credits and participating in this gallery without a real payment.','translated'=>__('Sign in to continue the sandbox checkout. You can test buying credits and participating in this gallery without a real payment.','contest-gallery')),
			'SignInBuyCredits'=>array('default'=>'Sign in to buy credits','translated'=>__('Sign in to buy credits','contest-gallery')),
			'SignInBuyDescription'=>array('default'=>'You need an account to buy credits and participate in this gallery. Continue with Google or receive a six-digit PIN by email.','translated'=>__('You need an account to buy credits and participate in this gallery. Continue with Google or receive a six-digit PIN by email.','contest-gallery')),
			'EmailAddress'=>array('default'=>'Email address','translated'=>__('Email address','contest-gallery')),
			'EmailMePin'=>array('default'=>'Email me a PIN','translated'=>__('Email me a PIN','contest-gallery')),
			'SixDigitPin'=>array('default'=>'Six-digit PIN','translated'=>__('Six-digit PIN','contest-gallery')),
			'PinDigit'=>array('default'=>'PIN digit {number}','translated'=>__('PIN digit {number}','contest-gallery')),
			'ConfirmPin'=>array('default'=>'Confirm PIN','translated'=>__('Confirm PIN','contest-gallery')),
			'SendNewPin'=>array('default'=>'Send a new PIN','translated'=>__('Send a new PIN','contest-gallery')),
			'SendNewPinCountdown'=>array('default'=>'Send a new PIN in {seconds}s','translated'=>__('Send a new PIN in {seconds}s','contest-gallery')),
			'PinConfirmedPreparing'=>array('default'=>'PIN confirmed. Preparing your checkout…','translated'=>__('PIN confirmed. Preparing your checkout…','contest-gallery')),
			'SignInSuccessfulReloading'=>array('default'=>'Sign-in successful. Reloading the gallery…','translated'=>__('Sign-in successful. Reloading the gallery…','contest-gallery')),
			'SignInFormUnavailable'=>array('default'=>'The sign-in form could not be loaded.','translated'=>__('The sign-in form could not be loaded.','contest-gallery')),
			'PinConfirmedUpper'=>array('default'=>'PIN CONFIRMED','translated'=>__('PIN CONFIRMED','contest-gallery')),
			'SignInSuccessful'=>array('default'=>'Sign-in successful','translated'=>__('Sign-in successful','contest-gallery')),
			'ContinueCreditCheckout'=>array('default'=>'Continue to buy credits','translated'=>__('Continue to buy credits','contest-gallery')),
			'WaitBeforeAnotherPin'=>array('default'=>'Please wait {seconds} {secondsLabel} before requesting another PIN.','translated'=>__('Please wait {seconds} {secondsLabel} before requesting another PIN.','contest-gallery')),
			'Second'=>array('default'=>'second','translated'=>__('second','contest-gallery')),
			'Seconds'=>array('default'=>'seconds','translated'=>__('seconds','contest-gallery')),
			'TooManyPins'=>array('default'=>'Too many PINs were requested. Please try again later.','translated'=>__('Too many PINs were requested. Please try again later.','contest-gallery')),
			'PinSendFailed'=>array('default'=>'The PIN could not be sent.','translated'=>__('The PIN could not be sent.','contest-gallery')),
			'ValidEmailRequired'=>array('default'=>'Enter a valid email address.','translated'=>__('Enter a valid email address.','contest-gallery')),
			'PinInvalidExpired'=>array('default'=>'The PIN is invalid or expired.','translated'=>__('The PIN is invalid or expired.','contest-gallery')),
			'TooManyPinAttempts'=>array('default'=>'Too many attempts. Request a new PIN.','translated'=>__('Too many attempts. Request a new PIN.','contest-gallery')),
			'PinIncorrect'=>array('default'=>'The PIN is incorrect.','translated'=>__('The PIN is incorrect.','contest-gallery')),
			'EnterSixPinDigits'=>array('default'=>'Enter all six PIN digits.','translated'=>__('Enter all six PIN digits.','contest-gallery')),
			'NewPinSendFailed'=>array('default'=>'A new PIN could not be sent.','translated'=>__('A new PIN could not be sent.','contest-gallery')),
			'PinRequestUnavailable'=>array('default'=>'The PIN request is no longer available.','translated'=>__('The PIN request is no longer available.','contest-gallery')),
			'SandboxPayment'=>array('default'=>'SANDBOX PAYMENT','translated'=>__('SANDBOX PAYMENT','contest-gallery')),
			'SecurePayment'=>array('default'=>'SECURE PAYMENT','translated'=>__('SECURE PAYMENT','contest-gallery')),
			'FinalizingSandboxCredits'=>array('default'=>'Finalizing your sandbox credits','translated'=>__('Finalizing your sandbox credits','contest-gallery')),
			'FinalizingCredits'=>array('default'=>'Finalizing your credits','translated'=>__('Finalizing your credits','contest-gallery')),
			'FinalizingSandboxDescription'=>array('default'=>'Your test payment was approved. We are adding your credits now. You will return to the gallery automatically.','translated'=>__('Your test payment was approved. We are adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'FinalizingDescription'=>array('default'=>'Your payment was approved. We are adding your credits now. You will return to the gallery automatically.','translated'=>__('Your payment was approved. We are adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'FinalizingSandboxSecureDescription'=>array('default'=>'Your test payment was approved. We are securely adding your credits now. You will return to the gallery automatically.','translated'=>__('Your test payment was approved. We are securely adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'FinalizingSecureDescription'=>array('default'=>'Your payment was approved. We are securely adding your credits now. You will return to the gallery automatically.','translated'=>__('Your payment was approved. We are securely adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'DoNotStartPayment'=>array('default'=>'Do not start another payment while this payment is being finalized.','translated'=>__('Do not start another payment while this payment is being finalized.','contest-gallery')),
			'TryFinalizingAgain'=>array('default'=>'Try finalizing again','translated'=>__('Try finalizing again','contest-gallery')),
			'CreditsReady'=>array('default'=>'Your credits are ready','translated'=>__('Your credits are ready','contest-gallery')),
			'CreditsAddedReturning'=>array('default'=>'Your credits were added successfully. Returning to the gallery now.','translated'=>__('Your credits were added successfully. Returning to the gallery now.','contest-gallery')),
			'PaymentNeedsAttention'=>array('default'=>'PAYMENT NEEDS ATTENTION','translated'=>__('PAYMENT NEEDS ATTENTION','contest-gallery')),
			'CouldNotFinishCredits'=>array('default'=>'We could not finish adding your credits','translated'=>__('We could not finish adding your credits','contest-gallery')),
			'PaymentFinalizeFailed'=>array('default'=>'The payment could not be finalized automatically. You can safely try finalizing this same payment again.','translated'=>__('The payment could not be finalized automatically. You can safely try finalizing this same payment again.','contest-gallery')),
			'CheckoutChanged'=>array('default'=>'Your credit selection changed. Please review it and try again.','translated'=>__('Your credit selection changed. Please review it and try again.','contest-gallery')),
			'GalleryUnavailable'=>array('default'=>'The requested gallery is not available.','translated'=>__('The requested gallery is not available.','contest-gallery')),
			'GalleryUnsupported'=>array('default'=>'Paid actions are not available for this gallery.','translated'=>__('Paid actions are not available for this gallery.','contest-gallery')),
			'PreviewExpired'=>array('default'=>'The private payment preview is invalid or expired.','translated'=>__('The private payment preview is invalid or expired.','contest-gallery')),
			'PreviewEnded'=>array('default'=>'This private preview session has ended. Open "Try it now" again from the gallery settings.','translated'=>__('This private preview session has ended. Open "Try it now" again from the gallery settings.','contest-gallery')),
			'ActivateActionBeforeTestCredits'=>array('default'=>'Activate pay for {type} before adding its test credits.','translated'=>__('Activate pay for {type} before adding its test credits.','contest-gallery')),
			'TestCreditsUpdating'=>array('default'=>'Test credits are being updated. Please try again.','translated'=>__('Test credits are being updated. Please try again.','contest-gallery')),
			'DemoCreditsAddFailed'=>array('default'=>'Demo credits could not be added.','translated'=>__('Demo credits could not be added.','contest-gallery')),
			'PreviewActionsResetting'=>array('default'=>'Preview actions are being reset. Please try again.','translated'=>__('Preview actions are being reset. Please try again.','contest-gallery')),
			'DemoCreditsReserveFailed'=>array('default'=>'Demo credits could not be reserved.','translated'=>__('Demo credits could not be reserved.','contest-gallery')),
			'NotEnoughDemoCredits'=>array('default'=>'Not enough demo credits are available.','translated'=>__('Not enough demo credits are available.','contest-gallery')),
			'ActionPurchaseUnavailable'=>array('default'=>'Purchasing credits for this action is temporarily unavailable.','translated'=>__('Purchasing credits for this action is temporarily unavailable.','contest-gallery')),
			'SignInPurchaseCredits'=>array('default'=>'Sign in to purchase and use gallery credits.','translated'=>__('Sign in to purchase and use gallery credits.','contest-gallery')),
			'InvalidCreditReservation'=>array('default'=>'Invalid credit reservation.','translated'=>__('Invalid credit reservation.','contest-gallery')),
			'PaidActionUnavailable'=>array('default'=>'This paid action is temporarily unavailable.','translated'=>__('This paid action is temporarily unavailable.','contest-gallery')),
			'ActionDoesNotRequireCredits'=>array('default'=>'This action does not require paid credits.','translated'=>__('This action does not require paid credits.','contest-gallery')),
			'CreditsUpdating'=>array('default'=>'Credits are being updated. Please try again.','translated'=>__('Credits are being updated. Please try again.','contest-gallery')),
			'CreditsReserveFailed'=>array('default'=>'Credits could not be reserved.','translated'=>__('Credits could not be reserved.','contest-gallery')),
			'NotEnoughCredits'=>array('default'=>'Not enough credits are available.','translated'=>__('Not enough credits are available.','contest-gallery')),
			'SandboxLinkInvalid'=>array('default'=>'This sandbox test link is invalid, expired or already belongs to another test user.','translated'=>__('This sandbox test link is invalid, expired or already belongs to another test user.','contest-gallery')),
			'PreviewActionRunning'=>array('default'=>'A preview action is still running. Please try the reset again.','translated'=>__('A preview action is still running. Please try the reset again.','contest-gallery')),
			'PreviewUploadsRemoveFailed'=>array('default'=>'Some preview uploads could not be removed. Run the reset again.','translated'=>__('Some preview uploads could not be removed. Run the reset again.','contest-gallery')),
			'PreviewResetComplete'=>array('default'=>'Preview actions were reset and their demo credits were restored.','translated'=>__('Preview actions were reset and their demo credits were restored.','contest-gallery')),
			'SixDigitPinSent'=>array('default'=>'A six-digit PIN was sent to your email address.','translated'=>__('A six-digit PIN was sent to your email address.','contest-gallery')),
			'NewSixDigitPinSent'=>array('default'=>'A new six-digit PIN was sent to your email address.','translated'=>__('A new six-digit PIN was sent to your email address.','contest-gallery')),
			'PreviewBusy'=>array('default'=>'The private payment preview is busy. Please try again.','translated'=>__('The private payment preview is busy. Please try again.','contest-gallery')),
			'PreviewResetFailed'=>array('default'=>'The test actions could not be reset.','translated'=>__('The test actions could not be reset.','contest-gallery')),
			'PreviewResetSuccess'=>array('default'=>'The saved test actions were reset.','translated'=>__('The saved test actions were reset.','contest-gallery')),
			'PreviewGrantFailed'=>array('default'=>'The test credits could not be added.','translated'=>__('The test credits could not be added.','contest-gallery')),
			'Resetting'=>array('default'=>'Resetting…','translated'=>__('Resetting…','contest-gallery')),
			'CodeCouldNotBeSent'=>array('default'=>'The code could not be sent.','translated'=>__('The code could not be sent.','contest-gallery')),
			'CodeInvalid'=>array('default'=>'The code is invalid.','translated'=>__('The code is invalid.','contest-gallery')),
			'GalleryNumber'=>array('default'=>'Gallery {number}','translated'=>__('Gallery {number}','contest-gallery')),
			'PurchaseCreditContinue'=>array('default'=>'Purchase a credit to continue.','translated'=>__('Purchase a credit to continue.','contest-gallery')),
			'CheckoutCouldNotBeStarted'=>array('default'=>'Checkout could not be started.','translated'=>__('Checkout could not be started.','contest-gallery')),
			'Processing'=>array('default'=>'Processing…','translated'=>__('Processing…','contest-gallery')),
			'ProcessingPayment'=>array('default'=>'Processing payment','translated'=>__('Processing payment','contest-gallery')),
			'PaymentRedirectNotice'=>array('default'=>'You may be redirected to complete the payment. You will return automatically.','translated'=>__('You may be redirected to complete the payment. You will return automatically.','contest-gallery')),
			'BrowserSavedCheckoutUnavailable'=>array('default'=>'This browser could not access the saved checkout data. Return to the gallery and contact the site owner before making another payment.','translated'=>__('This browser could not access the saved checkout data. Return to the gallery and contact the site owner before making another payment.','contest-gallery')),
			'SavedCheckoutOpenFailed'=>array('default'=>'The saved checkout data could not be opened. You can safely try finalizing this same payment again.','translated'=>__('The saved checkout data could not be opened. You can safely try finalizing this same payment again.','contest-gallery')),
			'SavedCheckoutBlocked'=>array('default'=>'Another browser tab is currently using the saved checkout data. Close the other tab and try finalizing this same payment again.','translated'=>__('Another browser tab is currently using the saved checkout data. Close the other tab and try finalizing this same payment again.','contest-gallery')),
			'SavedCheckoutReadFailed'=>array('default'=>'The saved checkout data could not be read. You can safely try finalizing this same payment again.','translated'=>__('The saved checkout data could not be read. You can safely try finalizing this same payment again.','contest-gallery')),
			'TestPaymentApprovedCreatingOrder'=>array('default'=>'Your test payment was approved. We are creating the gallery order and adding your credits now. You will return to the gallery automatically.','translated'=>__('Your test payment was approved. We are creating the gallery order and adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'PaymentApprovedCreatingOrder'=>array('default'=>'Your payment was approved. We are creating the gallery order and adding your credits now. You will return to the gallery automatically.','translated'=>__('Your payment was approved. We are creating the gallery order and adding your credits now. You will return to the gallery automatically.','contest-gallery')),
			'SavedCheckoutNotFound'=>array('default'=>'The saved checkout data was not found in this browser. Return to the gallery and contact the site owner before making another payment.','translated'=>__('The saved checkout data was not found in this browser. Return to the gallery and contact the site owner before making another payment.','contest-gallery')),
			'PaymentProcessingRetry'=>array('default'=>'Payment processing could not be started. You can safely try finalizing this same payment again.','translated'=>__('Payment processing could not be started. You can safely try finalizing this same payment again.','contest-gallery'))
		);
	}
}

if(!function_exists('cg_paid_actions_custom_translations')){
	function cg_paid_actions_custom_translations(){
		static $translations = null;
		if($translations!==null){return $translations;}
		$translations = array();
		$wpUploadDir = wp_upload_dir();
		$directory = $wpUploadDir['basedir'].'/contest-gallery/paid-actions/json';
		if(!is_dir($directory)){wp_mkdir_p($directory);}
		$file = $directory.'/translations.json';
		if(file_exists($file)){
			$decoded = json_decode(file_get_contents($file),true);
			if(is_array($decoded)){
				foreach($decoded as $default=>$translation){
					$translations[$default] = contest_gal1ery_convert_for_html_output_without_nl2br($translation);
				}
			}
		}
		return $translations;
	}
}

if(!function_exists('cg_paid_actions_t')){
	function cg_paid_actions_t($key,$replacements = array(),$useCustom = true){
		$definitions = cg_paid_actions_language_definitions();
		if(empty($definitions[$key])){return (string)$key;}
		$definition = $definitions[$key];
		$text = !empty($definition['translated']) ? $definition['translated'] : $definition['default'];
		if($useCustom){
			$custom = cg_paid_actions_custom_translations();
			if(!empty($custom[$definition['default']])){$text = $custom[$definition['default']];}
		}
		if(is_array($replacements) && count($replacements)){
			$replace = array();
			foreach($replacements as $name=>$value){$replace['{'.$name.'}'] = $value;}
			$text = strtr($text,$replace);
		}
		return $text;
	}
}

if(!empty($cgPaidActionsLanguageFunctionsOnly)){return;}

if(!isset($is_frontend)){$is_frontend = false;}
$paidActionsDefinitions = cg_paid_actions_language_definitions();
$paidActionsCustomTranslations = cg_paid_actions_custom_translations();
$translations = $paidActionsCustomTranslations;
foreach($paidActionsDefinitions as $paidActionsKey=>$paidActionsDefinition){
	$lVariable = 'l_'.$paidActionsKey;
	$languageVariable = 'language_'.$paidActionsKey;
	${$lVariable} = $paidActionsDefinition['default'];
	${$languageVariable} = cg_paid_actions_t($paidActionsKey,array(),$is_frontend);
	if(!isset($translations[$paidActionsDefinition['default']])){$translations[$paidActionsDefinition['default']] = '';}
}
