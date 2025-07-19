<?php

?>
<div id="cgOpenAiModelDallE2"  class='cg_openai_model '  data-ai-model-name="dall-e-2"  >
    <div class='cg_openai_model_image' >
        <img src="<?php echo $assetsPath;?>/dall-e-2.png" >
    </div>
    <div class='cg_openai_model_desc' >
        <b>DALL·E 2</b><br>First image generation model.<br>
        Offers "standard" quality.<br>
        <a href="https://platform.openai.com/docs/models/dall-e-2" target="_blank">Details</a>
        <span class="cg_openai_create_desc" style="margin-bottom: 10px; display: block;">
            Creating an image by OpenAI: <a target="_blank" href="https://www.contest-gallery.com/openai-create-image/">examples</a>
        </span>
    </div>
    <div class="cg_openai_resolutions_container" >
        <div class="cg_openai_header" >
            <b>Select resolution</b><br>
            <!--<div class="cg_openai_header_note" >
                (current prices may vary)
            </div>-->
        </div>
        <div  class="cg_openai_resolutions" >
            <div class="cg_openai_res cg_selected" >
                <div class="cg_openai_res_size"  data-cg-res="256x256" >
                    256x256
                </div>
                <div class="cg_openai_res_price" >
                    $0.016
                </div>
            </div>
            <div class="cg_openai_res" >
                <div class="cg_openai_res_size" data-cg-res="512x512"  >
                    512x512
                </div>
                <div class="cg_openai_res_price" >
                    $0.018
                </div>
            </div>
            <div class="cg_openai_res" >
                <div class="cg_openai_res_size" data-cg-res="1024x1024"  >
                    1024x1024
                </div>
                <div class="cg_openai_res_price" >
                    $0.02
                </div>
            </div>
        </div>
    </div>
</div>
<?php

?>