<?php


if($is_user_logged_in){
    if(!empty($isCGalleries)){
	    $wpUserImageIds = $wpdb->get_results( $wpdb->prepare(
		    "
							SELECT id
							FROM $tablename
							WHERE WpUserId = %d ORDER BY id DESC
						",
		    $WpUserId
	    ) );
    }else{
	    $wpUserImageIds = $wpdb->get_results( $wpdb->prepare(
		    "
							SELECT id
							FROM $tablename
							WHERE GalleryID = %d and WpUserId = %d ORDER BY id DESC
						",
		    $galeryID,$WpUserId
	    ) );
    }

}


if(empty($wpUserImageIds)){
	$wpUserImageIdsArray = [];
}

?>
<pre>
    <script data-cg-processing="true">

        var index = <?php echo json_encode($galeryIDuserForJs) ?>;
        cgJsData[index].onlyLoggedInUserImages = true;
        cgJsData[index].wpUserImageIds = [];

    </script>
    </pre>
<?php

if(!empty($wpUserImageIds)){
    if(count($wpUserImageIds)){
	        $wpUserImageIdsArray = [];
            foreach($wpUserImageIds as $row){
	            $wpUserImageIdsArray[] = intval($row->id);// intval important for javascript indexOf
            }
            ?>
            <pre>
                <script data-cg-processing="true">
                    var index = <?php echo json_encode($galeryIDuserForJs) ?>;
                    cgJsData[index].wpUserImageIds = <?php echo json_encode($wpUserImageIdsArray) ?>;// intval so later indexOf check goes right
                </script>
            </pre>
            <?php
    }
}
