<?php

?>
<div id="cgOpenAiModelDallE3" class='cg_openai_model '  data-ai-model-name="dall-e-3" >
    <div class='cg_openai_model_image' >
        <img src="<?php echo $assetsPath;?>/dall-e-3.png" >
    </div>
    <div class='cg_openai_model_desc' >
        <b>DALL·E 3</b><br>Previous generation image generation model.<br>
        Offers "standard" quality.<br>
        <a href="https://platform.openai.com/docs/models/dall-e-3" target="_blank">Details</a>
    </div>
    <div class="cg_openai_resolutions_container" >
        <div class="cg_openai_header" >
            <b>Select resolution</b><br>
            <!--<div class="cg_openai_header_note" >
                (current prices and resolutions may vary)
            </div>-->
        </div>
        <div  class="cg_openai_resolutions" >
            <div class="cg_openai_res cg_selected" >
                <div class="cg_openai_res_size" data-cg-res="1024x1024">
                    1024x1024
                </div>
                <div class="cg_openai_res_price" >
                    $0.04
                </div>
            </div>
            <div class="cg_openai_res <?php echo $cgProFalse;?>" >
                <div class="cg_openai_res_size" data-cg-res="1024x1792" data-cg-orientation="vertical"  >
                    1024x1792 (vertical)
                </div>
                <div class="cg_openai_res_price" >
                    $0.08
                </div>
            </div>
            <div class="cg_openai_res <?php echo $cgProFalse;?>" >
                <div class="cg_openai_res_size" data-cg-res="1792x1024" data-cg-orientation="horizontal" >
                    1792x1024 (horizontal)
                </div>
                <div class="cg_openai_res_price" >
                    $0.08
                </div>
            </div>
        </div>
    </div>
</div>
<?php

?>

