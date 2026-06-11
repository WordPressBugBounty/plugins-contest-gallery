<?php

return array(
    $tablename_categories => array(
        'GalleryID_Field_Order_id_index' => array('columns' => array('GalleryID','Field_Order','id')),
        'GalleryID_Active_Field_Order_index' => array('columns' => array('GalleryID','Active','Field_Order'))
    ),
    $tablename => array(
        'WpUpload_index' => array('columns' => array('WpUpload')),
        'WpUserId_index' => array('columns' => array('WpUserId')),
        'GalleryID_index' => array('columns' => array('GalleryID')),
        'PdfPreview_index' => array('columns' => array('PdfPreview')),
        'GalleryID_id_index' => array('columns' => array('GalleryID','id')),
        'GalleryID_Active_id_index' => array('columns' => array('GalleryID','Active','id')),
        'GalleryID_WpUserId_id_index' => array('columns' => array('GalleryID','WpUserId','id')),
        'GalleryID_PositionNumber_id_index' => array('columns' => array('GalleryID','PositionNumber','id')),
        'GalleryID_Active_Category_index' => array('columns' => array('GalleryID','Active','Category'))
    ),
    $tablename_ip => array(
        'pid_index' => array('columns' => array('pid')),
        'WpUserId_index' => array('columns' => array('WpUserId')),
        'GalleryID_index' => array('columns' => array('GalleryID')),
        'GalleryID_pid_id_index' => array('columns' => array('GalleryID','pid','id')),
        'GalleryID_WpUserId_pid_id_index' => array('columns' => array('GalleryID','WpUserId','pid','id')),
        'GalleryID_CookieId_pid_id_index' => array('columns' => array('GalleryID',array('name' => 'CookieId','length' => 64),'pid','id')),
        'GalleryID_IP_CookieId_pid_id_index' => array('columns' => array('GalleryID',array('name' => 'IP','length' => 64),array('name' => 'CookieId','length' => 64),'pid','id')),
        'GalleryID_Tstamp_index' => array('columns' => array('GalleryID','Tstamp'))
    ),
    $tablename_comments => array(
        'pid_index' => array('columns' => array('pid')),
        'WpUserId_index' => array('columns' => array('WpUserId')),
        'Active_index' => array('columns' => array('Active')),
        'GalleryID_index' => array('columns' => array('GalleryID')),
        'GalleryID_pid_id_index' => array('columns' => array('GalleryID','pid','id')),
        'GalleryID_WpUserId_id_index' => array('columns' => array('GalleryID','WpUserId','id')),
        'GalleryID_Active_pid_index' => array('columns' => array('GalleryID','Active','pid'))
    ),
    $tablename_email => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_email_admin => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_mail_user_upload => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_mail_user_comment => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_mail_user_vote => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_user_comment_mails => array(
        'WpUserId_GalleryID_id_index' => array('columns' => array('WpUserId','GalleryID','id'))
    ),
    $tablename_user_vote_mails => array(
        'WpUserId_GalleryID_id_index' => array('columns' => array('WpUserId','GalleryID','id'))
    ),
    $tablename_comments_notification_options => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_options => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_options_input => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_options_visual => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_entries => array(
        'pid_index' => array('columns' => array('pid')),
        'GalleryID_index' => array('columns' => array('GalleryID')),
        'GalleryID_pid_id_index' => array('columns' => array('GalleryID','pid','id')),
        'pid_f_input_id_index' => array('columns' => array('pid','f_input_id')),
        'GalleryID_f_input_id_pid_index' => array('columns' => array('GalleryID','f_input_id','pid'))
    ),
    $tablename_pro_options => array(
        'GalleryID_index' => array('columns' => array('GalleryID'))
    ),
    $tablename_create_user_entries => array(
        'Field_Type_index' => array('columns' => array('Field_Type')),
        'activation_key_index' => array('columns' => array('activation_key')),
        'wp_user_id_index' => array('columns' => array('wp_user_id')),
        'f_input_id_wp_user_id_index' => array('columns' => array('f_input_id','wp_user_id')),
        'GalleryID_f_input_id_index' => array('columns' => array('GalleryID','f_input_id')),
        'Field_Type_Tstamp_index' => array('columns' => array('Field_Type','Tstamp'))
    ),
    $tablename_create_user_form => array(
        'GeneralID_Active_Field_Order_index' => array('columns' => array('GeneralID','Active','Field_Order')),
        'GeneralID_Field_Type_Field_Order_index' => array('columns' => array('GeneralID','Field_Type','Field_Order')),
        'GalleryID_Active_Field_Order_index' => array('columns' => array('GalleryID','Active','Field_Order')),
        'GalleryID_Field_Type_Field_Order_index' => array('columns' => array('GalleryID','Field_Type','Field_Order'))
    ),
    $tablename_form_input => array(
        'GalleryID_Field_Order_id_index' => array('columns' => array('GalleryID','Field_Order','id')),
        'GalleryID_Field_Type_index' => array('columns' => array('GalleryID','Field_Type')),
        'GalleryID_Active_Field_Order_index' => array('columns' => array('GalleryID','Active','Field_Order'))
    ),
    $tablename_form_output => array(
        'GalleryID_Field_Order_id_index' => array('columns' => array('GalleryID','Field_Order','id')),
        'GalleryID_f_input_id_index' => array('columns' => array('GalleryID','f_input_id'))
    ),
    $tablename_google_users => array(
        'GoogleId_index' => array('columns' => array(array('name' => 'GoogleId','length' => 191))),
        'WpUserId_index' => array('columns' => array('WpUserId'))
    ),
    $tablename_ecommerce_entries => array(
        'pid_index' => array('columns' => array('pid')),
        'GalleryID_pid_index' => array('columns' => array('GalleryID','pid'))
    ),
    $tablename_ecommerce_orders => array(
        'OrderIdHash_index' => array('columns' => array(array('name' => 'OrderIdHash','length' => 64))),
        'IsTest_id_index' => array('columns' => array('IsTest','id')),
        'WpUserId_id_index' => array('columns' => array('WpUserId','id'))
    ),
    $tablename_ecommerce_orders_items => array(
        'ParentOrder_index' => array('columns' => array('ParentOrder')),
        'pid_ParentOrder_index' => array('columns' => array('pid','ParentOrder')),
        'GalleryID_ParentOrder_index' => array('columns' => array('GalleryID','ParentOrder'))
    ),
    $tablename_wp_pdf_previews => array(
        'WpUpload_index' => array('columns' => array('WpUpload')),
        'WpUploadPreview_index' => array('columns' => array('WpUploadPreview'))
    ),
    $tablename_wp_pages => array(
        'WpPage_index' => array('columns' => array('WpPage'))
    )
);
