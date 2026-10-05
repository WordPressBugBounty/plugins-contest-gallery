<?php

/* UPDATE/INSERT VALUES */


if(isset($_POST['Entry_Field_Content']) AND empty($_POST['wp_user_meta_entries'])){

    $_POST = cg1l_sanitize_post($_POST);

    foreach($_POST['Entry_Field_Content'] as $id => $value){

        $wpdb->update(
            "$tablename_contest_gal1ery_create_user_entries",
            array('Field_Content' => sanitize_text_field(htmlentities($value, ENT_QUOTES))),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

	}
}else{

    if(!empty($_POST['wp_user_meta_entries'])){
        $raw = wp_unslash($_POST);
        $fields = array();$posted = array();
        foreach(cg_member_profile_fields() as $field){
            $key = 'cg_custom_field_id_'.$field->id;
            if(isset($raw[$key]) || !empty($raw['cg_profile_present'][$field->id])){
                $fields[] = $field;
                $posted[$field->id] = isset($raw[$key]) ? $raw[$key] : array();
            }
        }
        $values = cg_member_profile_validate_fields($posted,$fields);
        if(is_wp_error($values)){wp_die(esc_html(cg_member_t($values->get_error_message())));}
        foreach($values as $key=>$value){update_user_meta($wpUserId,$key,wp_slash($value));}
    }

}


/* UPDATE/INSERT VALUES --- END */


?>