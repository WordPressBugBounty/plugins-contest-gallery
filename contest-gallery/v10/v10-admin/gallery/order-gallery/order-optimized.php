<?php

$cgOrderFilterSql = '';

if(!empty($_POST['cg_show_only_winners'])){
    $cgOrderFilterSql .= ' AND cgGallery.Winner = 1 ';
}

if(!empty($_POST['cg_show_only_active'])){
    $cgOrderFilterSql .= ' AND cgGallery.Active = 1 ';
}

if(!empty($_POST['cg_show_only_inactive'])){
    $cgOrderFilterSql .= ' AND cgGallery.Active = 0 ';
}

if($cgWpUserIdFilterValue === '0'){
    $cgOrderFilterSql .= ' AND cgGallery.WpUserId = 0 ';
}elseif(!empty($cgWpUserIdFilterValue)){
    $cgOrderFilterSql .= ' AND cgGallery.WpUserId = '.absint($cgWpUserIdFilterValue).' ';
}

$cgOrderSearchSql = '';
$cgOrderSearchParameters = array();

if($search !== ''){
    $cgOrderSearchLike = '%'.$wpdb->esc_like($search).'%';
    $cgOrderSearchId = absint($search);

    $cgOrderSearchSql = "
        AND (
            cgGallery.id = %d
            OR cgGallery.Exif LIKE %s
            OR EXISTS(
                SELECT 1
                FROM $tablenameentries AS cgSearchEntry
                WHERE cgSearchEntry.GalleryID = %d
                AND cgSearchEntry.pid = cgGallery.id
                AND (
                    cgSearchEntry.Short_Text LIKE %s
                    OR cgSearchEntry.Long_Text LIKE %s
                )
            )
            OR EXISTS(
                SELECT 1
                FROM $tablename_categories AS cgSearchCategory
                WHERE cgSearchCategory.GalleryID = %d
                AND cgSearchCategory.id = cgGallery.Category
                AND cgSearchCategory.Name LIKE %s
            )
            OR EXISTS(
                SELECT 1
                FROM $table_posts AS cgSearchPost
                WHERE cgSearchPost.ID = cgGallery.WpUpload
                AND (
                    cgSearchPost.post_content LIKE %s
                    OR cgSearchPost.post_title LIKE %s
                    OR cgSearchPost.post_name LIKE %s
                )
            )
            OR EXISTS(
                SELECT 1
                FROM $wpUsers AS cgSearchUser
                WHERE cgSearchUser.ID = cgGallery.WpUserId
                AND (
                    cgSearchUser.user_login LIKE %s
                    OR cgSearchUser.user_nicename LIKE %s
                    OR cgSearchUser.user_email LIKE %s
                    OR cgSearchUser.display_name LIKE %s
                )
            )
        )
    ";

    $cgOrderSearchParameters = array(
        $cgOrderSearchId,
        $cgOrderSearchLike,
        $GalleryID,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $GalleryID,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike,
        $cgOrderSearchLike
    );

    $cgOrderCountQuery = "
        SELECT COUNT(*)
        FROM $tablename AS cgGallery
        WHERE cgGallery.GalleryID = %d
        $cgOrderFilterSql
        $cgOrderSearchSql
    ";

    $cgOrderCountParameters = array_merge(
        array($GalleryID),
        $cgOrderSearchParameters
    );

    $countSearchSQL = $wpdb->get_var(
        $wpdb->prepare($cgOrderCountQuery,$cgOrderCountParameters)
    );
}

$cgOrderJoinSql = '';
$cgOrderJoinParameters = array();
$cgOrderExtraSelectSql = '';
$cgOrderBySql = 'cgGallery.id DESC';
$cgOrderDirection = (substr($order,-3) === 'asc') ? 'ASC' : 'DESC';
$cgOrderIsHandled = false;

if(strpos($order,'cg_categories_') !== false){
    $cgOrderJoinSql = "
        LEFT JOIN $tablename_categories AS cgOrderCategory
        ON cgOrderCategory.id = cgGallery.Category
        AND cgOrderCategory.GalleryID = cgGallery.GalleryID
    ";
    $cgOrderExtraSelectSql = ", COALESCE(cgOrderCategory.Name,'') AS Name";
    $cgOrderBySql = "COALESCE(cgOrderCategory.Name,'') $cgOrderDirection, cgGallery.rowid $cgOrderDirection";
    $cgOrderIsHandled = true;
}elseif(strpos($order,'cg_email_registered_users') !== false){
    $cgOrderJoinSql = "
        LEFT JOIN $wpUsers AS cgOrderUser
        ON cgOrderUser.ID = cgGallery.WpUserId
    ";
    $cgOrderExtraSelectSql = ", COALESCE(cgOrderUser.user_email,'') AS user_email";
    $cgOrderBySql = "COALESCE(cgOrderUser.user_email,'') $cgOrderDirection, cgGallery.rowid $cgOrderDirection";
    $cgOrderIsHandled = true;
}elseif(strpos($order,'wp_username') !== false){
    $cgOrderJoinSql = "
        LEFT JOIN $wpUsers AS cgOrderUser
        ON cgOrderUser.ID = cgGallery.WpUserId
    ";
    $cgOrderExtraSelectSql = ", COALESCE(cgOrderUser.user_login,'') AS user_login";
    $cgOrderBySql = "COALESCE(cgOrderUser.user_login,'') $cgOrderDirection, cgGallery.rowid $cgOrderDirection";
    $cgOrderIsHandled = true;
}elseif(
    strpos($order,'cg_input_') !== false ||
    strpos($order,'cg_textarea_') !== false ||
    strpos($order,'cg_select_') !== false ||
    strpos($order,'cg_date_') !== false ||
    strpos($order,'cg_email_unregistered_users') !== false
){
    $cgOrderTextColumn = 'Short_Text';

    if(strpos($order,'cg_textarea_') !== false){
        $cgOrderTextColumn = 'Long_Text';
    }elseif(strpos($order,'cg_date_') !== false){
        $cgOrderTextColumn = 'InputDate';
    }

    $cgOrderInputIdParts = explode('_for_id_',$order);
    $cgOrderInputId = 0;

    if(isset($cgOrderInputIdParts[1])){
        $cgOrderInputIdParts = explode('_id_',$cgOrderInputIdParts[1]);
        $cgOrderInputId = absint($cgOrderInputIdParts[0]);
    }

    $cgOrderJoinSql = "
        LEFT JOIN (
            SELECT cgOrderEntry.pid, cgOrderEntry.$cgOrderTextColumn AS SortValue
            FROM $tablenameentries AS cgOrderEntry
            INNER JOIN (
                SELECT pid, MAX(id) AS LatestEntryId
                FROM $tablenameentries
                WHERE GalleryID = %d
                AND f_input_id = %d
                GROUP BY pid
            ) AS cgOrderLatestEntry
            ON cgOrderLatestEntry.LatestEntryId = cgOrderEntry.id
        ) AS cgOrderField
        ON cgOrderField.pid = cgGallery.id
    ";
    $cgOrderJoinParameters = array($GalleryID,$cgOrderInputId);
    $cgOrderExtraSelectSql = ", COALESCE(cgOrderField.SortValue,'') AS $cgOrderTextColumn";
    $cgOrderBySql = "COALESCE(cgOrderField.SortValue,'') $cgOrderDirection, cgGallery.rowid $cgOrderDirection";
    $cgOrderIsHandled = true;
}

$cgOrderRatingOrders = array(
    'rating_desc',
    'rating_asc',
    'rating_desc_with_manip',
    'rating_asc_with_manip',
    'rating_desc_sum',
    'rating_asc_sum',
    'rating_desc_sum_with_manip',
    'rating_asc_sum_with_manip',
    'rating_desc_average',
    'rating_asc_average',
    'rating_desc_average_with_manip',
    'rating_asc_average_with_manip'
);

if(!$cgOrderIsHandled && in_array($order,$cgOrderRatingOrders,true)){
    $cgOrderRatingLimit = $AllowRating - 10;

    if($cgOrderRatingLimit < 2){
        $cgOrderRatingLimit = 2;
    }

    if($cgOrderRatingLimit > 10){
        $cgOrderRatingLimit = 10;
    }

    $cgOrderJoinSql = "
        LEFT JOIN (
            SELECT
                pid,
                SUM(CASE WHEN RatingS = 1 THEN 1 ELSE 0 END) AS CountStotalCount,
                SUM(CASE WHEN Rating >= 1 AND Rating <= $cgOrderRatingLimit THEN 1 ELSE 0 END) AS CountRtotalCount,
                SUM(CASE WHEN Rating >= 1 AND Rating <= $cgOrderRatingLimit THEN Rating ELSE 0 END) AS CountRtotalSum
            FROM $tablenameIP
            WHERE GalleryID = %d
            GROUP BY pid
        ) AS cgOrderVotes
        ON cgOrderVotes.pid = cgGallery.id
    ";
    $cgOrderJoinParameters = array($GalleryID);

    $cgOrderManualCountParts = array();
    $cgOrderManualSumParts = array();

    for($cgOrderRatingValue = 1;$cgOrderRatingValue <= $cgOrderRatingLimit;$cgOrderRatingValue++){
        $cgOrderManualCountParts[] = "cgGallery.addCountR$cgOrderRatingValue";
        $cgOrderManualSumParts[] = "cgGallery.addCountR$cgOrderRatingValue*$cgOrderRatingValue";
    }

    $cgOrderRealCountExpression = ($AllowRating == 2)
        ? 'COALESCE(cgOrderVotes.CountStotalCount,0)'
        : 'COALESCE(cgOrderVotes.CountRtotalCount,0)';
    $cgOrderManualCountExpression = ($AllowRating == 2)
        ? 'cgGallery.addCountS'
        : '('.implode('+',$cgOrderManualCountParts).')';
    $cgOrderRealSumExpression = 'COALESCE(cgOrderVotes.CountRtotalSum,0)';
    $cgOrderManualSumExpression = '('.implode('+',$cgOrderManualSumParts).')';
    $cgOrderEffectiveCountExpression = "($cgOrderRealCountExpression+$cgOrderManualCountExpression)";
    $cgOrderEffectiveSumExpression = "($cgOrderRealSumExpression+$cgOrderManualSumExpression)";

    if($order === 'rating_desc' || $order === 'rating_asc'){
        $cgOrderExtraSelectSql = ($AllowRating == 2)
            ? ", $cgOrderRealCountExpression AS CountStotalCount"
            : ", $cgOrderRealCountExpression AS CountRtotalCount";
        $cgOrderBySql = "$cgOrderRealCountExpression $cgOrderDirection, cgGallery.id $cgOrderDirection";
    }elseif($order === 'rating_desc_with_manip' || $order === 'rating_asc_with_manip'){
        $cgOrderExtraSelectSql = ($AllowRating == 2)
            ? ", $cgOrderRealCountExpression AS CountStotalCount, $cgOrderManualCountExpression AS CountStotalCountAdd, $cgOrderEffectiveCountExpression AS CountStotalCountWithManip"
            : ", $cgOrderRealCountExpression AS CountRtotalCount, $cgOrderManualCountExpression AS CountRtotalCountAdd, $cgOrderEffectiveCountExpression AS CountRtotalCountWithManip";
        $cgOrderBySql = "$cgOrderEffectiveCountExpression $cgOrderDirection, cgGallery.id $cgOrderDirection";
    }elseif(
        $order === 'rating_desc_sum' ||
        $order === 'rating_asc_sum' ||
        $order === 'rating_desc_sum_with_manip' ||
        $order === 'rating_asc_sum_with_manip'
    ){
        $cgOrderIncludeManipulation = (
            $order === 'rating_desc_sum_with_manip' ||
            $order === 'rating_asc_sum_with_manip'
        );
        $cgOrderSortSumExpression = $cgOrderIncludeManipulation
            ? $cgOrderEffectiveSumExpression
            : $cgOrderRealSumExpression;
        $cgOrderSortCountExpression = $cgOrderIncludeManipulation
            ? $cgOrderEffectiveCountExpression
            : $cgOrderRealCountExpression;
        $cgOrderExtraSelectSql = "
            , $cgOrderRealCountExpression AS CountRtotalCount
            , $cgOrderRealSumExpression AS CountRtotalSum
            , $cgOrderManualCountExpression AS CountRtotalCountAdd
            , $cgOrderManualSumExpression AS CountRtotalSumAdd
            , $cgOrderEffectiveCountExpression AS CountRtotalCountWithManip
            , $cgOrderEffectiveSumExpression AS CountRtotalSumWithManip
        ";
        $cgOrderBySql = "$cgOrderSortSumExpression $cgOrderDirection, $cgOrderSortCountExpression $cgOrderDirection, cgGallery.rowid $cgOrderDirection";
    }else{
        $cgOrderIncludeManipulation = (
            $order === 'rating_desc_average_with_manip' ||
            $order === 'rating_asc_average_with_manip'
        );
        $cgOrderAverageCountExpression = $cgOrderIncludeManipulation
            ? $cgOrderEffectiveCountExpression
            : $cgOrderRealCountExpression;
        $cgOrderAverageSumExpression = $cgOrderIncludeManipulation
            ? $cgOrderEffectiveSumExpression
            : $cgOrderRealSumExpression;
        $cgOrderAverageExpression = "
            CASE
                WHEN $cgOrderAverageCountExpression > 0
                THEN ROUND($cgOrderAverageSumExpression/$cgOrderAverageCountExpression,1)
                ELSE 0
            END
        ";
        $cgOrderExtraSelectSql = "
            , $cgOrderRealCountExpression AS CountRtotalCount
            , $cgOrderRealSumExpression AS CountRtotalSum
            , $cgOrderManualCountExpression AS CountRtotalCountAdd
            , $cgOrderManualSumExpression AS CountRtotalSumAdd
            , $cgOrderAverageCountExpression AS CountRtotalCountCalculated
            , $cgOrderAverageSumExpression AS CountRtotalSumCalculated
            , $cgOrderAverageExpression AS CountRtotalAverageCalculated
        ";
        $cgOrderBySql = "$cgOrderAverageExpression $cgOrderDirection, $cgOrderAverageCountExpression $cgOrderDirection, cgGallery.id DESC";
    }

    $cgOrderIsHandled = true;
}

if(!$cgOrderIsHandled){
    switch($order){
        case 'custom':
            $cgOrderBySql = 'cgGallery.PositionNumber ASC, cgGallery.id DESC';
            break;
        case 'date_asc':
            $cgOrderBySql = 'cgGallery.id ASC';
            break;
        case 'comments_desc':
            $cgOrderBySql = 'cgGallery.CountC DESC, cgGallery.id DESC';
            break;
        case 'comments_asc':
            $cgOrderBySql = 'cgGallery.CountC ASC, cgGallery.id DESC';
            break;
        case 'date_desc':
        default:
            $cgOrderBySql = 'cgGallery.id DESC';
            break;
    }
}

$cgOrderSelectQuery = "
    SELECT cgGallery.* $cgOrderExtraSelectSql
    FROM $tablename AS cgGallery
    $cgOrderJoinSql
    WHERE cgGallery.GalleryID = %d
    $cgOrderFilterSql
    $cgOrderSearchSql
    ORDER BY $cgOrderBySql
    LIMIT %d, %d
";

$cgOrderSelectParameters = array_merge(
    $cgOrderJoinParameters,
    array($GalleryID),
    $cgOrderSearchParameters,
    array($start,$step)
);

$selectSQL = $wpdb->get_results(
    $wpdb->prepare($cgOrderSelectQuery,$cgOrderSelectParameters)
);
