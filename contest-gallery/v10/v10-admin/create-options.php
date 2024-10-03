<?php
// Achtung hier! ForwardFrom entspricht der Einstellung als ob Slider als Einstellung gewählt ist
$wpdb->query($wpdb->prepare(
    "
				INSERT INTO $tablenameOptions
				( 
				id, 
				GalleryName, 
				PicsPerSite, 
				WidthThumb, 
				HeightThumb, 
				WidthGallery,
				HeightGallery, 
				DistancePics, 
				DistancePicsV, 
				MinResJPGon, 
				MinResPNGon, 
				MinResGIFon,				
				MinResJPGwidth, 
				MinResJPGheight, 		
				MinResPNGwidth, 
				MinResPNGheight, 		
				MinResGIFwidth, 
				MinResGIFheight, 
				MaxResJPGon, 
				MaxResPNGon, 
				MaxResGIFon,
				MaxResJPG, 
				MaxResJPGwidth, 
				MaxResJPGheight, 
				MaxResPNG, 
				MaxResPNGwidth, 
				MaxResPNGheight, 
				MaxResGIF, 
				MaxResGIFwidth, 
				MaxResGIFheight,
				OnlyGalleryView, 
				SinglePicView, 
				ScaleOnly, 
				ScaleAndCut, 
				FullSize,
				FullSizeGallery,
				FullSizeSlideOutStart,
				AllowSort, 
				RandomSort, 
				RandomSortButton, 
				AllowComments, 
				CommentsOutGallery, 
				AllowRating, 
				VotesPerUser, 
				RatingOutGallery, 
				ShowAlways, 
				ShowAlwaysInfoSlider, 
				IpBlock, 
				CheckLogin,
				FbLike, 
				FbLikeGallery, 
				FbLikeGalleryVote, 
				AllowGalleryScript, 
				InfiniteScroll, 
				FullSizeImageOutGallery, 
				FullSizeImageOutGalleryNewTab, 
				Inform, 
				InformAdmin,
				TimestampPicDownload,
				ThumbLook, 
				AdjustThumbLook, 
				HeightLook, 
				RowLook,
				ThumbLookOrder, 
				HeightLookOrder, 
				RowLookOrder,
				HeightLookHeight, 
				ThumbsInRow, 
				PicsInRow, 
				LastRow, 
				HideUntilVote, 
				HideInfo, 
				ActivateUpload, 
				ContestEnd, 
				ContestEndTime,
				ForwardToURL, 
				ForwardFrom, 
				ForwardType, 
				ActivatePostMaxMB, 
				PostMaxMB, 
				ActivateBulkUpload, 
				BulkUploadQuantity, 
				BulkUploadMinQuantity,
				ShowOnlyUsersVotes,
				FbLikeGoToGalleryLink,
				Version,
				CheckIp,
				CheckCookie,
				CheckCookieAlertMessage,
				SliderLook,
				SliderLookOrder,
				RegistryUserRole,
				ContestStart,
				ContestStartTime,
				MaxResICOon,
				MaxResICOwidth,
				MaxResICOheight,
				ActivatePostMaxMBfile,
				PostMaxMBfile,
				VersionDecimal,
				WpPageParent
				)
				VALUES ( 
				%s,
				%s,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%s,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%d,
				%s,
				%s,
				%d,
				%d,
				%s,
				%d,
				%d,
				%s,
				%d,
				%s,
				%d,
				%d,
				%d,
				%d,
				%d,
				%f,
				%d
				 )
			",
    '',
    $GalleryName,
    $PicsPerSite,
    $WidthThumb,
    $HeightThumb,
    $WidthGallery,
    $HeightGallery,
    $DistancePics,
    $DistancePicsV,
    $MinResJPGon,
    $MinResPNGon,
    $MinResGIFon,
    $MinResJPGwidth,
    $MinResJPGheight,
    $MinResPNGwidth,
    $MinResPNGheight,
    $MinResGIFwidth,
    $MinResGIFheight,
    $MaxResJPGon,
    $MaxResPNGon,
    $MaxResGIFon,
    $MaxResJPG,
    $MaxResJPGwidth,
    $MaxResJPGheight,
    $MaxResPNG,
    $MaxResPNGwidth,
    $MaxResPNGheight,
    $MaxResGIF,
    $MaxResGIFwidth,
    $MaxResGIFheight,
    $OnlyGalleryView,
    $SinglePicView,
    $ScaleOnly,
    $ScaleAndCut,
    $FullSize,
    $FullSizeGallery,
    $FullSizeSlideOutStart,
    $AllowSort,
    $RandomSort,
    $RandomSortButton,
    $AllowComments,
    $CommentsOutGallery,
    $AllowRating,
    $VotesPerUser,
    $RatingOutGallery,
    $ShowAlways,
    $ShowAlwaysInfoSlider,
    $IpBlock,
    $CheckLogin,
    $FbLike,
    $FbLikeGallery,
    $FbLikeGalleryVote,
    $AllowGalleryScript,
    $InfiniteScroll,
    $FullSizeImageOutGallery,
    $FullSizeImageOutGalleryNewTab,
    $Inform,
    $InformAdmin,
    $TimestampPicDownload,
    $ThumbLook,
    $AdjustThumbLook,
    $HeightLook,
    $RowLook,
    $ThumbLookOrder,
    $HeightLookOrder,
    $RowLookOrder,
    $HeightLookHeight,
    $ThumbsInRow,
    $PicsInRow,
    $LastRow,
    $HideUntilVote,
    $HideInfo,
    $ActivateUpload,
    $ContestEnd,
    $ContestEndTime,
    $ForwardToURL,
    $ForwardFrom,
    $ForwardType,
    $ActivatePostMaxMB,
    $PostMaxMB,
    $ActivateBulkUpload,
    $BulkUploadQuantity,
    $BulkUploadMinQuantity,
    $ShowOnlyUsersVotes,
    '',
    $VersionForScripts,
    $CheckIp,
    $CheckCookie,
    $CheckCookieAlertMessage,
    $SliderLook,
    $SliderLookOrder,
    $RegistryUserRole,
    $ContestStart,
    $ContestStartTime,
    $MaxResICOon,
    $MaxResICOwidth,
    $MaxResICOheight,
    $ActivatePostMaxMBfile,
    $PostMaxMBfile,
    $VersionDecimal,
    0
));