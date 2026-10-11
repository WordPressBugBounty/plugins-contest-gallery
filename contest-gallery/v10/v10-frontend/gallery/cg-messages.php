<?php

$cgMessageCloseLabel = esc_attr(cg_member_t('Close'));

$heredoc = <<<HEREDOC

<div id="cgMessagesContainer" class="cg_hide cg_messages_container $cgFeControlsStyle $BorderRadiusClass">
   <div id="cgMessagesDiv">
       <div id="cgMessagesClose" aria-label="$cgMessageCloseLabel" title="$cgMessageCloseLabel">
   
        </div>
       <div id="cgMessagesContent">
            Photo contest ist over
        </div>
    </div>
</div>

HEREDOC;

echo $heredoc;

if(!isset($cgl_heart)){
    $cgl_heart = '';
}

$heredoc = <<<HEREDOC
<div id="cgMessagesContainerPro" class="cg_hide cg_messages_container_pro $cgFeControlsStyle $BorderRadiusClass $cgl_heart">
   <div id="cgMessagesDiv">
       <div id="cgMessagesCloseProContainer">
           <div id="cgMessagesClose" aria-label="$cgMessageCloseLabel" title="$cgMessageCloseLabel">
            </div>
       </div>
       <div id="cgMessagesContent">
            Photo contest ist over
        </div>
    </div>
</div>

HEREDOC;

echo $heredoc;



?>
