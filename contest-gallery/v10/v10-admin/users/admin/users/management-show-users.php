<?php

global $wpdb;

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablename_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
$tablename_contest_gal1ery_options = $wpdb->prefix . "contest_gal1ery_options";
$tablename_contest_gal1ery_create_user_form = $wpdb->prefix . "contest_gal1ery_create_user_form";
$userFormShort = $tablename_contest_gal1ery_create_user_form;
$tablename_contest_gal1ery_create_user_entries = $wpdb->prefix . "contest_gal1ery_create_user_entries";
$tablename_contest_gal1ery_google_users = $wpdb->prefix."contest_gal1ery_google_users";
$table_usermeta = $wpdb->base_prefix . "usermeta";
$entriesShort = $tablename_contest_gal1ery_create_user_entries;
$wpUsers = $wpdb->base_prefix . "users";

$GalleryName = $wpdb->get_var("SELECT GalleryName FROM $tablename_contest_gal1ery_options WHERE id = '$GalleryID'");
$allGalleryNamesAndIds = $wpdb->get_results("SELECT GalleryName, id FROM $tablename_contest_gal1ery_options WHERE id >= '0'");


// will be inserted if create csv
echo "<div  id='cg_create_user_data_csv_container'>";
echo "<input type='hidden' name='cg_create_user_data_csv' id='cg_create_user_data_csv' value='true' />";
echo "</div>";


    $start = 0; // Startwert setzen (0 = 1. Zeile)
    $step = 50;

    if (isset($_GET["start"])) {
        $muster = "/^[0-9]+$/"; // reg. Ausdruck für Zahlen
        if (preg_match($muster, $_GET["start"]) == 0) {
            $start = 0; // Bei Manipulation Rückfall auf 0
        } else {
            $start = absint($_GET["start"]);
        }
    }

    if (isset($_GET["step"])) {
        $muster = "/^[0-9]+$/"; // reg. Ausdruck für Zahlen
        if (preg_match($muster, $_GET["start"]) == 0) {
            $step = 50; // Bei Manipulation Rückfall auf 0
        } else {
            $step = absint($_GET["step"]);
        }
    }



$cgSearchGalleryId = '';
$cgSearchGalleryIdParam = '';

if(!empty($_POST['cg-search-gallery-id']) OR !empty($_GET['cg-search-gallery-id'])){
    $cgSearchGalleryId = (!empty($_POST['cg-search-gallery-id'])) ? intval($_POST['cg-search-gallery-id']) : intval($_GET['cg-search-gallery-id']);
    $cgSearchGalleryIdParam = '&cg-search-gallery-id='.$cgSearchGalleryId;
}


    $cgUserName = '';
    $cgUserNameGetParam = '';


$toSelect = "$wpUsers.ID, $wpUsers.user_login, $wpUsers.user_email";

if(!empty($_GET['wp_user_id'])){

    $selectWPusers = $wpdb->get_results("SELECT * FROM $wpUsers WHERE ID = '".$_GET['wp_user_id']."'");

    $rows = $wpdb->get_var(
        "
		SELECT COUNT(*) AS NumberOfRows
		FROM $wpUsers"
    );

}elseif(!empty($_POST['cg-search-user-name']) OR !empty($_GET['cg-search-user-name'])){

        $cgUserName = (!empty($_POST['cg-search-user-name'])) ? sanitize_text_field(htmlentities(html_entity_decode($_POST['cg-search-user-name']))) : sanitize_text_field(htmlentities(html_entity_decode($_GET['cg-search-user-name'])));
        $cgUserNameGetParam = '&cg-search-user-name='.$cgUserName;

        if(!empty($cgSearchGalleryId)){

            $selectWPusers = $wpdb->get_results("SELECT DISTINCT $toSelect FROM $wpUsers, $entriesShort WHERE ($wpUsers.user_login LIKE '%$cgUserName%' OR $wpUsers.user_email LIKE '%$cgUserName%') AND ($wpUsers.ID = $entriesShort.wp_user_id AND $entriesShort.GalleryID = '$cgSearchGalleryId') ORDER BY $wpUsers.ID ASC LIMIT $start, $step");

            $rows = count($wpdb->get_results(
                "
		SELECT DISTINCT $wpUsers.ID  
		FROM $wpUsers, $entriesShort WHERE ($wpUsers.user_login LIKE '%$cgUserName%' OR $wpUsers.user_email LIKE '%$cgUserName%') AND ($wpUsers.ID = $entriesShort.wp_user_id AND $entriesShort.GalleryID = '$cgSearchGalleryId')"
            ));

        }else{
            $selectWPusers = $wpdb->get_results("SELECT $toSelect FROM $wpUsers WHERE user_login LIKE '%$cgUserName%' OR user_email LIKE '%$cgUserName%' ORDER BY $wpUsers.ID ASC LIMIT $start, $step");
            $rows = $wpdb->get_var(
                "
		SELECT COUNT(*) AS NumberOfRows
		FROM $wpUsers WHERE user_login LIKE '%$cgUserName%' OR user_email LIKE '%$cgUserName%'"
            );
        }

    }elseif(!empty($cgSearchGalleryId)){

        $selectWPusers = $wpdb->get_results("SELECT DISTINCT $toSelect FROM $wpUsers, $entriesShort WHERE $wpUsers.ID = $entriesShort.wp_user_id AND $entriesShort.GalleryID = '$cgSearchGalleryId' ORDER BY $wpUsers.ID ASC LIMIT $start, $step");

        $rows = count($wpdb->get_results(
            "
            SELECT DISTINCT $wpUsers.ID  
            FROM $wpUsers,$entriesShort WHERE $wpUsers.ID = $entriesShort.wp_user_id AND $entriesShort.GalleryID = '$cgSearchGalleryId'"
        ));

    }else{
        $selectWPusers = $wpdb->get_results("SELECT $toSelect FROM $wpUsers ORDER BY $wpUsers.ID ASC LIMIT $start, $step");
        $rows = count($selectWPusers);
        $rows = $wpdb->get_var(
            "
		SELECT COUNT(*) AS NumberOfRows
		FROM $wpUsers"
        );
    }


if(isset($_GET['delete_data_csv'])){
    $admin_email = get_option('admin_email');
    $adminHashedPass = $wpdb->get_var("SELECT user_pass FROM $wpUsers WHERE user_email = '$admin_email'");
    $code = $wpdb->base_prefix; // database prefix
    $code = md5($code.$adminHashedPass);
    $dir = plugin_dir_path( __FILE__ );
    $dir = $dir.$code."_userregdata.csv";
    if(file_exists($dir)){
        unlink($dir);
    }
    ?><script>alert('CSV data file deleted.');</script><?php
}

$versionColor = "#444";

echo "<div id='cgCreateDataCSVdiv' class='cg_export_data_div'>";
echo "<input type='button' class='cg_backend_button_gallery_action' id='cg_create_user_data_csv_submit' value='Export registered users data' style='text-align:center;width:210px;margin: 1px auto;' />";
echo "<div style='padding-top:2px;position: relative;'><span class=\"cg-info-icon\" style='font-weight:bold;margin-bottom:10px;position:relative;'>info<span class=\"cg-info-container cg-info-container-gallery-user\" style=\"top: 25px; margin-left: -210px; display: none;font-weight:normal;\">CSV file will be exported separated by semicolon ( ; )</span></span>
    </div>";
echo "</div>";

cg_mails_list();
cg1l_email_backend_template(true);

$nr1 = $start + 1;
/*echo "<div id='cgUsersListSteps' class='cg_pics_per_site' style='margin-top:2px;width: 100%;'>";
    echo "<div class='cg_user_list_step' >";
    for ($i = 0; $rows > $i; $i = $i + $step) {

        $anf = $i + 1;
        $end = $i + $step;

        if ($end > $rows) {
            $end = $rows;
        }


        if ($anf == $nr1 AND ($start+$step) > $rows AND $start==0) {
            continue;
            echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$anf-$end</a></div>";
        }

        elseif ($anf == $nr1 AND ($start+$step) > $rows AND $anf==$end) {

            echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$end</a></div>";
        }


        elseif ($anf == $nr1 AND ($start+$step) > $rows) {

            echo "<div class='cg_step cg_step_selected'><a href=\?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$anf-$end</a></div>";
        }

        elseif ($anf == $nr1) {
            echo "<div class='cg_step cg_step_selected'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$anf-$end</a></div>";
        }

        elseif ($anf == $end) {
            echo "<div class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$end</a></div>";
        }

        else {
            echo "<div class='cg_step'><a href=\"?page=".cg_get_version()."/index.php&option_id=$GalleryID&step=$step&start=$i&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam\">$anf-$end</a></div>";
        }
    }
    echo "</div>";
echo "</div>";*/

$nr1 = $start + 1;
$total = (int)$rows;
$current_start = $start + 1; // make sure it starts at 1, not 0
$current_end = min($start + $step, $total);

echo "<div id='cgUsersListSteps' class='cg_pics_per_site' style='margin-top:2px;width: 100%;'>";
echo "<div id='cgUserListStepsContainer' class='cg_steps_container'  style='display:flex;align-items:center;gap:6px;'>";
echo '<div id="cgRegUserStepsSelectTitle" class="cg_steps_container_title" >';
echo 'Registered users';
echo '</div>';
// --- Dropdown ---
echo "<select id='cgRegUserStepsSelect' class='" . (($rows == 0) ? 'cg_hidden' : '') . "' >";
for ($i = 0; $i < $rows; $i += $step) {
    $anf = $i + 1; // start counting from 1, not 0
    $end = min($i + $step, $rows);
    $label = ($anf === $end) ? $end : "$anf-$end";
    $selected = ($anf === $nr1) ? ' selected' : '';

    $href = "?page=" . cg_get_version() . "/index.php"
        . "&option_id=$GalleryID"
        . "&step=$step"
        . "&start=$i"
        . "&users_management=true"
        . $cgUserNameGetParam
        . $cgSearchGalleryIdParam;

    echo "<option value=\"$i\" data-cg-href=\"$href\"$selected>$label</option>";
}
echo "</select>";

// --- Prev / Next links ---
$prev_start = max(0, $start - $step);
$next_start = ($current_end < $total) ? $start + $step : $start;

$baseUrl = "?page=" . cg_get_version() . "/index.php&option_id=$GalleryID&step=$step&users_management=true$cgUserNameGetParam$cgSearchGalleryIdParam";

echo "<div id='cgRegUsersStepNav' class='cg_step_nav " . (($rows == 0) ? 'cg_hidden' : '') . "' style='display:inline-flex;align-items:center;gap:6px;'>";

echo "<a href=\"" . ($start > 0 ? $baseUrl . "&start=$prev_start" : '#') . "\" class=\"" . ($start > 0 ? "" : 'cg_disabled_background_opacity') . "\" >";

echo '        <svg width="14" height="14" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
          <path d="M10 3 L5 8 L10 13" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';

echo "</a>";

// --- Pagination info ---
echo "<span style='white-space:nowrap;'>&nbsp;$current_start-$current_end&nbsp;of&nbsp;$total&nbsp;</span>";

echo "<a href=\"" . ($current_end < $total ? $baseUrl . "&start=$next_start" : '#') . "\" class=\"" . ($current_end < $total ? "" : 'cg_disabled_background_opacity') . "\" >";
echo '        <svg width="14" height="14" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 3 L11 8 L6 13" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';
echo "</a>";

echo "</div>";


echo "<div id='cgRegistrationSearch'>";/*
echo"&nbsp;&nbsp;Show users per Site:";

*/

echo "<form id='cgUsersManagementForm' method='POST' action='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID#cg-search-results-container' class='cg_load_backend_submit'>";


if(!empty($_GET['wp_user_id'])){

    if(!empty($selectWPusers)){// in this case when uid is send must be only one
        foreach($selectWPusers as $wpUser){
            $cgUserName = $wpUser->user_email;
        }
    }

}
echo '<input style="flex-grow:1;" class="cg_search_user_name" type="text" placeholder="Username/Email"  name="cg-search-user-name" value="'.$cgUserName.'" />';
echo '<input type="hidden" class="cg_search_user_name_original" name="cg-search-user-name-original" value="'.$cgUserName.'" />';

echo "<input type='hidden' disabled name='cg_create_user_data_csv_new_export' id='cg_create_user_data_csv_new_export' value='true' />";
echo '<input type="hidden" name="cg-search-gallery-id-original" value="'.$cgSearchGalleryId.'" />';


echo '<select class="cg_hide" name="cg-search-gallery-id" style="margin-bottom: 6px;
    width: 342px;">';
echo '<option value="">';
echo 'Select users registered about a certain gallery';
echo '</option>';

$selected = '';



foreach($allGalleryNamesAndIds as $keyValue){

    if($keyValue->id==$cgSearchGalleryId){
        $selected = 'selected';
    }

    echo '<option value="'.$keyValue->id.'" '.$selected.'>';
    if(empty($keyValue->GalleryName)){
        echo 'gallery id: '.$keyValue->id;
    }
    else{
        $gid = $keyValue->id;
        echo  $keyValue->GalleryName." (gallery id: $gid)";
    }
    echo '</option>';

    $selected = '';
}
echo '</select>';

echo '<button type="submit" id="cgRegistrationSearchSubmit" data-cg-gid="'.$GalleryID.'" class="cg_backend_button_gallery_action" style="height:28px;">Search</button>';

echo '</form>';

echo '<div id="cgRegistrationSearchReset" title="Refresh and reset search"></div>';

echo "</div>";

echo "</div>";
echo "<div id='cgUnconfirmedListStepsContainer'  class='cg_steps_container cg_steps_loading'>";
    echo "<div id='cgUnconfirmedUserStepsSelectTitle'  class='cg_steps_container_title'>";
    echo "Unconfirmed users";
    echo "</div>";
    echo "<div class='cg_steps_container_loader' >";
        echo '<div class="cg_skeleton_loader_on_page_load" style="width: 100%;height: 30px;"></div>';
    echo "</div>";
echo "</div>";
echo "</div>";

/*
var_dump('$rows');
var_dump($_GET['edit_registration_entries']);
var_dump($_GET['wp_user_id']);
var_dump($rows);
die;*/
if(!empty($_GET['edit_registration_entries']) AND !empty($_GET['wp_user_id'])){

    include("management-show-user.php");

}else{

    echo "<div id='cgListsContainer'>";
    $usersTableHtmlStart = <<<HEREDOC
                <div id="cgUsersManagementList" class="cg_table_list" >
                    <form method="get">
                        <table class="wp-list-table widefat fixed striped users">
                            <thead>
                                <tr>
                                    <th scope="col" id="username" class="manage-column column-username">Username</th>
                                    <th scope="col" id="email" class="manage-column column-email">Email</th>
                                    <th scope="col" id="role" class="manage-column column-role">Role</th>
                                </tr>
                            </thead>
                            <tbody id="the-list" data-wp-lists="list:user">
HEREDOC;

    $usersTableHtmlEnd = <<<HEREDOC

                            </tbody>
                        </table>
                    </form>
                    <br class="clear">
                </div>
HEREDOC;

    $wp_roles = wp_roles();

    echo $usersTableHtmlStart;

    if(!empty($selectWPusers)){

        $wpUsersIdsWithNotConfirmedMailArray = array();

        // select not confirmed users
        $selectWpUsersNotConfirmedQuery = "SELECT DISTINCT wp_user_id FROM $tablename_create_user_entries WHERE";

        foreach ($selectWPusers as $user){
            $userId = $user->ID;
            $selectWpUsersNotConfirmedQuery .= " (wp_user_id = $userId AND activation_key != '') OR ";
        }

        $selectWpUsersNotConfirmedQuery = substr($selectWpUsersNotConfirmedQuery,0,-3);
        $allWpUsersNotConfirmed = $wpdb->get_results($selectWpUsersNotConfirmedQuery);

        foreach ($allWpUsersNotConfirmed as $notConfirmedUser){
            $wpUsersIdsWithNotConfirmedMailArray[] = $notConfirmedUser->wp_user_id;
        }
        // select not confirmed users --- END

        // select google users
        $googleUsersArray = [];

        $selectGoogleUsersQuery = "SELECT DISTINCT WpUserId, ImageUrl FROM $tablename_contest_gal1ery_google_users WHERE";

        foreach ($selectWPusers as $user){
            $userId = $user->ID;
            $selectGoogleUsersQuery .= " WpUserId = $userId OR ";
        }

        $selectGoogleUsersQuery = substr($selectGoogleUsersQuery,0,-3);
        $googleUsers = $wpdb->get_results($selectGoogleUsersQuery);

        foreach ($googleUsers as $googleUser){
            $googleUsersArray[$googleUser->WpUserId] = [];
            $googleUsersArray[$googleUser->WpUserId]['ImageUrl'] = $googleUser->ImageUrl;
        }
        // select google users --- END

        // select profileImages
        $profileImagesArray = [];
        $collectForProfileImages = '';

        foreach ($selectWPusers as $user){
            $userId = $user->ID;
            if($collectForProfileImages==''){
                $collectForProfileImages .= "WpUserId = ".$userId;
            }else{
                $collectForProfileImages .= " OR WpUserId = ".$userId;
            }
        }

        $profileImages = $wpdb->get_results( "SELECT WpUserId, WpUpload FROM $tablename WHERE ($collectForProfileImages) AND (IsProfileImage = '1')");

        if(!empty($profileImages)){
            foreach ($profileImages as $image){
                $imgSrcLarge=wp_get_attachment_image_src($image->WpUpload, 'large');
                if(!empty($imgSrcLarge)){
                    $imgSrcLarge=$imgSrcLarge[0];
                    $profileImagesArray[$image->WpUserId] = $imgSrcLarge;
                }
            }
        }

        // select profileImages --- END


        foreach($selectWPusers as $user){

            $cgAddedByGoogleUser = '';

            if(!empty($profileImagesArray[$user->ID])){
                $imgSrcLarge = $profileImagesArray[$user->ID];
                $avatarImage = '<div class="cg_profile_image_avatar" style="background-image: url('.$imgSrcLarge.');" /></div>';
            }elseif(!empty($googleUsersArray[$user->ID])){
                $cgAddedByGoogleUser = "<div class='cg_added_by_google_user'></div>";
                $googleUserImageUrl = contest_gal1ery_convert_for_html_output_without_nl2br($googleUsersArray[$user->ID]['ImageUrl']);
                $avatarImage = '<img src="'.$googleUserImageUrl.'" height="32px" width="32px" />';
            }else{
                $avatarImage = get_avatar( $user->ID,null,null,null,array('width'=>32,'height'=>32));
            }

            $userId = $user->ID;
            $editUserWPprofileLink = get_edit_user_link($userId);
            $user_login = $user->user_login;

            $user_meta=get_userdata($userId);

            $user_roles=$user_meta->roles; //array of roles the user is part of.

            $firstRoleKey = '';

            if(!empty($user_roles[0])){
                $firstRoleKey = $user_roles[0];
                $firstRoleName = $wp_roles->roles[$firstRoleKey]['name'];
            }else{
                $firstRoleName = '';
            }


            $user_email = $user->user_email;

            $contestGalleryEntriesEditLink = "";

            $cgEntriesCheck = $wpdb->get_var( "SELECT COUNT(*) FROM $entriesShort WHERE GeneralID = '0' AND (wp_user_id  = $userId) AND (Field_Type = 'user-select-field' OR Field_Type = 'user-comment-field' OR Field_Type = 'user-text-field' OR Field_Type = 'user-radio-field' OR Field_Type = 'user-check-field' OR Field_Type = 'user-check-agreement-field')");

            if(!empty($cgEntriesCheck)){
                $contestGalleryEntriesEditLink =  "<span class='cg_entries'><a href='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID&wp_user_id=$userId&edit_registration_entries=true' class='cg_entry' >Edit Contest Gallery registration form entries</a></span>";
            }else{
                $cgEntriesCheck = $wpdb->get_var( "SELECT COUNT(*) FROM $table_usermeta WHERE (user_id  = $userId) AND (meta_key LIKE '%cg_custom_field_id_%')");
                if(!empty($cgEntriesCheck)){
                    $contestGalleryEntriesEditLink =  "<span><a href='?page=".cg_get_version()."/index.php&users_management=true&option_id=$GalleryID&wp_user_id=$userId&edit_registration_entries=true&wp_user_meta_entries=true' class='cg_entry' >Edit Contest Gallery registration form entries</a></span>";
                }else{
                    $contestGalleryEntriesEditLink = "<span>(No Contest Gallery registration form entries to edit)</span>";
                }
            }

            // var_dump(get_avatar( 1));

            echo " <tr id=\"user-$userId\">
                                <td class=\"username column-username has-row-actions column-primary\"
                                    data-colname=\"Username\"><!--<img alt=\"\"
                                                                 src=\"http://0.gravatar.com/avatar/f84d37ce99493155ee296c2b746191d0?s=32&amp;d=mm&amp;r=g\"
                                                                 srcset=\"http://0.gravatar.com/avatar/f84d37ce99493155ee296c2b746191d0?s=64&amp;d=mm&amp;r=g 2x\"
                                                                 class=\"avatar avatar-32 photo\" height=\"32\" width=\"32\">-->
                                                     $cgAddedByGoogleUser$avatarImage                                                                             
                                    <strong><a
                                            href=\"$editUserWPprofileLink\">$user_login</a></strong>";
            if(in_array($userId,$wpUsersIdsWithNotConfirmedMailArray)){
                echo "<span style='margin-left:5px;font-weight:600;'>Mail not confirmed</span>";
            }
                echo "<br>
                                    <div class=\"row-actions\"><span><a target='_blank' 
                                            href=\"$editUserWPprofileLink\">Edit WordPress Profile</a> | </span>
                                            $contestGalleryEntriesEditLink
                                            </div>
                                </td>
                                <td class=\"email column-email\" data-colname=\"Email\">$user_email
                                </td>
                                <td class=\"role column-role\" data-colname=\"Role\">$firstRoleName</td>
                            </tr>";

        }

    }else{
        echo '<tr style="background-color: white;"><td colspan="3"><p style="
    text-align: center;
    margin-top: 30px;
    font-size: 20px;
">No results for this search</p></td></tr>';
    }

    echo $usersTableHtmlEnd;
    echo '<div id="cgUnconfirmedManagementList" class="cg_table_list"><table class="wp-list-table widefat fixed striped users">
                            <thead>
                            <tr>
                                <th scope="col" id="email" class="manage-column">Email</th>
                                <th scope="col" id="email" class="manage-column">Registry date</th>
                                <th scope="col" id="email" class="manage-column">Action</th>
                            </tr>
                            </thead>
                            <tbody >

 <tr id="user-1">
                                                            <td class="username column-username has-row-actions column-primary"  colspan="3">
        <div class="cg_skeleton_loader_on_page_load" style="width: 100%;height: 30px;"></div>
                                </td>  
                            </tr> <tr id="user-2">
                                 <td class="username column-username has-row-actions column-primary"  colspan="3">
        <div class="cg_skeleton_loader_on_page_load" style="width: 100%;height: 30px;"></div>
                                </td>
                            </tr><tr >
                                <td class="username column-username has-row-actions column-primary"  colspan="3">
        <div class="cg_skeleton_loader_on_page_load" style="width: 100%;height: 30px;"></div>
                                </td>
                            </tr> 
                            </tbody>
                        </table></div>';

    echo '</div>';

}


