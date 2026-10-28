<?php

if(!function_exists('cg_social_input_container')){
    function cg_social_input_container($GalleryID){
        $examples = array(
            array('YouTube', 'video-alt3', 'Use a video link. You can include a start time.', 'https://www.youtube.com/watch?v=RG9TMn1FJzc'),
            array('Instagram', 'instagram', 'Copy the link to a public post or reel.', 'https://www.instagram.com/p/C9fYTSPyPfp/'),
            array('TikTok', 'format-video', 'Use the full video link from the address bar.', 'https://www.tiktok.com/@scout2015/video/6718335390845095173'),
            array('X (Twitter)', 'twitter', 'Copy the link to an individual public post.', 'https://x.com/Interior/status/463440424141459456')
        );
        ?>
        <div id="cgAddYoutubeContainer" class="cg_media_container cg_social_embed_panel cg_hide" data-cg-gid="<?php echo absint($GalleryID); ?>">
            <div class="cg_social_embed_inner">
                <header class="cg_social_embed_header">
                    <span class="cg_social_embed_eyebrow"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span> SOCIAL MEDIA</span>
                    <h2 tabindex="-1">Add social embed</h2>
                    <p>Bring a social post into your gallery. Paste its public link to see a preview.</p>
                </header>
                <div class="cg_social_embed_layout">
                    <div class="cg_social_embed_controls">
                        <form id="cgAddYoutubeForm" class="cg_social_embed_card" novalidate>
                            <label for="cgAddYoutubeInput">Post URL</label>
                            <div class="cg_social_embed_input_wrap"><span class="dashicons dashicons-admin-links" aria-hidden="true"></span><input type="url" id="cgAddYoutubeInput" class="cg_social_embed_url" name="source_url" placeholder="https://www.youtube.com/watch?v=…" autocomplete="off" spellcheck="false" aria-describedby="cgSocialUrlHelp"></div>
                            <p id="cgSocialUrlHelp" class="cg_social_embed_help">Paste a link to one post or video. The platform is detected automatically.</p>
                            <div class="cg_social_embed_actions">
                                <button type="submit" id="cgAddYoutubeSubmit" class="cg_social_embed_button cg_social_embed_primary" disabled><span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span><span class="cg_social_embed_submit_label">Add to gallery</span></button>
                                <button type="button" class="cg_social_embed_button cg_social_embed_save" disabled><span class="dashicons dashicons-portfolio" aria-hidden="true"></span>Save to library</button>
                            </div>
                            <p class="cg_social_embed_destination">Adding to the gallery also saves the post in your social embed library.</p>
                            <div class="cg_social_embed_status" role="status" aria-live="polite" hidden></div>
                            <button type="button" class="cg_social_embed_button cg_social_embed_retry" hidden><span class="dashicons dashicons-update" aria-hidden="true"></span>Retry preview</button>
                            <button type="button" class="cg_social_embed_button cg_social_embed_reload" hidden><span class="dashicons dashicons-update" aria-hidden="true"></span>Reload gallery</button>
                            <button type="button" class="cg_social_embed_button cg_social_embed_open_library" hidden><span class="dashicons dashicons-portfolio" aria-hidden="true"></span>Open social embed library</button>
                        </form>
                        <section class="cg_social_embed_examples" aria-label="Supported platforms and example URLs">
                            <div class="cg_social_embed_section_title"><h3>Try an example</h3><span>4 supported platforms</span></div>
                            <?php foreach($examples as $example){ ?>
                                <article class="cg_social_embed_example cg_social_embed_card">
                                    <div class="cg_social_embed_example_heading"><span class="cg_social_embed_platform_icon"><span class="dashicons dashicons-<?php echo esc_attr($example[1]); ?>" aria-hidden="true"></span></span><div><h4><?php echo esc_html($example[0]); ?></h4><p><?php echo esc_html($example[2]); ?></p></div></div>
                                    <div class="cg_social_embed_example_url"><code><?php echo esc_html($example[3]); ?></code></div>
                                    <div class="cg_social_embed_example_actions">
                                        <button type="button" class="cg_social_embed_copy" data-cg-url="<?php echo esc_attr($example[3]); ?>" aria-label="<?php echo esc_attr('Copy '.$example[0].' example URL'); ?>"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span>Copy URL</span></button>
                                        <button type="button" class="cg_social_embed_use" data-cg-url="<?php echo esc_attr($example[3]); ?>">Use example<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></button>
                                    </div>
                                    <?php if($example[0]==='YouTube'){ $timed = $example[3].'&t=1m15s'; ?>
                                        <div class="cg_social_embed_start_time"><span class="dashicons dashicons-clock" aria-hidden="true"></span><span>Start at 1:15 <code>&amp;t=1m15s</code></span><button type="button" class="cg_social_embed_copy" data-cg-url="<?php echo esc_attr($timed); ?>" aria-label="Copy YouTube URL starting at 1:15"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span><span>Copy URL</span></button><button type="button" class="cg_social_embed_use" data-cg-url="<?php echo esc_attr($timed); ?>">Use</button></div>
                                    <?php } ?>
                                </article>
                            <?php } ?>
                            <p class="cg_social_embed_copy_status" role="status" aria-live="polite"></p>
                        </section>
                    </div>
                    <section class="cg_social_embed_card cg_social_embed_preview_card" aria-label="Post preview">
                        <div class="cg_social_embed_preview_header"><h3>Preview</h3><span class="cg_social_embed_provider" hidden></span></div>
                        <div class="cg_social_embed_empty"><span class="dashicons dashicons-format-video" aria-hidden="true"></span><h4>Your post will appear here</h4><p>Paste a link or choose an example to load the preview.</p></div>
                        <div class="cg_social_embed_loading" role="status" hidden><div class="cg_social_embed_skeleton"></div><p>Loading preview…</p></div>
                        <div id="cgAddYoutubePreview" hidden></div>
                        <div class="cg_social_embed_preview_caption" hidden><h4></h4><p></p></div>
                        <p class="cg_social_embed_preview_note">The preview connects to the selected platform. Private, removed or restricted posts may not be available.</p>
                    </section>
                </div>
            </div>
        </div>
        <?php
    }
}

if(!function_exists('cg_social_library')){
	function cg_social_library($GalleryID){

		echo "<div id='cgYoutubeLibraryContainer' class='cg_media_container cg_hide' data-cg-gid='$GalleryID'>";
		    echo "<div id='cgYoutubeNoEntries' class='cg_hide'>No social embed entries added</div>";
		    echo "<div id='cgYoutubeLibraryLoader' class='cg-lds-dual-ring-gallery-hide cg_hide'></div>";
		?>
            <form enctype="multipart/form-data" id="cgAddYoutubeToGalleryForm"  action='<?php echo '?page="'.cg_get_version().'"/index.php'; ?>' method='POST'>
                <input type='hidden' name='cgGalleryHash' value='<?php echo md5(wp_salt( 'auth').'---cngl1---'.$GalleryID);?>'>
                <input type='hidden' name='GalleryID' value='<?php echo $GalleryID;?>'>
                <input type='hidden' name='action' value='post_cg_social_platforms_add_to_gallery'>
            </form>
		<?php
		echo "</div>";
		echo "<div id='cgYoutubeLibraryFooter' class='cg_media_container cg_hide' >";
            echo '<input type="button" id="cgDeleteYoutubeLibraryButton" class="cg_disabled_one" value="Delete selected from library">';
            echo '<input type="button" id="cgAddYoutubeLibraryButton" class="cg_disabled_one" value="Add selected to gallery">';
		echo "</div>";

	}
}


?>