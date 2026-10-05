<?php
if(!defined('ABSPATH')){exit;}

// Called while holding the existing user/action lock. SQL rollback does not undo
// MyISAM writes, so remember only the counters and audit rows this operation owns.
function cg_paid_actions_recovery_start($user,$action,$token,$objectIds = array()){
    global $wpdb;
    $state=array('rows'=>array(),'ledger'=>false,'ledgerIds'=>array(),'ledgerWhere'=>'');
    $tables=array('entitlements'=>cg_paid_actions_table('entitlements'),'ledger'=>cg_paid_actions_table('ledger'));
    $objectIds=array_values(array_filter(array_map('absint',(array)$objectIds)));
    if($objectIds && cg_paid_actions_action_table($action)!==''){$tables['objects']=cg_paid_actions_action_table($action);}
    foreach($tables as $kind=>$table){
        $status=$wpdb->get_row($wpdb->prepare('SHOW TABLE STATUS WHERE Name=%s',$table));
        if($status && strtolower($status->Engine)==='innodb'){continue;}
        if($kind==='ledger'){
            $patterns=array();foreach(array('reserve','consume','release') as $event){$patterns[]=$wpdb->prepare('IdempotencyKey LIKE %s',$wpdb->esc_like($event.':'.$token.':').'%');}
            $state['ledger']=$table;$state['ledgerWhere']='('.implode(' OR ',$patterns).')';
            $state['ledgerIds']=$wpdb->get_col('SELECT id FROM '.$table.' WHERE '.$state['ledgerWhere']);
        }else{
            if($kind==='entitlements'){
                $columns='id,Reserved,Consumed,UpdatedAt';
                $where=$wpdb->prepare('WpUserId=%d AND ActionType=%s',$user,$action);
            }else{
                $columns='id,PaidActionSource,PaidActionPriceUnitGross,PaidActionCurrencyShort,PaidActionCurrencyPosition,PaidActionPriceDivider,PaidActionParentOrder,PaidActionOrderItem';
                $where='id IN ('.implode(',',$objectIds).')';
            }
            $state['rows'][$table]=$wpdb->get_results('SELECT '.$columns.' FROM '.$table.' WHERE '.$where,ARRAY_A);
        }
        if($wpdb->last_error){return new WP_Error('cg_paid_action_storage',cg_paid_actions_t('CreditsReserveFailed'));}
    }
    if($state['rows'] || $state['ledger']){
        $state['key']=wp_generate_uuid4();
        $GLOBALS['cgPaidActionRecovery'][$state['key']]=$state;
    }
    return $state;
}

function cg_paid_actions_recovery_transaction($sql,&$state){
    global $wpdb;
    $result=cg_upload_job_db_transaction($sql);
    if($sql==='COMMIT' && $result!==false){if(!empty($state['key'])){unset($GLOBALS['cgPaidActionRecovery'][$state['key']]);}$state=null;return $result;}
    if($sql!=='ROLLBACK' && !($sql==='COMMIT' && $result===false)){return $result;}
    if($sql==='COMMIT'){cg_upload_job_db_transaction('ROLLBACK');}
    $restored=true;
    if(is_array($state)){
        if($state['ledger']){
            $except=$state['ledgerIds'] ? ' AND id NOT IN ('.implode(',',array_map('absint',$state['ledgerIds'])).')' : '';
            if($wpdb->query('DELETE FROM '.$state['ledger'].' WHERE '.$state['ledgerWhere'].$except)===false){$restored=false;}
        }
        foreach($state['rows'] as $table=>$rows){
            foreach($rows as $row){$id=$row['id'];unset($row['id']);if($wpdb->update($table,$row,array('id'=>$id))===false){$restored=false;}}
        }
    }
    if(!empty($state['key'])){unset($GLOBALS['cgPaidActionRecovery'][$state['key']]);}
    $state=null;
    if(!$restored){error_log('Contest Gallery: failed to restore an upload credit operation. Administrator review is required.');}
    return $result!==false && $restored ? $result : false;
}

// Register before reservation-release handlers. A PHP fatal during a counter or
// ledger write must be compensated before shutdown retries pending settlement.
function cg_paid_actions_recovery_shutdown(){
    if(empty($GLOBALS['cgPaidActionRecovery'])){return;}
    foreach(array_reverse($GLOBALS['cgPaidActionRecovery']) as $state){cg_paid_actions_recovery_transaction('ROLLBACK',$state);}
}
register_shutdown_function('cg_paid_actions_recovery_shutdown');
