<?php
if(!function_exists('cg_create_google_options')){
    function cg_create_google_options($i){

        global $wpdb;
        $tablename_google_options = $wpdb->base_prefix . "$i"."contest_gal1ery_google_options";

        $selectSQLgoogleOptions = $wpdb->get_row( "SELECT * FROM $tablename_google_options WHERE GeneralID = '1'" );
        if(empty($selectSQLgoogleOptions)){

            $TextBeforeGoogleSignInButton = contest_gal1ery_htmlentities_and_preg_replace('<p><b>Sign in with Google to continue.</b></p><p>After signing in, you can vote, comment or upload files when these features are enabled.</p><p><small>This text can be configured in "Edit options" >>> "Login via Google options" >>> "Text before Google sign in button before logged in".</small></p>');

            $wpdb->query( $wpdb->prepare(
                "
					INSERT INTO $tablename_google_options
					( id, GeneralID,
					ClientId,ButtonTextOnLoad,
					ButtonStyle,BorderRadius,
					FeControlsStyle,TextBeforeGoogleSignInButton,
					GooglemailConvert
					)
					VALUES (
					%s,%d,
					%s,%s,
					%s,%d,
					%s,%s,
					%d
					)",
                '','1',
                '','Continue with Google',
                'bright',1,'white',$TextBeforeGoogleSignInButton,1
            ) );

        }
        return $selectSQLgoogleOptions;
    }
}
if(!function_exists('cg_get_google_options')){
    function cg_get_google_options(){
        global $wpdb;

        $tablename_google_options = $wpdb->prefix . "contest_gal1ery_google_options";
        $selectSQLgoogleOptions = $wpdb->get_row( "SELECT * FROM $tablename_google_options WHERE GeneralID = '1'" );

        return $selectSQLgoogleOptions;
    }
}
