<?php

if (!function_exists('cg_openai_container')) {
    function cg_openai_container($GalleryID,$cgProFalse)
    {

        $assetsPath = plugins_url() . "/" . cg_get_version() . "/v10/v10-css/backend/assets";
        $assign_fields_png = plugins_url('/../../../../v10/v10-css/assign-fields.png', __FILE__);

        $enterOpenAiKey = '<a href="?page=' . cg_get_version() . '/index.php&option_id=' . $GalleryID . '&edit_options=true&cg_go_to=cgOpenAiKeyRowColumn">Enter OpenAI Api key</a>';

        echo "<div id='cgOpenAiContainer' class='cg_media_container cg_hide' data-cg-gid='$GalleryID'>";
        ?>
        <div id='cgOpenAiSelected' class='cg_openai_main cg_hide'>
            <input type="button" id="cgOpenAiBackToModels" value="Back"
                   style="margin-bottom: 15px;    background-color: white; color: black;">
            <div class="cg_openai_header">
                <b>Selected model</b>
            </div>
            <div id='cgOpenAiModels' style="margin-bottom: 15px;">
                <?php include(__DIR__ . '/cg-openai-gpt-image-1.php'); ?>
                <?php include(__DIR__ . '/cg-openai-dall-e-3.php'); ?>
                <?php include(__DIR__ . '/cg-openai-dall-e-2.php'); ?>
            </div>
            <div id="cgOpenAiPromptContainer">
                <div class='cg_openai_model_desc' style="padding-left: 0; margin-bottom: 15px; margin-top: -10px;">
                    <a href="https://platform.openai.com/settings/organization/billing/overview" target="_blank">Show
                        current balance</a>
                </div>
                <div class="cg_openai_header" style="max-width: 600px;">
                    <b>Enter your request (prompt) for AI</b><br>
                    (recommended language: English)<br>
                    <a id="cgOpenAiShowMoreSupportedLanguages" style="cursor: pointer;">Show more supported
                        languages</a><span id="cgOpenAiSupportedLanguages" class="cg_hide"><b>English:</b> Best performance (most training data),
                    <b>Spanish:</b> Strong performance,
                    <b>French:</b> Strong performance,
                    <b>German:</b> Strong performance,
                    <b>Italian:</b> Good performance,
                    <b>Portuguese:</b> Good performance,
                    <b>Dutch:</b> Good performance,
                    <b>Chinese:</b> (Simplified/Traditional) Good understanding; may vary in image tasks,
                    <b>Japanese:</b> Moderate to good,
                    <b>Korean:</b> Moderate,
                    <b>Russian:</b> Good,
                    <b>Arabic:</b> Moderate to good,
                    <b>Hindi:</b> Basic to moderate</span><br>
                    <a id="cgOpenAiHideMoreSupportedLanguages" class="cg_hide" style="cursor: pointer;">Hide more
                        supported languages</a>
                </div>
                <div class="cg_openai_button_container" style="display: flex; align-items: center;">
                    <div class="cg_clear_container" style="margin-right: 15px;">
                        <textarea id="cgOpenAiPromptInput" rows="5"></textarea>
                        <div title="Clear field" id="cgOpenAiPromptInputClear" class="cg_clear" >
                            &#x274C;
                        </div>
                    </div>
                    <input type="button" id="cgOpenAiPromptSubmit" class="cg_disabled_one" value="Send">
                </div>
                <div id="cgOpenAiGenError" class="cg_hide">
                </div>
            </div>
            <div id="cgOpenAiGenLoaderContainer" class="cg_hide">
                <div class="cg_openai_header">
                    <b>Your prompt is being processed by OpenAI</b>
                    <div class="cg_openai_header_note">
                        Do not cancel
                    </div>
                </div>
                <div id="cgOpenAiGenLoader"
                     class="cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_thumb_view">
                    <div class="cg_skeleton_loader_on_page_load_container">
                        <div class="cg_skeleton_loader_on_page_load" style="width:100%;height:100%;"></div>
                    </div>
                </div>
            </div>
            <div id="cgOpenAiAddToWpLoaderContainer" class="cg_hide">
                <div class="cg_openai_header">
                    <b>Adding to WordPress media library</b>
                    <div class="cg_openai_header_note">
                        Do not cancel
                    </div>
                </div>
                <div id="cgOpenAiAddToWpLoader"
                     class="cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_thumb_view">
                    <div class="cg_skeleton_loader_on_page_load_container">
                        <div class="cg_skeleton_loader_on_page_load" style="width:367.54px;height:23.33px;"></div>
                    </div>
                </div>
            </div>
            <div id="cgOpenAiAddToWpSuccessContainer" class="cg_hide">
                <div class="cg_openai_header">
                    <b id="cgOpenAiAddToWpSuccess">Successful added to WordPress media library</b>
                </div>
            </div>
            <div id="cgOpenAiAddToWpErrorContainer" class="cg_hide">
                <div class="cg_openai_header">
                    <b id="cgOpenAiAddToWpError">Error adding to WordPress media library</b>
                </div>
            </div>
            <div id="cgOpenAiGenImageContainer" class="cg_hide">
                <div class="cg_openai_header">
                    <b>Image generated</b>
                </div>
                <div class="cg_openai_img">
                    <img id="cgOpenAiImg" src="">
                </div>
                <div class="cg_openai_header" style="max-width: 600px;">
                    <b>Enter name for the generated image to add to your WordPress library</b>
                    <div class="cg_openai_header_note">
                        Without image type at the end, .png will be added automatically
                    </div>
                </div>
                <div class="cg_openai_button_container" style="margin-bottom: 15px;">
                    <input type="text" id="cgOpenAiImageName" style="width: 542px;">
                </div>
                <div id="cgOpenAiImageFields" style="display: flex;">
                    <div>
                        <div class="cg_openai_header" style="margin-bottom: 10px;">
                            <b>Assign text to WordPress fields of the new image</b>
                        </div>
                        <div style="display: flex;">
                            <div style="display: flex; flex-flow: column;margin-right: 15px;">
                                <div class="cg_clear_container">
                                    <textarea id="cgOpenAiAltText" rows="4" placeholder="Alternative Text" class="cg_text"></textarea>
                                    <div title="Clear field" id="cgOpenAiAltTextClear" class="cg_clear" >
                                        &#x274C;
                                    </div>
                                </div>
                                <div class="cg_clear_container">
                                    <input id="cgOpenAiTitle" type="text" placeholder="Title"  class="cg_text">
                                    <div title="Clear field" id="cgOpenAiTitleClear" class="cg_clear" >
                                        &#x274C;
                                    </div>
                                </div>
                                <div class="cg_clear_container">
                                    <textarea id="cgOpenAiCaption" rows="4" placeholder="Caption" class="cg_text" ></textarea>
                                    <div title="Clear field" id="cgOpenAiCaptionClear" class="cg_clear" >
                                        &#x274C;
                                    </div>
                                </div>
                                <div class="cg_clear_container">
                                    <textarea id="cgOpenAiDescription" rows="4" placeholder="Description" class="cg_text"></textarea>
                                    <div title="Clear field" id="cgOpenAiDescriptionClear" class="cg_clear">
                                        &#x274C;
                                    </div>
                                </div>
                                <div style="display: flex;gap: 10px; display: none;">
                                    <div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate ">
                                        <div class="cg_image_checkbox_action">Alternative Text</div>
                                        <div class="cg_image_checkbox_icon"></div>
                                    </div>
                                    <div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate ">
                                        <div class="cg_image_checkbox_action">Title</div>
                                        <div class="cg_image_checkbox_icon"></div>
                                    </div>
                                </div>
                                <div style="display: flex;gap: 10px; display: none;">
                                    <div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate ">
                                        <div class="cg_image_checkbox_action">Caption</div>
                                        <div class="cg_image_checkbox_icon"></div>
                                    </div>
                                    <div class="cg_hover_effect cg_image_action_href cg_image_checkbox cg_image_checkbox_deactivate ">
                                        <div class="cg_image_checkbox_action">Description</div>
                                        <div class="cg_image_checkbox_icon"></div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex;">
                                <img src="<?php echo $assign_fields_png;?>" style="width: 400px;" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cg_openai_button_container" style="margin-bottom: 15px;">
                    <input type="button" id="cgOpenAiAddToWpLib" value="Add to your WordPress media library"
                           style="width: auto;" class="cg_disabled_one">
                </div>
                <div class="cg_openai_button_container cg_hide">
                    <input type="button" id="cgOpenAiAddToWpLibAndGallery"
                           value="Add to your WordPress media library and add to gallery" style="width: auto;"
                           class="cg_disabled_one">
                </div>
            </div>
            <div id='cgOpenAiKeyContainer' class='cg_openai_main cg_hide'>
                <div class="cg_openai_header">
                    <b>No OpenAI API key entered</b><br>
                    Get your API key from OpenAI within minutes:<br>
                    <a href="https://platform.openai.com/api-keys" target="_blank">...openai.com/api-keys</a>
                    <br><br>Enter your API key in "Edit options" to connect to your OpenAI account:<br>
                    <?php echo $enterOpenAiKey; ?>
                </div>
                <div class="cg_openai_button_container cg_hide">
                    <input type='text' id='cgOpenAiKeyInput'' > <input type="button" id="cgOpenAiKeySubmit"
                                                                       class="cg_disabled_one" value="Send">
                </div>
                <div id="cgOpenAiKeyError" class="cg_hide">
                </div>
            </div>
        </div>
        <div id='cgOpenAiOverview' class='cg_openai_main cg_hide'>
            <div class="cg_openai_header">
                <b>Image generation models</b><br>
                Models that can generate images, given a natural language prompt.<br>
                Select one of the models offered by OpenAI:
            </div>
            <div id='cgOpenAiModels'>
                <div id="cgOpenAiModelGpt1" class='cg_openai_model' data-ai-model-name="gpt-image-1">
                    <div class='cg_openai_model_image'>
                        <img src="<?php echo $assetsPath; ?>/gpt-image-1.png">
                    </div>
                    <div class='cg_openai_model_desc'>
                        <b>GPT Image 1</b><br>State-of-the-art image generation model
                    </div>
                </div>
                <div id="cgOpenAiModelDallE3" class='cg_openai_model' data-ai-model-name="dall-e-3">
                    <div class='cg_openai_model_image'>
                        <img src="<?php echo $assetsPath; ?>/dall-e-3.png">
                    </div>
                    <div class='cg_openai_model_desc'>
                        <b>DALL·E 3</b><br>Previous generation image generation model
                    </div>
                </div>
                <div id="cgOpenAiModelDallE2" class='cg_openai_model' data-ai-model-name="dall-e-2">
                    <div class='cg_openai_model_image'>
                        <img src="<?php echo $assetsPath; ?>/dall-e-2.png">
                    </div>
                    <div class='cg_openai_model_desc'>
                        <b>DALL·E 2</b><br>First image generation model
                    </div>
                </div>
            </div>
        </div>
        <div id='cgOpenAiKeyNotValid' class='cg_openai_main cg_hide'>
            <div class="cg_openai_header">
                <b>API key not valid</b><br>
                Enter new API key to connect to your OpenAI account.
            </div>
            <div class="cg_openai_button_container">
                <input type='text' id='cgOpenAiKeyInput'' > <input type="button" id="cgOpenAiKeySubmit"
                                                                   class="cg_disabled_one" value="Send">
            </div>
        </div>
        <input type='hidden' name='cgGalleryHash'
               value='<?php echo md5(wp_salt('auth') . '---cngl1---' . $GalleryID); ?>'>
        <input type='hidden' name='GalleryID' value='<?php echo $GalleryID; ?>'>
        <?php
        echo "</div>";

    }
}

?>