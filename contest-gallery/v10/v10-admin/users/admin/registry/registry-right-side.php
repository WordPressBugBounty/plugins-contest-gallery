<?php

$isOnlyPlaceHolder = true;

echo "<div id='cgFieldsToCloneAndAppendCloneRight' class='cg_hide'>";

    include (__DIR__.'/registry-fields-right/reg-check-agreement-right.php');
    include (__DIR__.'/registry-fields-right/reg-html-right.php');
    include (__DIR__.'/registry-fields-right/reg-profile-image-right.php');
    include (__DIR__.'/registry-fields-right/reg-simple-captcha-right.php');
    include (__DIR__.'/registry-fields-right/reg-google-captcha-right.php');
    include (__DIR__.'/registry-fields-right/reg-input-right.php');
    include (__DIR__.'/registry-fields-right/reg-select-right.php');
    include (__DIR__.'/registry-fields-right/reg-check-right.php');
    include (__DIR__.'/registry-fields-right/reg-radio-right.php');
    include (__DIR__.'/registry-fields-right/reg-textarea-right.php');
    include (__DIR__.'/registry-fields-right/reg-wp-first-name-right.php');
    include (__DIR__.'/registry-fields-right/reg-wp-last-name-right.php');

echo "</div>";

$isOnlyPlaceHolder = false;


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
        }elseif($value ->RowNumber != $previousRowNumber){
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
            include (__DIR__.'/registry-fields-right/registry-reg-add.php');
        }

        if($value->Field_Type == 'profile-image'){
            include (__DIR__.'/registry-fields-right/reg-profile-image-right.php');
        }

        if($value->Field_Type == 'user-check-agreement-field'){
            include (__DIR__.'/registry-fields-right/reg-check-agreement-right.php');
        }

        if($value->Field_Type == 'user-robot-field'){
            include (__DIR__.'/registry-fields-right/reg-simple-captcha-right.php');
        }

        if($value->Field_Type == 'user-robot-recaptcha-field'){
            include (__DIR__.'/registry-fields-right/reg-google-captcha-right.php');
        }

        if($value->Field_Type == 'user-html-field'){
            include (__DIR__.'/registry-fields-right/reg-html-right.php');
        }

        if($value->Field_Type == 'user-select-field'){
            include (__DIR__.'/registry-fields-right/reg-select-right.php');
        }

        if($value->Field_Type == 'user-radio-field'){
            include (__DIR__.'/registry-fields-right/reg-radio-right.php');
        }

        if($value->Field_Type == 'user-check-field'){
            include (__DIR__.'/registry-fields-right/reg-check-right.php');
        }

        if($value->Field_Type == 'user-text-field'){
            include (__DIR__.'/registry-fields-right/reg-input-right.php');
        }

        if($value->Field_Type == 'user-comment-field'){
            include (__DIR__.'/registry-fields-right/reg-textarea-right.php');
        }

        if($value->Field_Type == 'main-user-name'){
            include (__DIR__.'/registry-fields-right/wp-username-right.php');
        }

        if($value->Field_Type == 'wpfn'){
            include (__DIR__.'/registry-fields-right/reg-wp-first-name-right.php');
        }

        if($value->Field_Type == 'wpln'){
            include (__DIR__.'/registry-fields-right/reg-wp-last-name-right.php');
        }

        if(intval($galleryDbVersion)>=14){
            if($value->Field_Type == 'main-nick-name'){
                include (__DIR__.'/registry-fields-right/wp-nickname-right.php');
            }
        }

        if($value->Field_Type == 'main-mail'){
            include (__DIR__.'/registry-fields-right/wp-email-right.php');
        }

        if($value->Field_Type == 'password'){
            include (__DIR__.'/registry-fields-right/wp-password-right.php');
        }

        if($value->Field_Type == 'password-confirm'){
            include (__DIR__.'/registry-fields-right/wp-password-confirm-right.php');
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

