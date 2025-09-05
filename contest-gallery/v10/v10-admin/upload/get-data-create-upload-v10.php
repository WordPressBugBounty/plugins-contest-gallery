<?php


// 1. Delete Felder in Entries, F_Input, F_Output
// 2. Swap Field_Order in Entries, F_Input, F_Output (bei post "done-upload" wird alles mitgegeben
// 3. Neue Felder hinzuf�gen in F_Input, Entries
// 4. // Auswahl zum Anzeigen gespeicherter Felder

// Empfangen von Galerie OptiOns ID

$GalleryID = absint($_GET['option_id']);

global $wpdb;

// Tabellennamen bestimmen

$tablename = $wpdb->prefix . "contest_gal1ery";
$tablenameoptions = $wpdb->prefix . "contest_gal1ery_options";
$tablenameentries = $wpdb->prefix . "contest_gal1ery_entries";
$tablename_form_input = $wpdb->prefix . "contest_gal1ery_f_input";
$tablename_form_output = $wpdb->prefix . "contest_gal1ery_f_output";
$tablename_options_visual = $wpdb->prefix . "contest_gal1ery_options_visual";
$tablename_categories = $wpdb->prefix . "contest_gal1ery_categories";
$tablename_pro_options = $wpdb->prefix . "contest_gal1ery_pro_options";

$optionsSql = $wpdb->get_row($wpdb->prepare("SELECT GalleryName, FbLike, Version FROM $tablenameoptions WHERE id = %d", [$GalleryID]));

$GalleryName = $optionsSql->GalleryName;
$FbLike = $optionsSql->FbLike;
$dbGalleryVersion = $optionsSql->Version;

$Version = cg_get_version_for_scripts();

if (!isset($_POST['deleteFieldnumber'])) {
    $_POST['deleteFieldnumber'] = false;
}

// Pr�fen ob es ein Feld gibt welches als Images URL genutzt werden soll
$Use_as_URL = $wpdb->get_var($wpdb->prepare("SELECT Use_as_URL FROM $tablename_form_input WHERE GalleryID = %d AND Use_as_URL = '1'", [$GalleryID]));

$Use_as_URL_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $tablename_form_input WHERE GalleryID = %d AND Use_as_URL = '1'", [$GalleryID]));

$WatermarkPosition = '';
$WatermarkPositionForVisualOptions = '';

$WpAttachmentDetailsType = '';

$IsForWpPageTitleID = 0;
$IsForWpPageDescriptionID = 0;

$SubTitle = 0;
$SubTitleToSet = 0;
$ThirdTitle = 0;
$ThirdTitleToSet = 0;
$EcommerceTitle = 0;
$EcommerceTitleToSet = 0;
$EcommerceDescription = 0;
$EcommerceDescriptionToSet = 0;
$ForwardToUrl = 0;
$ForwardToUrlToSet = 0;
$ForwardToUrlNewTab = 0;
$checkedForwardToUrl = '';
$checkedForwardToUrlNewTab = '';

if (!empty($_POST['upload'])) {

//    echo "<pre>";
//        print_r($_POST);
//    echo "</pre>";

    check_admin_referer('cg_admin');

    $wp_upload_dir = wp_upload_dir();

    $checkDataFormOutput = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_form_input WHERE GalleryID = %d and (Field_Type = 'comment-f' or Field_Type = 'text-f' or Field_Type = 'email-f')", [$GalleryID]));

    //print_r($checkDataFormOutput);

    $rowVisualOptions = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options_visual WHERE GalleryID = %d", [$GalleryID]));

    $Field1IdGalleryView = $rowVisualOptions->Field1IdGalleryView;

    // make json file
    $optionsFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/' . $GalleryID . '-options.json';
    $fp = fopen($optionsFile, 'r');
    $optionsFileData = json_decode(fread($fp, filesize($optionsFile)), true);
    fclose($fp);


    $infoInSliderId = null;
    $infoInGalleryId = null;
    $alternativeFileTypeNameId = null;
    $tagInGalleryId = null;
    $tagInGalleryIdIsForCategories = false;

    // Check if certain fieldnumber should be deleted

    // L�schen Ddaten in Tablename entries
    // L�schen Ddaten in Tablename f_input
    // L�schen Ddaten in Tablename f_output

    $deletedFieldnumberInput = 0;
    $IsForWpPageTitleInputDeleted = false;
    $categoriesDeleted = false;

    $deleteFieldnumberArray = [];

    if (!empty($_POST['deleteFieldnumber'])) {

        if (!empty($_POST['deleteFieldnumber']['deleteCategoryField'])) {

            $categoriesDeleted = true;

            $deleteFieldnumber = intval($_POST['deleteFieldnumber']['deleteCategoryField']);

            //            var_dump('$deleteFieldnumber category');
//            var_dump($deleteFieldnumber);
//            die;

            $deleteFieldnumberArray[] = $deleteFieldnumber;

            $wpdb->query($wpdb->prepare(
                "
                    DELETE FROM $tablename_categories WHERE GalleryID = %d
                ",
                $GalleryID
            ));

            $wpdb->update(
                "$tablename",
                array('Category' => 0),
                array('GalleryID' => $GalleryID),
                array('%d'),
                array('%d')
            );

        }

        if (!empty($_POST['deleteFieldnumber']['normalFields'])) {
            foreach ($_POST['deleteFieldnumber']['normalFields'] as $normalField) {
                $deleteFieldnumber = intval($normalField);
                $deleteFieldnumberArray[] = $deleteFieldnumber;
            }
        }

//        var_dump('$deleteFieldnumberArray');
//         var_dump($deleteFieldnumberArray);

        $IsForWpPageTitleInputId = $wpdb->get_var("SELECT id FROM $tablename_form_input WHERE GalleryID = '$GalleryID' AND IsForWpPageTitle = '1'");

        foreach ($deleteFieldnumberArray as $deleteFieldnumber) {

            if (!empty($deleteFieldnumber) && !empty($IsForWpPageTitleInputId) && $deleteFieldnumber == $IsForWpPageTitleInputId) {
                $IsForWpPageTitleInputDeleted = true;
                // isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized can be reseted generally in that case
                if (!empty($_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized']) && $_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'] == $deleteFieldnumber) {
                    $_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'] = '';
                }
            }

            $wpdb->query($wpdb->prepare(
                "
                DELETE FROM $tablename_form_input WHERE GalleryID = %d AND id = %d
             ",
                $GalleryID, $deleteFieldnumber
            ));

            $wpdb->query($wpdb->prepare(
                "
                DELETE FROM $tablename_form_output WHERE GalleryID = %d AND f_input_id = %d
             ",
                $GalleryID, $deleteFieldnumber
            ));

            $wpdb->query($wpdb->prepare(
                "
                DELETE FROM $tablenameentries WHERE GalleryID = %d AND f_input_id = %d
             ",
                $GalleryID, $deleteFieldnumber
            ));

        }

    }

    // Check if certain fieldnumber should be deleted --- ENDE


    // insert delete Categories


    if (!empty($_POST['deleteCategory']) && !$categoriesDeleted) {// if categories deleted then already all fields deleted

        foreach ($_POST['deleteCategory'] as $categoryId) {
            $categoryId = intval($categoryId);
            $wpdb->query($wpdb->prepare(
                "
                DELETE FROM $tablename_categories WHERE id = %d
             ",
                $categoryId
            ));
            // wenn es die Kategorie gibt wird diese mit 0 upgedatet, wenn nicht dann nicht
            $wpdb->update(
                "$tablename",
                array('Category' => 0),
                array('Category' => $categoryId),
                array('%d'),
                array('%d')
            );
        }

    }


    if (!empty($_POST['cg_category'])) {

        $order = 1;

        $categoriesCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) AS NumberOfRows FROM $tablename_categories WHERE GalleryID = %d ORDER BY Field_Order", [$GalleryID]));

        if (empty($categoriesCount)) { // then CatWidget option has to be set to 1 and show other also to 1 again

            $wpdb->update(
                "$tablename_pro_options",
                array('ShowOther' => 1, 'CatWidget' => 1),
                array('GalleryID' => $GalleryID),
                array('%d', '%d'),
                array('%s')
            );


            if (!empty($optionsFileData[$GalleryID])) {
                $optionsFileData[$GalleryID]['pro']['ShowOther'] = 1;
                $optionsFileData[$GalleryID]['pro']['CatWidget'] = 1;
                $optionsFileData[$GalleryID . '-u']['pro']['ShowOther'] = 1;
                $optionsFileData[$GalleryID . '-u']['pro']['CatWidget'] = 1;
                $optionsFileData[$GalleryID . '-nv']['pro']['ShowOther'] = 1;
                $optionsFileData[$GalleryID . '-nv']['pro']['CatWidget'] = 1;
                $optionsFileData[$GalleryID . '-w']['pro']['ShowOther'] = 1;
                $optionsFileData[$GalleryID . '-w']['pro']['CatWidget'] = 1;
                $optionsFileData[$GalleryID . '-ec']['pro']['ShowOther'] = 1;
                $optionsFileData[$GalleryID . '-ec']['pro']['CatWidget'] = 1;
            } else {
                $optionsFileData['pro']['ShowOther'] = 1;
                $optionsFileData['pro']['CatWidget'] = 1;
            }


            // make json file
            $optionsFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/' . $GalleryID . '-options.json';
            $fp = fopen($optionsFile, 'w');
            fwrite($fp, json_encode($optionsFileData));
            fclose($fp);

        }

        /*
         * Forwarding cg_category example
         * [14] => Array
        (
            [2758] => Category14 <<< such looks
        )
            [15] => brax <<< such looks new
         *
         * */

        foreach ($_POST['cg_category'] as $key => $value) {

            if (is_array($value)) {

                foreach ($value as $id => $name) {
                    $name = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($name);
                    $wpdb->update(
                        "$tablename_categories",
                        array('Name' => $name, 'Field_Order' => $order),
                        array('id' => $id),
                        array('%s'),
                        array('%d')
                    );
                    $order++;

                }

            } else {

                $value = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($value);

                $wpdb->query($wpdb->prepare(
                    "
                      INSERT INTO $tablename_categories
                      ( id, GalleryID, Name, Field_Order, Active)
                      VALUES ( %s,%s,%s,%s,%d )
                   ",
                    '', $GalleryID, $value, $order, 1
                ));

                $order++;

            }


        }

    }

    // insert delete Categories end

    /*    echo "<pre>";
        print_r($_POST['upload']);
        echo "</pre>";*/

    if (!empty($_POST['upload'])) {

        foreach ($_POST['upload'] as $id => $field) {

            if ($id == 'new-0') {
                continue;
            }

            if (strpos($id, 'new-') !== false) {
                $id = 'new';
                $field['new'] = 'true';
            } else {
                $id = absint($id);
            }

            if ($field['type'] == 'bh') {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $bhFieldsArray = array();

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (!empty($field['collapsed'])) {
                    if ($field['collapsed'] == 'on') {
                        $collapsed = 'on';
                    } else {
                        $collapsed = 'off';
                    }
                } else {
                    $collapsed = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 2;
                }

                $bhFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $bhFieldsArray['collapsed'] = cg1l_sanitize_method($collapsed);

                $bhFieldsArray['titel'] = cg1l_sanitize_method($field['title']);
                $bhFieldsArray['file-type-img'] = cg1l_sanitize_method(!empty($field['file-type-img']) ? $field['file-type-img'] : '');
                $bhFieldsArray['alternative-file-title'] = cg1l_sanitize_method(!empty($field['alternative-file-title']) ? $field['alternative-file-title'] : '');
                $bhFieldsArray['alternative-file-type-pdf'] = cg1l_sanitize_method(!empty($field['alternative-file-type-pdf']) ? $field['alternative-file-type-pdf'] : '');
                $bhFieldsArray['alternative-file-type-zip'] = cg1l_sanitize_method(!empty($field['alternative-file-type-zip']) ? $field['alternative-file-type-zip'] : '');
                $bhFieldsArray['alternative-file-type-txt'] = cg1l_sanitize_method(!empty($field['alternative-file-type-txt']) ? $field['alternative-file-type-txt'] : '');
                $bhFieldsArray['alternative-file-type-doc'] = cg1l_sanitize_method(!empty($field['alternative-file-type-doc']) ? $field['alternative-file-type-doc'] : '');
                $bhFieldsArray['alternative-file-type-docx'] = cg1l_sanitize_method(!empty($field['alternative-file-type-docx']) ? $field['alternative-file-type-docx'] : '');
                $bhFieldsArray['alternative-file-type-xls'] = cg1l_sanitize_method(!empty($field['alternative-file-type-xls']) ? $field['alternative-file-type-xls'] : '');
                $bhFieldsArray['alternative-file-type-xlsx'] = cg1l_sanitize_method(!empty($field['alternative-file-type-xlsx']) ? $field['alternative-file-type-xlsx'] : '');
                $bhFieldsArray['alternative-file-type-csv'] = cg1l_sanitize_method(!empty($field['alternative-file-type-csv']) ? $field['alternative-file-type-csv'] : '');
                $bhFieldsArray['alternative-file-type-mp3'] = cg1l_sanitize_method(!empty($field['alternative-file-type-mp3']) ? $field['alternative-file-type-mp3'] : '');
                $bhFieldsArray['alternative-file-type-m4a'] = cg1l_sanitize_method(!empty($field['alternative-file-type-m4a']) ? $field['alternative-file-type-m4a'] : '');
                $bhFieldsArray['alternative-file-type-ogg'] = cg1l_sanitize_method(!empty($field['alternative-file-type-ogg']) ? $field['alternative-file-type-ogg'] : '');
                $bhFieldsArray['alternative-file-type-wav'] = cg1l_sanitize_method(!empty($field['alternative-file-type-wav']) ? $field['alternative-file-type-wav'] : '');
                $bhFieldsArray['alternative-file-type-mp4'] = cg1l_sanitize_method(!empty($field['alternative-file-type-mp4']) ? $field['alternative-file-type-mp4'] : '');
                // $bhFieldsArray['alternative-file-type-avi'] = cg1l_sanitize_method(!empty($field['alternative-file-type-avi']) ? $field['alternative-file-type-avi'] : '' );
                $bhFieldsArray['alternative-file-type-mov'] = cg1l_sanitize_method(!empty($field['alternative-file-type-mov']) ? $field['alternative-file-type-mov'] : '');
                $bhFieldsArray['alternative-file-type-webm'] = cg1l_sanitize_method(!empty($field['alternative-file-type-webm']) ? $field['alternative-file-type-webm'] : '');
                $bhFieldsArray['alternative-file-type-ppt'] = cg1l_sanitize_method(!empty($field['alternative-file-type-ppt']) ? $field['alternative-file-type-ppt'] : '');
                $bhFieldsArray['alternative-file-type-pptx'] = cg1l_sanitize_method(!empty($field['alternative-file-type-pptx']) ? $field['alternative-file-type-pptx'] : '');
                //$bhFieldsArray['alternative-file-type-wmv'] = cg1l_sanitize_method(!empty($field['alternative-file-type-wmv']) ? $field['alternative-file-type-wmv'] : '' );

                if (!empty($cgProFalse)) {
                    $bhFieldsArray['alternative-file-type-pdf'] = '';
                    $bhFieldsArray['alternative-file-type-zip'] = '';
                    // $bhFieldsArray['alternative-file-type-txt'] = '';
                    //$bhFieldsArray['alternative-file-type-doc'] = '';
                    $bhFieldsArray['alternative-file-type-docx'] = '';
                    //$bhFieldsArray['alternative-file-type-xls'] = '';
                    $bhFieldsArray['alternative-file-type-xlsx'] = '';
                    //$bhFieldsArray['alternative-file-type-csv'] = '';
                    $bhFieldsArray['alternative-file-type-mp3'] = '';
                    $bhFieldsArray['alternative-file-type-m4a'] = '';
                    $bhFieldsArray['alternative-file-type-ogg'] = '';
                    $bhFieldsArray['alternative-file-type-wav'] = '';
                    $bhFieldsArray['alternative-file-type-mp4'] = '';
                    $bhFieldsArray['alternative-file-type-mov'] = '';
                    //$bhFieldsArray['alternative-file-type-avi'] = '';
                    $bhFieldsArray['alternative-file-type-webm'] = '';
                    //$bhFieldsArray['alternative-file-type-wmv'] = '';
                    //$bhFieldsArray['alternative-file-type-ppt'] = '';
                    $bhFieldsArray['alternative-file-type-pptx'] = '';
                }

                $bhFieldsArray['alternative-file-preview-required'] = cg1l_sanitize_method(!empty($field['alternative-file-preview-required']) ? $field['alternative-file-preview-required'] : '');
                $bhFieldsArray['alternative-file-preview-hide'] = cg1l_sanitize_method(!empty($field['alternative-file-preview-hide']) ? $field['alternative-file-preview-hide'] : '');

                $bhFieldsArray = serialize($bhFieldsArray);
                $order = $field['order'];


                if (empty($field['new'])) {

                    $wpdb->update(
                        "$tablename_form_input",
                        array(
                            'GalleryID' => $GalleryID, 'Field_Type' => 'image-f', 'Field_Order' => $order, 'Field_Content' => $bhFieldsArray, 'Active' => $active, 'Show_Slider' => 0, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols
                        ),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                      INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%d )
                   ",
                        '', $GalleryID, 'image-f', $order, $bhFieldsArray, 0, $active, $RowNumber, $ColNumber, $RowCols
                    ));

                }

            }

            if ($field['type'] == 'cb' && $cgProVersion) {// CHECK AGREEMENT!!!!!!!

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $cbFieldsArray = array();
                $cbFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $cbFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $onOff = 'on';// check agreement always required

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $order = $field['order'];

                $cbFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);

                /*                echo "<pre>";
                                print_r($cbFieldsArray);
                                echo "</pre>";*/

                $cbFieldsArray = serialize($cbFieldsArray);

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'check-f', 'Field_Order' => $order, 'Field_Content' => $cbFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'Version' => $Version, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {
                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider, Active, Version,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, 'check-f', $order, $cbFieldsArray, $Show_Slider, $active, $Version, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }

                }
            }

            if ($field['type'] == 'nf' or $field['type'] == 'fbt') {// TEXT FIELD!!!!!

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $nfFieldsArray = array();
                $nfFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $nfFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);
                $nfFieldsArray['min-char'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['min-char']);
                $nfFieldsArray['max-char'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['max-char']);

                if (!empty($field['watermarkChecked'])) {
                    $WatermarkPosition = $field['watermarkPosition'];
                    $WatermarkPositionForVisualOptions = $field['watermarkPosition'];
                } else {
                    $WatermarkPosition = '';
                }

                if (!empty($field['WpAttachmentDetailsType'])) {
                    $WpAttachmentDetailsType = $field['WpAttachmentDetailsType'];
                    if (!$cgProVersion) {
                        $WpAttachmentDetailsType = '';
                    }
                } else {
                    $WpAttachmentDetailsType = '';
                }

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                if (!empty($field['IsForWpPageTitle'])) {
                    $IsForWpPageTitle = 1;
                } else {
                    $IsForWpPageTitle = 0;
                }

                if (!empty($field['IsForWpPageDescription'])) {
                    $IsForWpPageDescription = 1;
                } else {
                    $IsForWpPageDescription = 0;
                }

                if (!empty($field['SubTitle'])) {
                    $SubTitle = 1;
                } else {
                    $SubTitle = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (strpos($Version, '-PRO') === false) {
                    $SubTitle = 0;
                }

                if (!empty($field['EcommerceTitle'])) {
                    $EcommerceTitle = 1;
                } else {
                    $EcommerceTitle = 0;
                }

                if (!empty($field['ThirdTitle'])) {
                    $ThirdTitle = 1;
                } else {
                    $ThirdTitle = 0;
                }

                if (!empty($field['EcommerceDescription'])) {
                    $EcommerceDescription = 1;
                } else {
                    $EcommerceDescription = 0;
                }

                if (strpos($Version, '-PRO') === false) {
                    $EcommerceDescription = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $nfFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);

                /*
                echo "<pre>";
                print_r($nfFieldsArray);
                echo "</pre>";*/

                $nfFieldsArray = serialize($nfFieldsArray);
                $order = $field['order'];

                $fieldType = ($field['type'] == 'nf') ? 'text-f' : 'fbt-f';

                //var_dump('$WpAttachmentDetailsType333');
                //var_dump($WpAttachmentDetailsType);

                if (empty($field['new'])) {
                    //var_dump('update');
                    //var_dump($id);
                    //var_dump($tablename_form_input);
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => $fieldType, 'Field_Order' => $order, 'Field_Content' => $nfFieldsArray,
                            'Active' => $active, 'Show_Slider' => $Show_Slider, 'WatermarkPosition' => $WatermarkPosition, 'IsForWpPageTitle' => $IsForWpPageTitle, 'IsForWpPageDescription' => $IsForWpPageDescription, 'SubTitle' => $SubTitle, 'ThirdTitle' => $ThirdTitle, 'EcommerceTitle' => $EcommerceTitle, 'EcommerceDescription' => $EcommerceDescription, 'WpAttachmentDetailsType' => $WpAttachmentDetailsType,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols
                        ),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $id;
                    }
                    if (!empty($IsForWpPageTitle)) {
                        $IsForWpPageTitleID = $id;
                    }
                    if (!empty($IsForWpPageDescription)) {
                        $IsForWpPageDescriptionID = $id;
                    }
                    if (!empty($EcommerceTitle)) {
                        $EcommerceTitleToSet = $id;
                    }
                    if (!empty($EcommerceDescription)) {
                        $EcommerceDescriptionToSet = $id;
                    }
                    if (!empty($field['alternativeFileTypeName'])) {
                        $alternativeFileTypeNameId = $id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }
                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,WatermarkPosition,IsForWpPageTitle,IsForWpPageDescription,SubTitle,ThirdTitle,EcommerceTitle,EcommerceDescription,WpAttachmentDetailsType,FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%s,%d,%d,%d,%d,%s,%s,%s,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, $fieldType, $order, $nfFieldsArray, $Show_Slider, $active, $WatermarkPosition, $IsForWpPageTitle, $IsForWpPageDescription, $SubTitle, $ThirdTitle, $EcommerceTitle, $EcommerceDescription, $WpAttachmentDetailsType, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($IsForWpPageTitle)) {
                        $IsForWpPageTitleID = $wpdb->insert_id;
                    }
                    if (!empty($IsForWpPageDescription)) {
                        $IsForWpPageDescriptionID = $wpdb->insert_id;
                    }
                    if (!empty($EcommerceTitle)) {
                        $EcommerceTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($EcommerceDescription)) {
                        $EcommerceDescriptionToSet = $wpdb->insert_id;
                    }
                    if (!empty($field['alternativeFileTypeName'])) {
                        $alternativeFileTypeNameId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }

                }
            }

            if ($field['type'] == 'dt') {// TEXT FIELD!!!!!

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $dtFieldsArray = array();
                $dtFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $dtFieldsArray['format'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['format']);


                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $dtFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);

                /*
                echo "<pre>";
                print_r($dtFieldsArray);
                echo "</pre>";*/

                $dtFieldsArray = serialize($dtFieldsArray);
                $order = $field['order'];

                $fieldType = 'date-f';

                if (!empty($field['SubTitle'])) {
                    $SubTitle = 1;
                } else {
                    $SubTitle = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (strpos($Version, '-PRO') === false) {
                    $SubTitle = 0;
                }

                if (!empty($field['ThirdTitle'])) {
                    $ThirdTitle = 1;
                } else {
                    $ThirdTitle = 0;
                }

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => $fieldType, 'Field_Order' => $order, 'Field_Content' => $dtFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'SubTitle' => $SubTitle, 'ThirdTitle' => $ThirdTitle,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,SubTitle,ThirdTitle,FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, $fieldType, $order, $dtFieldsArray, $Show_Slider, $active, $SubTitle, $ThirdTitle, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }


                }
            }

            if ($field['type'] == 'url' && ($cgProVersion || !$cgProVersion && floatval($dbGalleryVersion)<27)) {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $urlFieldsArray = array();
                $urlFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $urlFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (!empty($field['ForwardToUrl'])) {
                    $ForwardToUrl = 1;
                } else {
                    $ForwardToUrl = 0;
                }

                if (!empty($field['ForwardToUrlNewTab'])) {
                    $ForwardToUrlNewTab = 1;
                } else {
                    $ForwardToUrlNewTab = 0;
                }

                if (strpos($Version, '-PRO') === false) {
                    $ForwardToUrl = 0;
                    $ForwardToUrlNewTab = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $urlFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $urlFieldsArray = serialize($urlFieldsArray);
                $order = $field['order'];

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'url-f', 'Field_Order' => $order,
                            'Field_Content' => $urlFieldsArray, 'Active' => $active,
                            'Show_Slider' => $Show_Slider, 'ForwardToUrl' => $ForwardToUrl, 'ForwardToUrlNewTab' => $ForwardToUrlNewTab,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%d',
                            '%s', '%d',
                            '%d', '%d', '%d',
                            '%s', '%d', '%d', '%d'),
                        array('%d')
                    );

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }
                    if (!empty($ForwardToUrl)) {
                        $ForwardToUrlToSet = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider, Active, ForwardToUrl, ForwardToUrlNewTab, FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, 'url-f', $order, $urlFieldsArray, $Show_Slider, $active, $ForwardToUrl, $ForwardToUrlNewTab, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($ForwardToUrl)) {
                        $ForwardToUrlToSet = $wpdb->insert_id;
                    }

                }
            }

            if ($field['type'] == 'ef' && $cgProVersion) {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $efFieldsArray = array();
                $efFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $efFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $efFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $efFieldsArray = serialize($efFieldsArray);
                $order = $field['order'];

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'email-f', 'Field_Order' => $order, 'Field_Content' => $efFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%d )
                            ",
                        '', $GalleryID, 'email-f', $order, $efFieldsArray, $Show_Slider, $active, $RowNumber, $ColNumber, $RowCols
                    ));


                }
            }

            if ($field['type'] == 'kf' or $field['type'] == 'fbd') {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                if (!empty($field['WpAttachmentDetailsType'])) {
                    $WpAttachmentDetailsType = $field['WpAttachmentDetailsType'];
                    if (!$cgProVersion) {
                        $WpAttachmentDetailsType = '';
                    }
                } else {
                    $WpAttachmentDetailsType = '';
                }

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $kfFieldsArray = array();
                $kfFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $kfFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);
                $kfFieldsArray['min-char'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['min-char']);
                $kfFieldsArray['max-char'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['max-char']);

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $kfFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $kfFieldsArray = serialize($kfFieldsArray);
                $order = $field['order'];

                $fieldType = ($field['type'] == 'kf') ? 'comment-f' : 'fbd-f';

                if (!empty($field['IsForWpPageDescription'])) {
                    $IsForWpPageDescription = 1;
                } else {
                    $IsForWpPageDescription = 0;
                }

                if (!empty($field['SubTitle'])) {
                    $SubTitle = 1;
                } else {
                    $SubTitle = 0;
                }
                if (strpos($Version, '-PRO') === false) {
                    $SubTitle = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (!empty($field['ThirdTitle'])) {
                    $ThirdTitle = 1;
                } else {
                    $ThirdTitle = 0;
                }

                if (!empty($field['EcommerceDescription'])) {
                    $EcommerceDescription = 1;
                } else {
                    $EcommerceDescription = 0;
                }
                if (strpos($Version, '-PRO') === false) {
                    $EcommerceDescription = 0;
                }

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => $fieldType, 'Field_Order' => $order, 'Field_Content' => $kfFieldsArray,
                            'Active' => $active, 'Show_Slider' => $Show_Slider, 'IsForWpPageDescription' => $IsForWpPageDescription, 'SubTitle' => $SubTitle, 'ThirdTitle' => $ThirdTitle, 'EcommerceDescription' => $EcommerceDescription, 'WpAttachmentDetailsType' => $WpAttachmentDetailsType,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $id;
                    }
                    if (!empty($EcommerceDescription)) {
                        $EcommerceDescriptionToSet = $id;
                    }
                    if (!empty($IsForWpPageDescription)) {
                        $IsForWpPageDescriptionID = $id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {
                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,IsForWpPageDescription,SubTitle,ThirdTitle,EcommerceDescription,WpAttachmentDetailsType,FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%d,%s,%s,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, $fieldType, $order, $kfFieldsArray, $Show_Slider, $active, $IsForWpPageDescription, $SubTitle, $ThirdTitle, $EcommerceDescription, $WpAttachmentDetailsType, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($EcommerceDescription)) {
                        $EcommerceDescriptionToSet = $wpdb->insert_id;
                    }
                    if (!empty($IsForWpPageDescription)) {
                        $IsForWpPageDescriptionID = $wpdb->insert_id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                }
            }

            if ($field['type'] == 'ht' && $cgProVersion) {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $htFieldsArray = array();
                $htFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                //$htFieldsArray['content'] = cg1l_sanitize_method($field['content']);
                $htFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);

                // no need for html field
                /*            if(!empty($field['required'])){
                                $onOff = 'on';
                            }else{
                                $onOff = 'off';
                            }
                            $htFieldsArray['mandatory']=cg1l_sanitize_method($onOff);*/

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $htFieldsArray = serialize($htFieldsArray);
                $order = $field['order'];

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'html-f', 'Field_Order' => $order, 'Field_Content' => $htFieldsArray, 'Active' => $active, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallerytagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%d )
                            ",
                        '', $GalleryID, 'html-f', $order, $htFieldsArray, $Show_Slider, $active, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }


                }
            }

            if ($field['type'] == 'caRo') {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $caFieldsArray = array();
                $caFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $onOff = 'off';// I am not robot captcha always required

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $caFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $caFieldsArray = serialize($caFieldsArray);
                $order = $field['order'];

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'caRo-f', 'Field_Order' => $order, 'Field_Content' => $caFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%d,%d,%d )
                            ",
                        '', $GalleryID, 'caRo-f', $order, $caFieldsArray, $Show_Slider, $active, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }


                }
            }

            if ($field['type'] == 'caRoRe' && ($cgProVersion || !$cgProVersion && floatval($dbGalleryVersion)<27)) {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $caFieldsArray = array();
                if (!empty($field['title'])) {
                    $caFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                } else {
                    $caFieldsArray['titel'] = '';
                }

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                $onOff = 'on';

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $caFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $caFieldsArray = serialize($caFieldsArray);
                $order = $field['order'];
                $ReCaKey = $field['ReCaKey'];
                $ReCaLang = $field['ReCaLang'];

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'caRoRe-f', 'Field_Order' => $order, 'Field_Content' => $caFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'ReCaKey' => $ReCaKey, 'ReCaLang' => $ReCaLang, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active, ReCaKey, ReCaLang,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%s,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, 'caRoRe-f', $order, $caFieldsArray, $Show_Slider, $active, $ReCaKey, $ReCaLang, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }


                }
            }

            if ($field['type'] == 'se' && ($cgProVersion || !$cgProVersion && floatval($dbGalleryVersion)<27)) {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $seFieldsArray = array();
                $seFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);
                $seFieldsArray['content'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['content']);

                if (!empty($field['watermarkChecked'])) {
                    $WatermarkPosition = $field['watermarkPosition'];
                    $WatermarkPositionForVisualOptions = $field['watermarkPosition'];
                } else {
                    $WatermarkPosition = '';
                }

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $seFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $seFieldsArray = serialize($seFieldsArray);

                $order = $field['order'];

                if (!empty($field['SubTitle'])) {
                    $SubTitle = 1;
                } else {
                    $SubTitle = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (strpos($Version, '-PRO') === false) {
                    $SubTitle = 0;
                }
                if (!empty($field['ThirdTitle'])) {
                    $ThirdTitle = 1;
                } else {
                    $ThirdTitle = 0;
                }

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'select-f', 'Field_Order' => $order, 'Field_Content' => $seFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'WatermarkPosition' => $WatermarkPosition, 'SubTitle' => $SubTitle, 'ThirdTitle' => $ThirdTitle,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );
                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,WatermarkPosition,SubTitle,ThirdTitle,FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%s,%d,%d,%s,%d,%d,%d )
                            ",
                        '', $GalleryID, 'select-f', $order, $seFieldsArray, $Show_Slider, $active, $WatermarkPosition, $SubTitle, $ThirdTitle, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }


                }
            }

            if ($field['type'] == 'sec') {

                $ColNumber = (!empty($field['ColNumber'])) ? absint($field['ColNumber']) : 1;
                $RowNumber = (!empty($field['RowNumber'])) ? absint($field['RowNumber']) : 1;
                $RowCols = (!empty($field['RowCols'])) ? absint($field['RowCols']) : 1;

                $secFieldsArray = array();
                $secFieldsArray['titel'] = contest_gal1ery_htmlentities_and_preg_replace_with_cg1l_sanitize_method($field['title']);

                if (!empty($field['watermarkChecked'])) {
                    $WatermarkPosition = $field['watermarkPosition'];
                    $WatermarkPositionForVisualOptions = $field['watermarkPosition'];
                } else {
                    $WatermarkPosition = '';
                }

                if (!empty($field['infoInSlider'])) {
                    $Show_Slider = 1;
                } else {
                    $Show_Slider = 0;
                }

                if (!empty($field['required'])) {
                    if ($field['required'] == 'on') {
                        $onOff = 'on';
                    } else {
                        $onOff = 'off';
                    }
                } else {
                    $onOff = 'off';
                }

                if (empty($field['hide'])) {
                    $active = 1;
                } else {
                    $active = 0;
                }

                $secFieldsArray['mandatory'] = cg1l_sanitize_method($onOff);
                $secFieldsArray = serialize($secFieldsArray);

                $order = $field['order'];

                if (!empty($field['SubTitle'])) {
                    $SubTitle = 1;
                } else {
                    $SubTitle = 0;
                }

                $FieldTitleGallery = !empty(trim($field['FieldTitleGallery'])) ? trim($field['FieldTitleGallery']) : '';

                if (strpos($Version, '-PRO') === false) {
                    $SubTitle = 0;
                }
                if (!empty($field['ThirdTitle'])) {
                    $ThirdTitle = 1;
                } else {
                    $ThirdTitle = 0;
                }

                if (empty($field['new'])) {
                    $wpdb->update(
                        "$tablename_form_input",
                        array('GalleryID' => $GalleryID, 'Field_Type' => 'selectc-f', 'Field_Order' => $order, 'Field_Content' => $secFieldsArray, 'Active' => $active, 'Show_Slider' => $Show_Slider, 'WatermarkPosition' => $WatermarkPosition, 'SubTitle' => $SubTitle, 'ThirdTitle' => $ThirdTitle,
                            'FieldTitleGallery' => $FieldTitleGallery, 'RowNumber' => $RowNumber, 'ColNumber' => $ColNumber, 'RowCols' => $RowCols),
                        array('id' => $id),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d'),
                        array('%d')
                    );

                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $id;
                    }
                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $id;
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryIdIsForCategories = true;
                        $tagInGalleryId = $id;
                    }

                } else {

                    $wpdb->query($wpdb->prepare(
                        "
                                INSERT INTO $tablename_form_input
                      ( id, GalleryID, Field_Type, Field_Order, Field_Content, Show_Slider,Active,WatermarkPosition,SubTitle,ThirdTitle,FieldTitleGallery,RowNumber,ColNumber,RowCols)
                      VALUES ( %s,%d,%s,%d,%s,%d,%d,%s,%d,%d,%s,%d,%d,%d )",
                        '', $GalleryID, 'selectc-f', $order, $secFieldsArray, $Show_Slider, $active, $WatermarkPosition, $SubTitle, $ThirdTitle, $FieldTitleGallery, $RowNumber, $ColNumber, $RowCols
                    ));

                    if (!empty($SubTitle)) {
                        $SubTitleToSet = $wpdb->insert_id;
                    }
                    if (!empty($ThirdTitle)) {
                        $ThirdTitleToSet = $wpdb->insert_id;
                    }

                    $wpdb->update(
                        "$tablename_pro_options",
                        array('ShowOther' => 1, 'CatWidget' => 1),
                        array('GalleryID' => $GalleryID),
                        array('%d', '%d'),
                        array('%s')
                    );

                    if (!empty($field['infoInGallery'])) {
                        $infoInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }
                    if (!empty($field['tagInGallery'])) {
                        $tagInGalleryIdIsForCategories = true;
                        $tagInGalleryId = $wpdb->get_var("SELECT id FROM $tablename_form_input ORDER BY id DESC LIMIT 1");
                    }

                }
            }

        }

    }

    $isResaveOptionsJson = false;

    // update watermark position
    // only a JSON option, will be not saved in table
    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
        $optionsFileData[$GalleryID . '-u']['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
        $optionsFileData[$GalleryID . '-nv']['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
        $optionsFileData[$GalleryID . '-w']['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
        $optionsFileData[$GalleryID . '-ec']['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
    } else {
        $optionsFileData['visual']['WatermarkPosition'] = $WatermarkPositionForVisualOptions;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['SubTitle'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['SubTitle'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['SubTitle'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['SubTitle'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['SubTitle'] = 0;
    } else {
        $optionsFileData['visual']['SubTitle'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['ThirdTitle'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['ThirdTitle'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['ThirdTitle'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['ThirdTitle'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['ThirdTitle'] = 0;
    } else {
        $optionsFileData['visual']['ThirdTitle'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['ForwardToUrl'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['ForwardToUrl'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['ForwardToUrl'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['ForwardToUrl'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['ForwardToUrl'] = 0;
    } else {
        $optionsFileData['visual']['ForwardToUrl'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['IsForWpPageTitleID'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['IsForWpPageTitleID'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['IsForWpPageTitleID'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['IsForWpPageTitleID'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['IsForWpPageTitleID'] = 0;
    } else {
        $optionsFileData['visual']['IsForWpPageTitleID'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['IsForWpPageDescriptionID'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['IsForWpPageDescriptionID'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['IsForWpPageDescriptionID'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['IsForWpPageDescriptionID'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['IsForWpPageDescriptionID'] = 0;
    } else {
        $optionsFileData['visual']['IsForWpPageDescriptionID'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['Field1IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['Field1IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['Field1IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['Field1IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['Field1IdGalleryView'] = 0;
    } else {
        $optionsFileData['visual']['Field1IdGalleryView'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['Field2IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['Field2IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['Field2IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['Field2IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['Field2IdGalleryView'] = 0;
    } else {
        $optionsFileData['visual']['Field2IdGalleryView'] = 0;
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['Field3IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-u']['visual']['Field3IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-nv']['visual']['Field3IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-w']['visual']['Field3IdGalleryView'] = 0;
        $optionsFileData[$GalleryID . '-ec']['visual']['Field3IdGalleryView'] = 0;
    } else {
        $optionsFileData['visual']['Field3IdGalleryView'] = 0;
    }

    if (!empty($SubTitleToSet)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['SubTitle'] = $SubTitleToSet;
            $optionsFileData[$GalleryID . '-u']['visual']['SubTitle'] = $SubTitleToSet;
            $optionsFileData[$GalleryID . '-nv']['visual']['SubTitle'] = $SubTitleToSet;
            $optionsFileData[$GalleryID . '-w']['visual']['SubTitle'] = $SubTitleToSet;
            $optionsFileData[$GalleryID . '-ec']['visual']['SubTitle'] = $SubTitleToSet;
        } else {
            $optionsFileData['visual']['SubTitle'] = $SubTitleToSet;
        }
    }

    if (!empty($EcommerceTitleToSet)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
            $optionsFileData[$GalleryID . '-u']['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
            $optionsFileData[$GalleryID . '-nv']['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
            $optionsFileData[$GalleryID . '-w']['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
            $optionsFileData[$GalleryID . '-ec']['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
        } else {
            $optionsFileData['visual']['EcommerceTitle'] = $EcommerceTitleToSet;
        }
    }

    if (!empty($EcommerceDescriptionToSet)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
            $optionsFileData[$GalleryID . '-u']['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
            $optionsFileData[$GalleryID . '-nv']['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
            $optionsFileData[$GalleryID . '-w']['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
            $optionsFileData[$GalleryID . '-ec']['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
        } else {
            $optionsFileData['visual']['EcommerceDescription'] = $EcommerceDescriptionToSet;
        }
    }

    if (!empty($ThirdTitleToSet)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['ThirdTitle'] = $ThirdTitleToSet;
            $optionsFileData[$GalleryID . '-u']['visual']['ThirdTitle'] = $ThirdTitleToSet;
            $optionsFileData[$GalleryID . '-nv']['visual']['ThirdTitle'] = $ThirdTitleToSet;
            $optionsFileData[$GalleryID . '-w']['visual']['ThirdTitle'] = $ThirdTitleToSet;
            $optionsFileData[$GalleryID . '-ec']['visual']['ThirdTitle'] = $ThirdTitleToSet;
        } else {
            $optionsFileData['visual']['ThirdTitle'] = $ThirdTitleToSet;
        }
    }

    if (!empty($ForwardToUrlToSet)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
            $optionsFileData[$GalleryID . '-u']['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
            $optionsFileData[$GalleryID . '-nv']['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
            $optionsFileData[$GalleryID . '-w']['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
            $optionsFileData[$GalleryID . '-ec']['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
        } else {
            $optionsFileData['visual']['ForwardToUrl'] = $ForwardToUrlToSet;
        }
    }

    if (!empty($optionsFileData[$GalleryID])) {
        $optionsFileData[$GalleryID]['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
        $optionsFileData[$GalleryID . '-u']['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
        $optionsFileData[$GalleryID . '-nv']['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
        $optionsFileData[$GalleryID . '-w']['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
        $optionsFileData[$GalleryID . '-ec']['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
    } else {
        $optionsFileData['visual']['ForwardToUrlNewTab'] = $ForwardToUrlNewTab;
    }

    if (!empty($IsForWpPageTitleID)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
            $optionsFileData[$GalleryID . '-u']['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
            $optionsFileData[$GalleryID . '-nv']['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
            $optionsFileData[$GalleryID . '-w']['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
            $optionsFileData[$GalleryID . '-ec']['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
        } else {
            $optionsFileData['visual']['IsForWpPageTitleID'] = $IsForWpPageTitleID;
        }
    }

    if (!empty($IsForWpPageDescriptionID)) {
        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
            $optionsFileData[$GalleryID . '-u']['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
            $optionsFileData[$GalleryID . '-nv']['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
            $optionsFileData[$GalleryID . '-w']['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
            $optionsFileData[$GalleryID . '-ec']['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
        } else {
            $optionsFileData['visual']['IsForWpPageDescriptionID'] = $IsForWpPageDescriptionID;
        }
    }

    // falls Show info in gallery gesetzt wurde dann inserten
    if (!empty($infoInGalleryId)) {

        $wpdb->update(
            "$tablename_options_visual",
            array('Field1IdGalleryView' => $infoInGalleryId),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );

        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['Field1IdGalleryView'] = $infoInGalleryId;
            $optionsFileData[$GalleryID . '-u']['visual']['Field1IdGalleryView'] = $infoInGalleryId;
            $optionsFileData[$GalleryID . '-nv']['visual']['Field1IdGalleryView'] = $infoInGalleryId;
            $optionsFileData[$GalleryID . '-w']['visual']['Field1IdGalleryView'] = $infoInGalleryId;
            $optionsFileData[$GalleryID . '-ec']['visual']['Field1IdGalleryView'] = $infoInGalleryId;
        } else {
            $optionsFileData['visual']['Field1IdGalleryView'] = $infoInGalleryId;
        }

    } else {
        $wpdb->update(
            "$tablename_options_visual",
            array('Field1IdGalleryView' => 0),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );
    }

    // falls Show info in gallery gesetzt wurde dann inserten
    if (!empty($tagInGalleryId)) {

        $wpdb->update(
            "$tablename_options_visual",
            array('Field2IdGalleryView' => $tagInGalleryId),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );

        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['Field2IdGalleryView'] = $tagInGalleryId;
            $optionsFileData[$GalleryID . '-u']['visual']['Field2IdGalleryView'] = $tagInGalleryId;
            $optionsFileData[$GalleryID . '-nv']['visual']['Field2IdGalleryView'] = $tagInGalleryId;
            $optionsFileData[$GalleryID . '-w']['visual']['Field2IdGalleryView'] = $tagInGalleryId;
            $optionsFileData[$GalleryID . '-ec']['visual']['Field2IdGalleryView'] = $tagInGalleryId;
        } else {
            $optionsFileData['visual']['Field2IdGalleryView'] = $tagInGalleryId;
        }

    } else {
        $wpdb->update(
            "$tablename_options_visual",
            array('Field2IdGalleryView' => 0),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );
    }

    // falls Use as file name in single view gesetzt wurde dann inserten
    if (!empty($alternativeFileTypeNameId)) {

        $wpdb->update(
            "$tablename_options_visual",
            array('Field3IdGalleryView' => $alternativeFileTypeNameId),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );

        if (!empty($optionsFileData[$GalleryID])) {
            $optionsFileData[$GalleryID]['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
            $optionsFileData[$GalleryID . '-u']['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
            $optionsFileData[$GalleryID . '-nv']['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
            $optionsFileData[$GalleryID . '-w']['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
            $optionsFileData[$GalleryID . '-ec']['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
        } else {
            $optionsFileData['visual']['Field3IdGalleryView'] = $alternativeFileTypeNameId;
        }

    } else {

        $wpdb->update(
            "$tablename_options_visual",
            array('Field3IdGalleryView' => 0),
            array('GalleryID' => $GalleryID),
            array('%d'),
            array('%d')
        );

    }

    $optionsFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/' . $GalleryID . '-options.json';
    file_put_contents($optionsFile, json_encode($optionsFileData));

    if (!empty($_POST['cg_category'])) {

        // make json file

        $categories = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_categories WHERE GalleryID = %d ORDER BY Field_Order", [$GalleryID]));

        $categoriesFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/' . $GalleryID . '-categories.json';

        $categoriesArray = array();

        foreach ($categories as $category) {

            if ($tagInGalleryIdIsForCategories) {
                $category->isShowTagInGallery = true;
            }

            $categoriesArray[$category->id] = $category;

        }

        $fp = fopen($categoriesFile, 'w');
        fwrite($fp, json_encode($categoriesArray));
        fclose($fp);

    } else {

        $categoriesFile = $wp_upload_dir['basedir'] . '/contest-gallery/gallery-id-' . $GalleryID . '/json/' . $GalleryID . '-categories.json';

        $fp = fopen($categoriesFile, 'w');
        fwrite($fp, json_encode(array()));
        fclose($fp);

    }

    do_action('cg_json_upload_form', $GalleryID);
    //do_action('cg_json_upload_form_info_data_files',$GalleryID,null);

    if (empty($IsForWpPageTitleInputDeleted)) {
        cg_json_upload_form_info_data_files_new($GalleryID, [], $_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'], $IsForWpPageTitleInputDeleted, true);
    } else if (!empty($IsForWpPageTitleInputDeleted) && !empty($_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'])) {
        cg_json_upload_form_info_data_files_new($GalleryID, [], '', $IsForWpPageTitleInputDeleted, true);
        $IsForWpPageTitleInputDeleted = false;// has to be set false then after first cg_json_upload_form_info_data_files_new with $IsForWpPageTitleInputDeleted as true was processed
        cg_json_upload_form_info_data_files_new($GalleryID, [], $_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'], $IsForWpPageTitleInputDeleted, true);
    } else {
        cg_json_upload_form_info_data_files_new($GalleryID, [], $_POST['isFromEditContactFormIsForWpPageTitleChangedOrHasToBeActualized'], $IsForWpPageTitleInputDeleted, true);
    }

    do_action('cg_json_single_view_order', $GalleryID);

    $tstampFile = $wp_upload_dir["basedir"] . "/contest-gallery/gallery-id-$GalleryID/json/$GalleryID-gallery-tstamp.json";
    $fp = fopen($tstampFile, 'w');
    fwrite($fp, json_encode(time()));
    fclose($fp);

}

// input felder holen zur ausgabe
$selectFormInput = $wpdb->get_results($wpdb->prepare("SELECT * FROM $tablename_form_input WHERE GalleryID = %d ORDER BY Field_Order ASC", [$GalleryID]));

$rowVisualOptions = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tablename_options_visual WHERE GalleryID = %d", [$GalleryID]));

$Field1IdGalleryView = $rowVisualOptions->Field1IdGalleryView;
$Field2IdGalleryView = $rowVisualOptions->Field2IdGalleryView;
$Field3IdGalleryView = $rowVisualOptions->Field3IdGalleryView;


?>