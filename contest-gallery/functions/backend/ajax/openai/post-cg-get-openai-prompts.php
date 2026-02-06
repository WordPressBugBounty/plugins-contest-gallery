<?php

// post_cg_get_openai_prompts
add_action('wp_ajax_post_cg_get_openai_prompts', 'post_cg_get_openai_prompts');
if (!function_exists('post_cg_get_openai_prompts')) {
    function post_cg_get_openai_prompts() {

        global $wpdb;
        $tablename_ai_prompts = $wpdb->prefix . "contest_gal1ery_ai_prompts";

        $start = 0;
        $isFirst = true;

        if(!empty($_POST['cg_start'])) {
            $start = absint($_POST['cg_start']);
        }

        if(!empty($_POST['cg_start']) && $_POST['cg_start'] > 0) {
            $isFirst = false;
        }

        $prompts = $wpdb->get_results("SELECT * FROM $tablename_ai_prompts WHERE id > 0 ORDER BY id DESC LIMIT $start, 100");

       $start = $start+100;
       // $end = $start+2;

        //var_dump('$start');
        //var_dump($start);

        $morePrompts = count($wpdb->get_results("SELECT * FROM $tablename_ai_prompts WHERE id > 0 LIMIT $start, 100"));

        //var_dump('$morePrompts');
        //var_dump($morePrompts);

        $gmt_offset = get_option('gmt_offset');

        // convert WordPress time
        foreach($prompts as $key => $prompt){
            $prompt->date = cg_get_time_based_on_wp_timezone_conf($prompt->Tstamp,'d-M-Y H:i:s');
            $prompt->gmt_offset = $gmt_offset;
            $prompts[$key] = $prompt;
        }

        ?>
        <script data-cg-processing="true">
            cgJsClassAdmin.gallery.vars.openAiIsFirstGet = <?php echo json_encode($isFirst); ?>;
            cgJsClassAdmin.gallery.vars.openAiMorePrompts = <?php echo json_encode($prompts); ?>;
            cgJsClassAdmin.gallery.vars.openAiMorePromptsCount = <?php echo json_encode($morePrompts); ?>;

            if(cgJsClassAdmin.gallery.vars.openAiIsFirstGet){
                cgJsClassAdmin.gallery.vars.openAiPrompts = <?php echo json_encode($prompts); ?>;
            }else{
                cgJsClassAdmin.gallery.vars.openAiMorePrompts.forEach(function (prompt){
                    cgJsClassAdmin.gallery.vars.openAiPrompts.push(prompt);
                });

                if(!cgJsClassAdmin.gallery.vars.openAiMorePromptsCount){
                    cgJsClassAdmin.gallery.vars.openAiPromptsAllGot = true;
                }
            }
        </script>
        <?php

    }
}
