<?php
if(!defined('ABSPATH')){exit;}

// One schema definition feeds installation, column repair and index repair.
function cg_community_schema($prefix){
	$id = array('id'=>'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT');
	$number = 'BIGINT(20) UNSIGNED NOT NULL DEFAULT 0';
	return array(
		$prefix.'contest_gal1ery_member_threads'=>array('columns'=>array_merge($id,array(
			'UserLow'=>$number,'UserHigh'=>$number,'GalleryID'=>$number,'CreatedAt'=>$number,'UpdatedAt'=>$number,
			'LastMessageId'=>$number,'LowReadId'=>$number,'HighReadId'=>$number,'LowNotified'=>$number,'HighNotified'=>$number
		)),'indexes'=>array('MemberPair'=>array('columns'=>array('UserLow','UserHigh'),'unique'=>true),'LowUpdated'=>array('columns'=>array('UserLow','UpdatedAt','id')),'HighUpdated'=>array('columns'=>array('UserHigh','UpdatedAt','id')))),
		$prefix.'contest_gal1ery_member_messages'=>array('columns'=>array_merge($id,array(
			'ConversationID'=>$number,'SenderID'=>$number,'RecipientID'=>$number,'Body'=>'MEDIUMTEXT NOT NULL','CreatedAt'=>$number,'RequestKey'=>"VARCHAR(64) NOT NULL DEFAULT ''"
		)),'indexes'=>array('SenderRequest'=>array('columns'=>array('SenderID','RequestKey'),'unique'=>true),'ConversationId'=>array('columns'=>array('ConversationID','id')),'RecipientId'=>array('columns'=>array('RecipientID','id')))),
		$prefix.'contest_gal1ery_member_friends'=>array('columns'=>array_merge($id,array(
			'UserLow'=>$number,'UserHigh'=>$number,'RequesterID'=>$number,'GalleryID'=>$number,'Status'=>"VARCHAR(16) NOT NULL DEFAULT 'pending'",'CreatedAt'=>$number,'UpdatedAt'=>$number,'LastRequestedAt'=>$number
		)),'indexes'=>array('MemberPair'=>array('columns'=>array('UserLow','UserHigh'),'unique'=>true),'LowStatus'=>array('columns'=>array('UserLow','Status','id')),'HighStatus'=>array('columns'=>array('UserHigh','Status','id')))),
		$prefix.'contest_gal1ery_member_blocks'=>array('columns'=>array_merge($id,array(
			'BlockerID'=>$number,'BlockedID'=>$number,'CreatedAt'=>$number
		)),'indexes'=>array('BlockPair'=>array('columns'=>array('BlockerID','BlockedID'),'unique'=>true),'BlockedUser'=>array('columns'=>array('BlockedID','BlockerID'))))
	);
}
function cg_community_schema_columns($prefix){
	$result=array();
	foreach(cg_community_schema($prefix) as $table=>$definition){
		foreach($definition['columns'] as $column=>$type){
			// Existing primary keys are created by the required-table installation path.
			if($column==='id'){continue;}
			$parts=explode(' DEFAULT ',$type,2);
			$result[$table][$column]=array('COLUMN_TYPE'=>str_replace(' NOT NULL','',$parts[0]),'DEFAULT'=>isset($parts[1]) ? $parts[1] : null);
		}
	}
	return $result;
}
function cg_community_schema_indexes($prefix){
	$result=array();
	foreach(cg_community_schema($prefix) as $table=>$definition){$result[$table]=$definition['indexes'];}
	return $result;
}
function cg_community_create_tables($prefix,$existing){
	global $wpdb;
	require_once ABSPATH.'wp-admin/includes/upgrade.php';
	foreach(cg_community_schema($prefix) as $table=>$definition){
		if(isset($existing[$table])){continue;}
		$lines=array();
		foreach($definition['columns'] as $name=>$type){$lines[]=$name.' '.$type;}
		$lines[]='PRIMARY KEY  (id)';
		foreach($definition['indexes'] as $name=>$index){$lines[]=(!empty($index['unique']) ? 'UNIQUE KEY ' : 'KEY ').$name.' ('.implode(',',$index['columns']).')';}
		dbDelta('CREATE TABLE '.$table." (\n".implode(",\n",$lines)."\n) ENGINE=InnoDB ".$wpdb->get_charset_collate().';');
	}
}
