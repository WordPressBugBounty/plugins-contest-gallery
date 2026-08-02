<?php
if(!function_exists('cg_votes_csv_export_all')){
    function cg_votes_csv_export_all(){
        if(!current_user_can('manage_options')){
            echo "Logged in user have to be able to manage_options to execute export votes.";die;
        }

        cg_check_nonce();

        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";
        $tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";

        $wpPosts = $wpdb->base_prefix . "posts";
        $wpUsers = $wpdb->base_prefix . "users";

        $GalleryID = absint($_POST['cg_option_id']);

        $generalOptions = $wpdb->get_row($wpdb->prepare("SELECT AllowRating FROM $tablename_options WHERE id = %d",array($GalleryID)));

        $AllowRating = (!empty($generalOptions->AllowRating)) ? intval($generalOptions->AllowRating) : 0;
        if($AllowRating===1){
            $AllowRating = 15;
        }
        $AllowRatingMax = 0;
        if($AllowRating>=12 && $AllowRating<=20){
            $AllowRatingMax = $AllowRating-10;
        }

        $categories = $wpdb->get_results($wpdb->prepare("SELECT id, Name FROM $tablename_categories WHERE GalleryID = %d ORDER BY Field_Order DESC",array($GalleryID)));

        // for check-language.php
        $galeryID = $GalleryID;

        include(__DIR__ ."/../../../check-language.php");

        $categoriesUidsNames = array();
        $categoriesUidsNames[0] = $language_Other;

        foreach($categories as $category){
            $categoriesUidsNames[$category->id] = $category->Name;
        }

        $ratingQuery = "$tablename_ip.RatingS = 1";
        $queryArgs = array($GalleryID);
        if($AllowRatingMax){
            $ratingQuery .= " OR ($tablename_ip.Rating BETWEEN 1 AND %d)";
            $queryArgs[] = $AllowRatingMax;
        }

        $votingDataSql = "SELECT
                $wpUsers.user_login,
                $wpUsers.user_email,
                $wpPosts.post_title,
                $wpPosts.guid,
                $tablename.id AS pid,
                $tablename.WpUpload,
                $tablename_ip.id AS ipId,
                $tablename_ip.Tstamp,
                $tablename_ip.IP,
                $tablename_ip.Rating,
                $tablename_ip.RatingS,
                $tablename_ip.WpUserId,
                $tablename_ip.OptionSet,
                $tablename_ip.CookieId,
                $tablename_ip.Category,
                $tablename_ip.CategoriesOn
            FROM $tablename_ip
            INNER JOIN $tablename
                ON $tablename.id = $tablename_ip.pid
                AND $tablename.GalleryID = $tablename_ip.GalleryID
            LEFT JOIN $wpPosts
                ON $wpPosts.ID = $tablename.WpUpload
            LEFT JOIN $wpUsers
                ON $wpUsers.ID = $tablename_ip.WpUserId
            WHERE $tablename_ip.GalleryID = %d
                AND ($ratingQuery)
            ORDER BY $tablename_ip.id DESC";

        $votingData = $wpdb->get_results($wpdb->prepare($votingDataSql,$queryArgs));

        $filename = "cg-votes-gallery-id-$GalleryID.csv";

        nocache_headers();
        header("Content-type: text/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $fp = fopen("php://output",'w');
        if($fp===false){
            echo "CSV export could not be created.";
            die;
        }

        fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));

        $headerRows = array(
            array('gallery id: '.$GalleryID),
            array(
                'entry id',
                'file name',
                'file url',
                'User recognition method',
                'vote id',
                'IP',
                'Cookie id',
                'Category of image as voting was done - id (name)',
                'Rating one star',
                'Rating multiple stars',
                'WordPress user id',
                'WordPress user name',
                'WordPress user email',
                'Vote date'
            )
        );

        foreach($headerRows as $fields){
            fputcsv($fp,cg_neutralize_csv_array($fields),";");
        }

        foreach($votingData as $value){
            if(!empty($value->CategoriesOn)){
                $category = (isset($categoriesUidsNames[$value->Category]))
                    ? $value->Category.' ('.$categoriesUidsNames[$value->Category].')'
                    : $value->Category.' (deleted category)';
            }else{
                $category = '';
            }

            $fields = array(
                $value->pid,
                (!empty($value->WpUpload)) ? $value->post_title : '',
                (!empty($value->WpUpload)) ? $value->guid : '',
                $value->OptionSet,
                $value->ipId,
                $value->IP,
                $value->CookieId,
                $category,
                (!empty($value->RatingS)) ? $value->RatingS : '',
                (!empty($value->Rating)) ? $value->Rating : '',
                (!empty($value->WpUserId)) ? $value->WpUserId : '',
                (!empty($value->WpUserId) && !empty($value->user_login)) ? $value->user_login : '',
                (!empty($value->WpUserId) && !empty($value->user_email)) ? $value->user_email : '',
                cg_get_time_based_on_wp_timezone_conf($value->Tstamp,'d-M-Y H:i:s')
            );

            fputcsv($fp,cg_neutralize_csv_array($fields),";");
        }

        fclose($fp);
        die();
    }
}

?>
