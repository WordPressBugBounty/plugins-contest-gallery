<?php

if (!defined('ABSPATH')) { exit; }

if (!function_exists('cg_openai_models')) {
    function cg_openai_models($overview) {
        $isFree = cg_get_version() !== 'contest-gallery-pro';
        foreach (cg_ai_image_models() as $model => $config) {
            if ($overview) {
                ?>
                <button type="button" class="cg_openai_model cg_openai_model_card" data-ai-model-name="<?php echo esc_attr($model); ?>">
                    <span class="cg_openai_model_badge" aria-hidden="true"><?php echo esc_html($config['badge']); ?></span>
                    <span class="cg_openai_model_desc"><b><?php echo esc_html($config['name']); ?></b><br><?php echo esc_html($config['description']); ?></span>
                </button>
                <?php
                continue;
            }
            ?>
            <div class="cg_openai_model cg_hide" data-ai-model-name="<?php echo esc_attr($model); ?>">
                <div class="cg_openai_model_desc">
                    <b><?php echo esc_html($config['name']); ?></b><br>
                    <?php echo esc_html($config['description']); ?><br>
                    <a href="<?php echo esc_url('https://developers.openai.com/api/docs/models/' . $model); ?>" target="_blank" rel="noopener noreferrer">Model details</a>
                </div>
                <div class="cg_openai_model_controls">
                    <label>Quality
                        <select class="cg_openai_quality">
                            <?php foreach ($config['qualities'] as $quality => $label) { ?>
                                <option value="<?php echo esc_attr($quality); ?>" data-cg-label="<?php echo esc_attr($label); ?>" data-cg-pro-edit="<?php echo !in_array($quality, array('low', 'medium'), true) ? '1' : '0'; ?>" <?php selected($quality, 'medium'); ?>><?php echo esc_html($label); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label class="cg_openai_create_desc">Image size
                        <select class="cg_openai_size">
                            <?php foreach (cg_ai_image_sizes() as $size => $label) {
                                $requiresPro = $isFree && $size !== '1024x1024'; ?>
                                <option value="<?php echo esc_attr($size); ?>" <?php disabled($requiresPro); ?>><?php echo esc_html($label . ($requiresPro ? ' — PRO' : '')); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <p class="cg_openai_edit_desc cg_hide">Image size is chosen automatically when editing.</p>
                </div>
                <?php if ($isFree) { ?>
                    <p class="cg_openai_edit_desc cg_openai_quality_note cg_hide">High and higher editing quality levels are available in Contest Gallery PRO.</p>
                <?php } ?>
            </div>
            <?php
        }
    }
}


if (!function_exists('cg_openai_container')) {
    function cg_openai_container($GalleryID,$cgProFalse)
    {

        $assign_fields_png = plugins_url('/../../../../v10/v10-css/assign-fields.png', __FILE__);

        if(cg_user_can_manage_global_settings()){
            $openAiKeySettingsMessage = '<a href="?page=' . cg_get_version() . '/index.php&option_id=' . absint($GalleryID) . '&edit_options=true&cg_go_to=cgOpenAiKeyRowColumn">Open the OpenAI settings</a>';
        }else{
            $openAiKeySettingsMessage = 'Please contact an administrator to configure the OpenAI API key.';
        }

        ?>
        <div id="cgOpenAiSelectToEditTemplate" class="cg_hide">
            <div id="cgOpenAiSelectToEdit">
                <input class="cg_hide" type="button" id="cgOpenAiEditSelectedImages" value="Edit previously selected image">
                Select a JPG or PNG image to edit with OpenAI.
            </div>
        </div>
        <?php
        echo "<div id='cgOpenAiContainer' class='cg_media_container cg_hide' data-cg-free='" . (cg_get_version() !== 'contest-gallery-pro' ? '1' : '0') . "' data-cg-gid='$GalleryID'>";
        ?>
        <div id='cgOpenAiPrompts' class='cg_openai_main' >
            <div class="cg_openai_header" style="text-align: center;">
                <b>Last successful prompts to OpenAI</b>
            </div>
            <div id='cgOpenAiPromptsContainer' class='cg_hide' >
                <div id='cgOpenAiPromptsNotEntered'  class="cg_openai_model_desc cg_hide">
                    No prompts entered so far
                </div>
                <div id='cgOpenAiPromptsEntered'  class="cg_hide">
                    <div id="cgOpenAiShowMoreLoader" style="padding-left: 0;"
                         class="cg_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_thumb_view">
                        <div class="cg_skeleton_loader_on_page_load_container" style="width: 100%;">
                            <div class="cg_skeleton_loader_on_page_load" style="width:25%;height:14px;margin-bottom: 10px;"></div>
                            <div class="cg_skeleton_loader_on_page_load" style="width:50%;height:22px;"></div>
                        </div>
                    </div>
                    <input type="button" id="cgOpenAiMorePrompts" value="Show more prompts"
                           style="margin-bottom: 15px; margin-top: 15px; background-color: white; color: black;width: auto; left: 50%; transform: translateX(-50%);  position: relative; margin-left: 18px;">
                </div>
            </div>
            <div id='cgOpenAiShowMore' title="Show more" >

            </div>
            <div id='cgOpenAiShowLess' title="Show less" class="cg_hide" >

            </div>
        </div>
        <div class="cg_openai_edit_sources cg_hide">
            <div class="cg_openai_header"><b>Selected image to edit</b></div>
            <div id="cgOpenAiEditImages"></div>
            <input type="button" id="cgOpenAiEditSelectImages" value="Select another image">
        </div>
        <div id='cgOpenAiSelected' class='cg_openai_main cg_hide'>
            <input type="button" id="cgOpenAiBackToModels" value="Change model">
            <div class="cg_openai_header">
                <b>Selected model</b>
            </div>
            <div class="cg_openai_models cg_openai_selected_models">
                <?php cg_openai_models(false); ?>
            </div>
            <p class="cg_openai_quality_changed cg_hide" role="status">Quality was reset to Standard because the previous setting is unavailable for this model or editing mode.</p>
            <p class="cg_openai_cost_note">OpenAI charges separately for image requests. Costs depend on the model, image size, quality and input images.
                <a href="https://developers.openai.com/api/docs/pricing#image-generation" target="_blank" rel="noopener noreferrer">View current OpenAI pricing</a>
            </p>
            <div id="cgOpenAiPromptContainer">
                <div id="cgOpenAiPromptShowCurrentBalance" class='cg_openai_model_desc' style="padding-left: 0; margin-bottom: 15px; margin-top: -10px;">
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
                        <div title="Clear field" id="cgOpenAiPromptInputClear" class="cg_hide cg_clear" >
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
                    <br><br>
                    <?php echo $openAiKeySettingsMessage; ?>
                </div>
                <div id="cgOpenAiKeyError" class="cg_hide">
                </div>
            </div>
        </div>
        <div id='cgOpenAiOverview' class='cg_openai_main cg_hide'>
            <div class="cg_openai_header">
                <b>Image models</b><br>
                Choose a model to generate or edit an image.
            </div>
            <div class="cg_openai_models cg_openai_model_overview">
                <?php cg_openai_models(true); ?>
            </div>
        </div>
        <div id='cgOpenAiKeyNotValid' class='cg_openai_main cg_hide'>
            <div class="cg_openai_header">
                <b>API key not valid</b><br>
                <?php echo $openAiKeySettingsMessage; ?>
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
