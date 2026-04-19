<?php


// 4 variants possible here if other settings than standard were made

// Check for 1-star rating and non-logged-in user
if($generalOptions['ShowOnlyUsersVotes']==1 && $generalOptions['CheckLogin']!=1 && $generalOptions['AllowRating']==2){

    $countS = $wpdb->get_var( $wpdb->prepare(
        "
							SELECT COUNT(*) AS NumberOfRows
							FROM $tablenameIP 
							WHERE GalleryID = %d and IP = %s and RatingS = %d and pid = %d
						",
        $galeryID,$ip,1,$pictureID
    ) );

}

// Check for 1-star rating and logged-in user
if($generalOptions['ShowOnlyUsersVotes']==1 && $generalOptions['CheckLogin']==1 && $generalOptions['AllowRating']==2){

    if(is_user_logged_in()){

        $countS = $wpdb->get_var( $wpdb->prepare(
            "
								SELECT COUNT(*) AS NumberOfRows
								FROM $tablenameIP
								WHERE GalleryID = %d and WpUserId = %s and RatingS = %d and pid = %d
							",
            $galeryID,get_current_user_id(),1,$pictureID
        ) );

        $countVotesOfUserPerGallery = $wpdb->get_var( $wpdb->prepare(
            "
								SELECT COUNT(*) AS NumberOfRows
								FROM $tablenameIP
								WHERE GalleryID = %d and WpUserId = %s and RatingS = %d
							",
            $galeryID,get_current_user_id(),1
        ) );
    }
}

// Check for 5-star rating and non-logged-in user. countR and rating is necessary to know here
if($generalOptions['ShowOnlyUsersVotes']==1 && $generalOptions['CheckLogin']!=1 && ($generalOptions['AllowRating']>=12 OR $generalOptions['AllowRating']==1)){

    $countR = $wpdb->get_var( $wpdb->prepare(
        "
							SELECT COUNT(*) AS NumberOfRows
							FROM $tablenameIP 
							WHERE GalleryID = %d and IP = %s and Rating >= %d and pid = %d
						",
        $galeryID,$ip,1,$pictureID
    ) );

    $rating = $wpdb->get_var( $wpdb->prepare(
        "
							SELECT SUM(Rating)
							FROM $tablenameIP 
							WHERE GalleryID = %d and IP = %s and Rating >= %d and pid = %d
						",
        $galeryID,$ip,1,$pictureID
    ) );

}


// Check for 5-star rating and non-logged-in user. countR and rating is necessary to know here --- ENDE

// Check for 5-star rating and logged-in user. countR and rating is necessary to know here

if($generalOptions['ShowOnlyUsersVotes']==1 && $generalOptions['CheckLogin']==1 && ($generalOptions['AllowRating']>=12 OR $generalOptions['AllowRating']==1)){

    if(is_user_logged_in()){

        $countR = $wpdb->get_var( $wpdb->prepare(
            "
								SELECT COUNT(*) AS NumberOfRows
								FROM $tablenameIP
								WHERE GalleryID = %d and WpUserId = %s and Rating >= %d and pid = %d
							",
            $galeryID,get_current_user_id(),1,$pictureID
        ) );

        $rating = $wpdb->get_var( $wpdb->prepare(
            "
								SELECT SUM(Rating)
								FROM $tablenameIP
								WHERE GalleryID = %d and WpUserId = %s and Rating >= %d and pid = %d
							",
            $galeryID,get_current_user_id(),1,$pictureID
        ) );

    }

    $countVotesOfUserPerGallery = $wpdb->get_var( $wpdb->prepare(
        "
								SELECT COUNT(*) AS NumberOfRows
								FROM $tablenameIP
								WHERE GalleryID = %d and WpUserId = %s and Rating >= %d
							",
        $galeryID,get_current_user_id(),1
    ) );

}

// Check for 5-star rating and non-logged-in user. countR and rating is necessary to know here --- END
?>