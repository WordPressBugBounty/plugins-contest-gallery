<?php
if(!defined('ABSPATH')){exit;}

// Keep custom wording when the default wording changes.
function cg_member_translation_aliases(){
	return array(
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

function cg_member_translation_values($values){
	if(!is_array($values)){return array();}
	foreach(cg_member_translation_aliases() as $current=>$previous){
		// An explicitly stored empty new value opts back into the translated default.
		if(!array_key_exists($current,$values) && !empty($values[$previous])){$values[$current] = $values[$previous];}
	}
	return $values;
}

// The same catalogue drives Edit translations and the frontend JS dictionary.
function cg_member_interface_translation_groups(){
	return array(
		'Member access and messages'=>array(
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
			'Entry owner'
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

function cg_member_interface_strings(){
	$strings = array();
	foreach(cg_member_interface_translation_groups() as $group){$strings = array_merge($strings,$group);}
	return $strings;
}
