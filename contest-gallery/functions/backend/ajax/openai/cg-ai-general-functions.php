<?php
if (!function_exists('cg_ai_insert_prompt')) {
    function cg_ai_insert_prompt($prompt, $cgOpenAiGenIsValid = false, $revisedPrompt = '', $type = 0) {

        global $wpdb;

        $tablename_ai_prompts = $wpdb->prefix . "contest_gal1ery_ai_prompts";
        $cgOpenAiLastPrompt = false;

        //var_dump('$revisedPrompt');
        //var_dump($revisedPrompt);

        if($cgOpenAiGenIsValid){
            $wpdb->query( $wpdb->prepare(
                "
					INSERT INTO $tablename_ai_prompts 
					( id, Prompt, RevisedPrompt, Company, Model, Quality, Resolution, Type, Tstamp)
					VALUES ( %s,%s,%s,%s,%s,%s,%s,%d,%d)
				",
                '',$prompt,$revisedPrompt,'openai',$_POST['cg_ai_model'],$_POST['cg_ai_quality'],$_POST['cg_ai_res'],$type,time()
            ) );
            $insert_id = $wpdb->insert_id;
            $cgOpenAiLastPrompt = $wpdb->get_row("SELECT * FROM $tablename_ai_prompts WHERE id = $insert_id");
            $gmt_offset = get_option('gmt_offset');
            $cgOpenAiLastPrompt->date = cg_get_time_based_on_wp_timezone_conf($cgOpenAiLastPrompt->Tstamp,'d-M-Y H:i:s');
            $cgOpenAiLastPrompt->gmt_offset = $gmt_offset;
            return $cgOpenAiLastPrompt;
        }
        return $cgOpenAiLastPrompt;

    }
}