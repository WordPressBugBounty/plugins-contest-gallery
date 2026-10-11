<?php
if(empty($isOnlyGalleryNoVoting) && empty($isOnlyGalleryWinner)){
    $votingCookieId = !empty($options['general']['CheckCookie']) && empty($options['general']['CheckLogin']) ? cg_get_valid_frontend_cookie($galeryID,'voting',true) : '';
    $voteOwnerWhere = cg_get_voting_identity_where($galeryID,!empty($options['general']['CheckLogin']),!empty($options['general']['CheckCookie']),!empty($options['general']['CheckIp']),$WpUserId,$userIP,$votingCookieId);
    $ownVotes = $wpdb->get_results($wpdb->prepare(
        "SELECT pid, Category, CategoriesOn FROM $tablenameIP WHERE GalleryID = %d AND ($voteOwnerWhere) AND RatingS = 1",
        $galeryID
    ));
    $countUserVotes = count($ownVotes);
    if(!empty($ownVotes)){
        foreach($ownVotes as $object){
            $votedUserPids[] = intval($object->pid);
            $addCurrentUserVoteCategoryCount($object);
        }
    }
}
