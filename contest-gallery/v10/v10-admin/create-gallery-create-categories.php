<?php

$secFieldsArray = array();
// 1. Feldtitel
$secFieldsArray['titel']="Category";

// 5. Felderfordernis + Eingabe in die Datenbank
$secFieldsArray['mandatory']="off";

$secFieldsArray = serialize($secFieldsArray);

// Zuerst Form Input kreiren
$wpdb->query( $wpdb->prepare(
    "
							INSERT INTO $tablename_form_input
							( id, GalleryID, Field_Type, Field_Order, Field_Content,Show_Slider,Use_as_URL,Active)
							VALUES ( %s,%d,%s,%d,%s,%d,%d,%d )
						",
    '',$nextIDgallery,'selectc-f',1,$secFieldsArray,1,0,1
) );

// create Categories

$categoriesArray = array();
$categoriesToCreate = array(
    1 => 'People',
    2 => 'Nature',
    3 => 'Food',
    4 => 'Architecture',
    5 => 'Animals',
    6 => 'Lost Places',
    7 => 'Machines',
    8 => 'Macro',
    9 => 'Monochrome',
    10 => 'Landscape'
);

foreach($categoriesToCreate as $categoryOrder => $categoryName){
    $categoryInserted = $wpdb->query( $wpdb->prepare(
        "
                      INSERT INTO $tablenameCategories
                      ( id, GalleryID, Name, Field_Order, Active)
                      VALUES ( %s,%s,%s,%s,%d )
                   ",
        '',$nextIDgallery,$categoryName,$categoryOrder,1
    ) );

    if($categoryInserted!==false){
        $category = new stdClass();
        $category->id = (string)$wpdb->insert_id;
        $category->GalleryID = (string)$nextIDgallery;
        $category->Name = $categoryName;
        $category->Field_Order = (string)$categoryOrder;
        $category->Active = '1';
        $categoriesArray[$category->id] = $category;
    }
}

// make json file

$fp = fopen($galleryUpload.'/json/'.$nextIDgallery.'-categories.json', 'w');
fwrite($fp, json_encode($categoriesArray));
fclose($fp);

// create Categories --- END
