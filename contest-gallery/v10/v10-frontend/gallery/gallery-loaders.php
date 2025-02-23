<?php
if(!isset($isOnlyContactForm)){$isOnlyContactForm='';}
if(!isset($mainCGdivShowUncollapsed)){$mainCGdivShowUncollapsed='';}
if(!isset($entryId)){$entryId='';}
if(!isset($currentLook)){$currentLook='';}
if(!isset($cgFeControlsStyle)){$cgFeControlsStyle='';}
if(!isset($BorderRadiusClass)){$BorderRadiusClass='';}

// $cg_skeleton_loader_on_page_load_div_hide = 'cg_hide'; is used for cg_galleries click to go to gallery and back to galleries load

if(empty($cg_skeleton_loader_on_page_load_div_hide)){
	echo "<div class='mainCGdivHelperParent mainCGdivHelperParentSkeletonLoader cg_display_block $cgFeControlsStyle'>";
	echo "<div class='mainCGdiv    $cgFeControlsStyle $BorderRadiusClass' >";
}


if($isOnlyContactForm){
	if($mainCGdivShowUncollapsed){
		echo "<div class='$cg_skeleton_loader_on_page_load_div_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_uncollapsed'>";
		echo "<div class='cg_skeleton_loader_on_page_load_container'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:220px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:150px;'></div>";
		echo "</div>";
		echo "</div>";
	}else{
		echo "<div class='$cg_skeleton_loader_on_page_load_div_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_form_collapsed'>";
		echo "<div class='cg_skeleton_loader_on_page_load_container'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:60px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:25%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:50%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:75%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:100%;'></div>";
		echo "</div>";
		echo "</div>";
	}
}else{

    if(empty($currentLook)){// so some loader will be loaded for sure
	    $currentLook='thumb';
    }

	if(!empty($entryId) OR $currentLook=='blog' OR $currentLook=='slider'){
        // cgCenterDiv max content width is 930px
		echo "<div class='$cg_skeleton_loader_on_page_load_div_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_blog_and_slider_view' style='max-width: 930px;'>";
		echo "<div class='cg_skeleton_loader_on_page_load_container'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:60px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:400px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:25%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:50%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:75%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:30px;width:100%;'></div>";
		echo "</div>";
		echo "</div>";
	}else if($currentLook=='thumb'){
		echo "<div class='$cg_skeleton_loader_on_page_load_div_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_thumb_view cg_display_flex cg_flex_flow_column '>";
		echo "<div class='cg_skeleton_loader_on_page_load_container'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:60px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_display_flex cg_justify_content_space_between'>";
		echo "<div class='cg_skeleton_loader_on_page_load_container cg_container cg_display_flex cg_flex_flow_column' style='width:32.00%;' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container cg_container cg_display_flex cg_flex_flow_column'  style='width:32.00%;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container cg_container cg_display_flex cg_flex_flow_column'  style='width:32.00%;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:300px;width: 100%;margin-bottom: 30px;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:600px;width: 100%;margin-bottom: 30px;'></div>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
	}else if($currentLook=='height'){
		echo "<div class='$cg_skeleton_loader_on_page_load_div_hide cg_skeleton_loader_on_page_load_div cg_skeleton_loader_on_page_load_div_height_view'>";
		echo "<div class='cg_skeleton_loader_on_page_load_container'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:60px;width:100%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 25%;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 74%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' style='margin-bottom: 10px;'>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 74%;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 25%;'></div>";
		echo "</div>";
		echo "<div class='cg_skeleton_loader_on_page_load_container' >";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 25%;'></div>";
		echo "<div class='cg_skeleton_loader_on_page_load' style='height:250px;width: 74%;'></div>";
		echo "</div>";
		echo "</div>";
	}
}

if(empty($cg_skeleton_loader_on_page_load_div_hide)){
	echo "</div>";
	echo "</div>";
}


?>
<script>
    console.log('console.log test loader load');
    debugger
</script>
