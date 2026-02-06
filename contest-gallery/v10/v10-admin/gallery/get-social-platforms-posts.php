<?php

global $wpdb;
$table_posts = $wpdb->prefix."posts";

$start = 0;
$step = 100;

if(!empty($_POST['cg_start'])){
	$start = absint($_POST['cg_start']);
}

$posts = $wpdb->get_results( "SELECT * FROM $table_posts WHERE post_mime_type = 'contest-gallery-youtube' || post_mime_type = 'contest-gallery-twitter' || post_mime_type = 'contest-gallery-instagram' || post_mime_type = 'contest-gallery-tiktok'  ORDER BY ID DESC LIMIT $start, $step");

$postsCount = $wpdb->get_var( "SELECT COUNT(*) as NumberOfRows FROM $table_posts WHERE post_mime_type = 'contest-gallery-youtube' || post_mime_type = 'contest-gallery-twitter' || post_mime_type = 'contest-gallery-instagram' || post_mime_type = 'contest-gallery-tiktok'");

foreach ($posts as $post){
    if($post->post_mime_type=='contest-gallery-twitter' || $post->post_mime_type=='contest-gallery-tiktok'){
	    $post->blockquote = cg_get_blockquote_from_post_content($post->post_content);
    }
}

$youtubePostsShowMore = false;
if($postsCount>($start+$step)){
	$youtubePostsShowMore = true;
}

?>
<script data-cg-processing="true">
    cgJsClassAdmin.gallery.vars.youtubePostsShowMore = <?php echo json_encode($youtubePostsShowMore); ?>;
    cgJsClassAdmin.gallery.vars.youtubePostsCount = <?php echo json_encode($postsCount); ?>;
    cgJsClassAdmin.gallery.vars.youtubePostsStep = <?php echo json_encode(($start+$step)); ?>;
    cgJsClassAdmin.gallery.vars.youtubePosts = <?php echo json_encode($posts); ?>;
</script>

