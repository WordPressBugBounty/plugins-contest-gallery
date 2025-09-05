<?php

$isOnlyPlaceHolder = true;

echo "<div id='cgFieldsToCloneAndAppendCloneRight' class='cg_hide'>";

    include (__DIR__.'/fields-right/input-right.php');

    include (__DIR__.'/fields-right/textarea-right.php');

    include (__DIR__.'/fields-right/select-right.php');

    include (__DIR__.'/fields-right/select-categories-right.php');

    include (__DIR__.'/fields-right/date-right.php');

include (__DIR__.'/fields-right/email-right.php');

include (__DIR__.'/fields-right/url-right.php');

include (__DIR__.'/fields-right/check-agreement-right.php');

include (__DIR__.'/fields-right/simple-captcha-right.php');

include (__DIR__.'/fields-right/google-captcha-right.php');

include (__DIR__.'/fields-right/html-right.php');

echo "</div>";

$isOnlyPlaceHolder = false;

$hasRowsAndColumns = false;

foreach ($selectFormInput as $value) {
    if(!empty($value->RowNumber)){
        $hasRowsAndColumns = true;
    }
}

foreach ($selectFormInput as $value) {
    if($value->Field_Type == 'image-f'){
        include (__DIR__.'/fields-right/image-right.php');
    }
}

$rowCount = 2;// because first is image

$emptyRowCols = [];
function rowColsCheck($rowNumber,$rowCols,$selectFormInput,&$emptyRowCols)
{
    $emptyColsRowNumber[$rowCols] = [];
    foreach ($selectFormInput as $key => $value) {
            // check for 1
            if($value->RowNumber==$rowNumber){
                $emptyColsRowNumber[$rowCols] = $value->RowNumber;
            }
    }
}

$selectFormInput = cg_sort_form_input($selectFormInput);
/*
  echo "<pre>";
   print_r($selectFormInput);
   echo "</pre>";*/

$previousRowNumber = -1;
$previousColNumber = 1;
$echoRowClosed = false;
$closeRow = false;

if(!function_exists('cgEchoClosedRightSide')){
    function cgEchoClosedRightSide()
    {
        echo '<div class="cg_del_row cg_hide" title="Delete row"></div>';
        echo "</div>";
        echo "<div class='cg_row cg_add_row' title='Add row'></div>";
    }
}

if(count($selectFormInput)==0){
    echo "<div class='cg_row cg_add_row'  title='Add row' ></div>";
}else{

    echo "<div class='cg_row cg_add_row'  title='Add row' ></div>";

    foreach ($selectFormInput as $key => $value) {
        $echoRowClosed = false;
        if($previousRowNumber!=-1 && $value ->RowNumber>$previousRowNumber){
            $echoRowClosed = true;
            cgEchoClosedRightSide();
        }

        if($value->RowNumber == 0){// if first time load in 27.0.0
            echo "<div class='cg_row'>";
            echo "<div class='cg_add_col' title='Add column' ></div>";
            $echoRowClosed = true;
        }else if($value ->RowNumber != $previousRowNumber){
            echo "<div class='cg_row'>";
            $cg_hide = '';
            if($value->RowCols==3){
                $cg_hide = 'cg_hide';
            }
            echo "<div class='cg_add_col $cg_hide' title='Add column' ></div>";
            $echoRowClosed = false;
        }

        $previousRowNumber = $value->RowNumber;

        if($value->Field_Type == 'empty-col'){
            include (__DIR__.'/fields-right/upl-add.php');
        }

        if($value->Field_Type == 'text-f'){
            include (__DIR__.'/fields-right/input-right.php');
        }

        if($value->Field_Type == 'comment-f'){
            include (__DIR__.'/fields-right/textarea-right.php');
        }

        if($value->Field_Type == 'select-f'){
            include (__DIR__.'/fields-right/select-right.php');
        }

        if($value->Field_Type == 'selectc-f'){
            include (__DIR__.'/fields-right/select-categories-right.php');
        }

        if($value->Field_Type == 'date-f'){
            include (__DIR__.'/fields-right/date-right.php');
        }

        if($value->Field_Type == 'email-f'){
            include (__DIR__.'/fields-right/email-right.php');
        }

        if($value->Field_Type == 'url-f'){
            include (__DIR__.'/fields-right/url-right.php');
        }

        if($value->Field_Type == 'check-f'){
            include (__DIR__.'/fields-right/check-agreement-right.php');
        }

        if($value->Field_Type == 'caRo-f'){
            include (__DIR__.'/fields-right/simple-captcha-right.php');
        }

        if($value->Field_Type == 'caRoRe-f'){
            include (__DIR__.'/fields-right/google-captcha-right.php');
        }

        if($value->Field_Type == 'html-f'){
            include (__DIR__.'/fields-right/html-right.php');
        }

        if($value->RowNumber == 0){
            cgEchoClosedRightSide();
        }

    }

    if(!$echoRowClosed){
        cgEchoClosedRightSide();
    }

}


//echo '<div class="cg_row cg_add"></div>';

