<?php
if(!defined('ABSPATH')){exit;}

// Keep custom wording when the default wording changes.
if(!function_exists('cg_member_translation_aliases')){
function cg_member_translation_aliases(){
	return array(
		'Maximum number of uploads for this category'=>'Maximum amount of uploads per category is',
		'Delete this entry and all its files?'=>'Delete this entry and all its images?',
		'Share this entry'=>'Share this image',
		'My entries'=>'My images',
		'All entries'=>'All images',
		'Your entries. Your gallery.'=>'Your images. Your gallery.',
		'Join and show your own entries.'=>'Join and show your own images.',
		'Show your first entry'=>'Show your first image',
		'Your uploads will appear here.'=>'Your uploads will appear here. You can edit or delete them at any time.',
		'Take a look at this entry'=>'Take a look at this image',
		'No participants with a profile yet. Published entries with a profile will appear here.'=>'No participants yet. Published entries with a profile will appear here.'
	);
}
}

if(!function_exists('cg_member_translation_values')){
function cg_member_translation_values($values){
	if(!is_array($values)){return array();}
	foreach(cg_member_translation_aliases() as $current=>$previous){
		// An explicitly stored empty new value opts back into the translated default.
		if(!array_key_exists($current,$values) && !empty($values[$previous])){$values[$current] = $values[$previous];}
	}
	return $values;
}
}

// The same catalogue drives Edit translations and the frontend JS dictionary.
if(!function_exists('cg_member_interface_translation_groups')){
function cg_member_interface_translation_groups(){
	return array(
		'Navigation'=>array(
			'Member area',
			'Overview',
			'Messages',
			'Friends',
			'Latest entries',
			'Back',
			'Back to friends',
			'Back to overview',
			'Back to received requests',
			'Load more'
		),
		'Messages'=>array(
			'Contact',
			'Message',
			'Send message',
			'Write a message…',
			'Up to 5,000 characters',
			'Earlier messages',
			'Your conversations',
			'You',
			'Sending…',
			'Saving…',
			'No messages yet. Open a participant’s profile to start a conversation.',
			'Discard your unsent message?',
			'{count} new messages and requests'
		),
		'Friends and requests'=>array(
			'Add friend',
			'Add friend - {name}',
			'Remove friend',
			'Remove friend - {name}',
			'Remove this friend?',
			'Remove this friend - {name}?',
			'Accept request',
			'Decline',
			'Cancel request',
			'Received requests',
			'Sent requests',
			'Blocked',
			'Unblock',
			'Block',
			'Review request',
			'Request sent',
			'Friend request sent.',
			'Friend request received.',
			'Block this member? This stops messages and requests and ends your friendship.',
			'Block this member? This stops messages and requests and ends your friendship. Public entries remain visible.',
			'Unblock this member? This allows messages and friend requests again. Your previous friendship will not be restored.'
		),
		'Status and errors'=>array(
			'An administrator has disabled your ability to send messages and friend requests.',
			'Try again',
			'No accessible entries yet.',
			'No accessible galleries yet.',
			'No friends yet.',
			'No friends yet. Send a request from a participant’s profile.',
			'No participants with a profile yet.',
			'Nothing here yet.',
			'Account unavailable',
			'Enter a message of 1 to 5,000 characters.',
			'Invalid page.',
			'Messaging is unavailable for this contact.',
			'Please reopen the conversation and try again.',
			'Please sign in to view friends.',
			'Please wait before sending more messages.',
			'Please wait before sending more requests.',
			'The entry could not be loaded. Please try again.',
			'The member area is not available.',
			'The message could not be saved. Please try again.',
			'This action is not available.',
			'This entry is no longer available.',
			'This member is no longer available.',
			'This participant list is no longer available.',
			'This request has already been used.',
			'The PIN request is being processed. Please try again.'
		),
		'Entries and paid actions'=>array(
			'Gallery entries',
			'View gallery entries',
			'View gallery entries: {name}',
			'Browse entries',
			'Entry',
			'{entries} by {name}',
			'Paid actions are available on the entry page.',
			'Open entry page',
			'Open entry page in a new tab'
		),
		'Sign-in and access'=>array(
			'Gallery view',
			'Please try again.',
			'The connection failed. Please try again.',
			'Please reload the page and try again.',
			'This gallery is not available.',
			'Please allow cookies to sign in.',
			'Please sign in.',
			'Check your email for your code.',
			'Please wait {seconds} seconds before requesting another code.',
			'Loading…'
		),
		'Profile display and saving'=>array(
			'Saved',
			'Visible on your public profile',
			'View profile',
			'View profile of {name}',
			'Your public profile',
			'Public profile',
			'View public profile',
			'Entry owner',
			'Maximum resolution'
		),
		'Participants and their entries'=>array(
			'Participants',
			'Back to participants',
			'Search names and profile information',
			'Name A–Z',
			'Name Z–A',
			'Votes descend',
			'Votes ascend',
			'No participants found.',
			'No participants with a profile yet. Published entries with a profile will appear here.',
			'This profile is no longer available in this gallery.',
			'1 entry',
			'%s entries',
			'Rating average',
			'Rating sum'
		),
		'Gallery headings and statistics'=>array(
			'Gallery',
			'Galleries',
			'Entries',
			'Discover galleries',
			'Explore the gallery',
			'Explore galleries and their entries.',
			'Explore the entries in this gallery.',
			'This participant in this gallery'
		),
		'Upload processing'=>array(
			'Continuing image processing…',
			'Check status',
			'Upload completed.',
			'Refresh'
		),
		'File replacement messages'=>array(
			'Replace file',
			'Choose the file to replace.',
			'Replaced',
			'File replaced.',
			'The preview could not be refreshed.',
			'The file does not meet the gallery upload requirements.',
			'The PDF preview could not be created. Please try again.',
			'Too many replacement attempts. Please try again later.',
			'Your replacement storage limit has been reached. Please contact the gallery administrator.'
		)
	);
}
}

if(!function_exists('cg_member_interface_strings')){
function cg_member_interface_strings(){
	$strings = array();
	foreach(cg_member_interface_translation_groups() as $group){$strings = array_merge($strings,$group);}
	return array_values(array_unique($strings));
}
}

// General wording, edited with the other gallery voting messages.
if(!function_exists('cg_member_voting_translation_strings')){
function cg_member_voting_translation_strings(){
	return array(
		'Vote',
		'Please allow cookies and reload the page to be able to vote.',
		'Your vote could not be saved. Please try again.',
		'Vote and linked comment could not be removed.'
	);
}
}
