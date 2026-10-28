<?php

if(!defined('ABSPATH')){exit;}

if(!function_exists('cg_votes_csv_write_batch')){
    function cg_votes_csv_write_batch($GalleryID,$cgExportStream,$context,$ids,$headers=false){
        $idSql = count($ids) ? implode(',',array_map('absint',$ids)) : '0';
        global $wpdb;

        $tablename = $wpdb->prefix . "contest_gal1ery";
        $tablename_ip = $wpdb->prefix . "contest_gal1ery_ip";
        $tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
        $tablename_options = $wpdb->prefix . "contest_gal1ery_options";

        $wpPosts = $wpdb->posts;
        $wpUsers = $wpdb->base_prefix . "users";


        $categories = cg_gallery_export_objects($context['categories']);

        // for check-language.php
        $galeryID = $GalleryID;

        include(__DIR__ ."/../../../check-language.php");

        $categoriesUidsNames = array();
        $categoriesUidsNames[0] = $language_Other;

        foreach($categories as $category){
            $categoriesUidsNames[$category->id] = $category->Name;
        }

        $ratingQuery = cg_gallery_export_vote_condition($context,$tablename_ip);
        $queryArgs = array($GalleryID);

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
                AND ($ratingQuery) AND $tablename_ip.id IN ($idSql)
            ORDER BY $tablename_ip.id DESC";

        $votingData = cg_gallery_export_results($wpdb->prepare($votingDataSql,$queryArgs));
        cg_gallery_export_check_db();

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

        if($headers){
            if(fwrite($cgExportStream,chr(0xEF).chr(0xBB).chr(0xBF))!==3){throw new Exception('The CSV header could not be written.');}
            foreach($headerRows as $fields){cg_gallery_export_csv_row($cgExportStream,$fields);}
            return;
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

            cg_gallery_export_csv_row($cgExportStream,$fields);
        }

        cg_gallery_export_check_db();
    }
}


if(!function_exists('cg_votes_csv_export_all')){
    function cg_votes_csv_export_all(){
        cg_gallery_export_legacy_redirect('votes_csv');
    }
}
