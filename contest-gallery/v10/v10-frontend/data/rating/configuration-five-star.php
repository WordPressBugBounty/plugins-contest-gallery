<?php
$AllowRatingMax = $options['general']['AllowRating']-10;

if(empty($isOnlyGalleryNoVoting) && empty($isOnlyGalleryWinner)){
    $votingCookieId = !empty($options['general']['CheckCookie']) && empty($options['general']['CheckLogin']) ? cg_get_valid_frontend_cookie($galeryID,'voting',true) : '';
    $voteOwnerWhere = cg_get_voting_identity_where($galeryID,!empty($options['general']['CheckLogin']),!empty($options['general']['CheckCookie']),!empty($options['general']['CheckIp']),$WpUserId,$userIP,$votingCookieId);
    $ownVotes = $wpdb->get_results($wpdb->prepare(
        "SELECT pid, Rating, Category, CategoriesOn FROM $tablenameIP WHERE GalleryID = %d AND ($voteOwnerWhere) AND Rating >= 1 AND Rating <= %d",
        $galeryID, $AllowRatingMax
    ));
    $countUserVotes = count($ownVotes);
    if(!empty($ownVotes)){
        foreach($ownVotes as $object){
            if(!isset($votedUserPids[$object->pid])){$votedUserPids[$object->pid] = array();}
            $votedUserPids[$object->pid][] = $object->Rating;
            $addCurrentUserVoteCategoryCount($object);
        }
    }
}
