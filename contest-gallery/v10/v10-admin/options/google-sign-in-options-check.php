<?php

if(empty($cgGoogleSignInLibStatus)){
    $cgGoogleSignInLibStatus = cg_google_sign_in_lib_checks();
}

if(empty($cgGoogleSignInLibStatus['error'])){
    $CheckMethodGoogleNameClass = 'CheckMethod';
    $CheckMethodUploadGoogleClass = 'CheckMethodUpload';
    $CheckMethodUploadGoogleName = 'RegUserUploadOnly';
}else{
    $CheckMethodGoogleNameClass = 'CheckMethodGoogleNotAvailable';
    $CheckMethodUploadGoogleClass = 'CheckMethodUploadGoogleNotAvailable';
    $CheckMethodUploadGoogleName = 'CheckMethodUploadGoogleNotAvailable';
}

if(empty($isPHPVersionChangedGoogleSignIn)){
    $isPHPVersionChangedGoogleSignIn = 0;
}

if(empty($cgGoogleSignInIsFromMainMenu)){
    $cgGoogleSignInIsFromMainMenu = 0;
}

// Legacy hidden input prevents older admin JS from opening the removed library download dialog.
echo "<input type='hidden' value='1' id='cgGoogleSignInLibAvailable' >";

if(empty($upload_dir)){
    $upload_dir = wp_upload_dir();
}

$cgGoogleSignInTestingFolder = $upload_dir['basedir'].'/contest-gallery/google-sign-in';
$cgGoogleSignInTestingFilePath = $cgGoogleSignInTestingFolder.'/google-sign-in-testing.html';
$cgGoogleSignInTestingFileVersion = 'cg-version-6';
$cgGoogleSignInTestingVersionFileForOrientation = $cgGoogleSignInTestingFolder.'/'.$cgGoogleSignInTestingFileVersion.'.txt';
$cgGoogleSignInTestingFileUrl = __DIR__.'/google-sign-in-testing.html';

if(!file_exists($cgGoogleSignInTestingFilePath)){
    $cgGoogleSignInTestingFileContent = file_get_contents($cgGoogleSignInTestingFileUrl);
    if(!is_dir($cgGoogleSignInTestingFolder)){
        mkdir($cgGoogleSignInTestingFolder, 0755);
    }
    file_put_contents($cgGoogleSignInTestingFilePath,$cgGoogleSignInTestingFileContent);
    file_put_contents($cgGoogleSignInTestingVersionFileForOrientation,$cgGoogleSignInTestingFileVersion);
}else{
    if(!file_exists($cgGoogleSignInTestingVersionFileForOrientation)){
        $cgGoogleSignInTestingFileContent = file_get_contents($cgGoogleSignInTestingFileUrl);
        $cgVersionFiles = glob($cgGoogleSignInTestingFolder.'/cg-version-*.txt');
        if(count($cgVersionFiles)){
            foreach ($cgVersionFiles as $cgVersionFile){
                unlink($cgVersionFile);
            }
        }
        file_put_contents($cgGoogleSignInTestingFilePath,$cgGoogleSignInTestingFileContent);
        file_put_contents($cgGoogleSignInTestingVersionFileForOrientation,$cgGoogleSignInTestingFileVersion);
    }

}
