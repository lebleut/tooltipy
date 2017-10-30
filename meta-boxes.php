<?php
defined('ABSPATH') or die("No script kiddies please!");

/*place metabox after the Title*/ 
add_action('edit_form_after_title', function() {
    global $post, $wp_meta_boxes,$post_type;
    
	do_meta_boxes($post_type,'after_title',$post);
	//echo("<pre>");print_r($post_type);echo("</pre>");
	
});
/**/

add_action('do_meta_boxes',function(){
	global $tooltipy_post_type_name;
//for keywords
		add_meta_box(
			'bluet_kw_settings_meta',
			__('Keyword Settings','bluet-kw'),
			'bluet_keyword_settings_render',
			$tooltipy_post_type_name,
			'after_title',
			'high'
		);
		
//for post types except my_keywords
	$screens = array();
	$all_post_types=get_post_types();
	foreach ($all_post_types as $key => $pt) {
		if($pt!=$tooltipy_post_type_name){
			array_push($screens,$pt);
		}
	}

	foreach ( $screens as $screen ) {
		//related keywords
		add_meta_box(
			'bluet_kw_post_related_keywords_meta',
			__('Keywords related','bluet-kw').' (KTTG)',
			'bluet_keywords_related_render',
			$screen,
			'side',
			'high'
		);
	}
		
});

function bluet_keyword_settings_render(){
	?>
	<p>
		<Label for="bluet_synonyms_id"><?php _e('Synonyms','bluet-kw');?></label>
		<input type="text" 
			id="bluet_synonyms_id" 
			name="bluet_synonyms_name" 
			value="<?php echo(get_post_meta(get_the_id(),'bluet_synonyms_keywords',true));?>" 
			placeholder="<?php _e("Type here the keyword's Synonyms separated with '|'","bluet-kw");?>" 
			style=" width:100%;" 
		/>
	</p>
	
	<p>
		<label for='bluet_case_sensitive_id'><?php _e('Make this keyword <b>Case Sensitive</b>','bluet-kw');?>  </label>
		<input type="checkbox" 
			id="bluet_case_sensitive_id" 
			name="bluet_case_sensitive_name" <?php if(get_post_meta(get_the_id(),'bluet_case_sensitive_word',true)) echo('checked');?> 
		/>
	</p>
	<?php
	if(function_exists('bluet_prefix_metabox')){
		bluet_prefix_metabox();
	}
	
	if(function_exists('bluet_video_metabox')){
		bluet_video_metabox();
	}
	
	/*for a specific use icon extention*/
	if(function_exists('bluet_icon_metabox')){
		bluet_icon_metabox();
	}
	
	
}

function bluet_keywords_related_render(){
//exclude checkbox to exclode the current post from being matched
	global $post, $tooltipy_post_type_name;

	$current_post_id=$post->ID;
	$exclude_me = get_post_meta($current_post_id,'bluet_exclude_post_from_matching',true);
	$exclude_keywords_string = get_post_meta($current_post_id,'bluet_exclude_keywords_from_matching',true);

	//get excluded terms and sanitize them
	/*begin*/
		$my_excluded_keywords=explode(',',$exclude_keywords_string);
		$my_excluded_keywords=array_map('trim',$my_excluded_keywords);
		$my_excluded_keywords=array_map('strtolower',$my_excluded_keywords);
		
		$my_excluded_keywords=array_filter($my_excluded_keywords,function($val){
			$ret=array();
			if($val!=""){
				array_push($ret,$val);
			}
			return $ret;
		});
	/*end*/

	?>
	<div>
		<h3><?php _e('Exclude this post from being matched','bluet-kw'); ?></h3>
		<input type="checkbox" 
			id="bluet_kw_admin_exclude_post_from_matching_id" 
			onClick="hideIfChecked('bluet_kw_admin_exclude_post_from_matching_id','bluet_kw_admin_div_terms')" 
			name="bluet_exclude_post_from_matching_name" <?php if(!empty($exclude_me)) echo "checked"; ?>
		/>
		<label for="bluet_kw_admin_exclude_post_from_matching_id" style="color:red;"><?php _e('Exclude this post','bluet-kw'); ?></label>


	
	<?php

//show keywords list related
	
	$my_kws=array();
	
	$my_kws=kttg_get_related_keywords($current_post_id);
	
	//echo('<pre>');print_r($my_kws);echo('</pre>');

	$bluet_matching_keywords_field=get_post_meta($current_post_id,'bluet_matching_keywords_field',true);
	
	?>
	
		<div id="bluet_kw_admin_div_terms">
		<?php

	if(!empty($my_kws)){
		?>		
			<h3><?php _e('Keywords related','bluet-kw');?></h3>
		<?php
		echo('<ul style="list-style: initial; padding-left: 20px;">');
			foreach($my_kws as $kw_id){
				$kw_title=get_the_title($kw_id);

				if(in_array(strtolower(trim($kw_title)),$my_excluded_keywords)){
					echo('<li style="color:red;"><i>'.$kw_title.'</i></li>'); 
				}else{
					echo('<li style="color:green;"><i>'.$kw_title.'</i></li>'); 
				}
				
			}
		echo('</ul>');
	}else{
		echo('<p>'.__('No KeyWords found for this post','bluet-kw').'</p>');
	}
	
	?>
		<h3><?php _e('Keywords to exclude','bluet-kw'); ?></h3>			
		<!-- test -->
		<div class="easy_tags">		
			<div class="easy_tags-content" onclick="jQuery('#bluet_cover_areas_id').focus()"> <!-- content before add button -->
				<div class="easy_tags-list tagchecklist" id="cover_areas_list" >	<!-- list before field -->
				</div>
				
				<input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_areas_id" placeholder="<?php _e('keyword...','bluet-kw'); ?>"> <!-- field -->
					<input class="easy_tags-to_send" type="hidden" name="bluet_exclude_keywords_from_matching_name" id="exclude-keywords-field" value="<?php echo $exclude_keywords_string; ?>" > <!-- hidden text to send -->
			</div>

			<input class="easy_tags-add button tagadd" type="button" value="<?php _e('Add'); ?>" id="cover_class_add" > <!-- add button -->
		</div>
	<!-- end -->
	<script>
	jQuery(document).ready(function(){
		field=easy_tags.construct(",");

		field.init(".easy_tags");		
		field.fill_classes(".easy_tags");
	});
	</script>
	<?php

	echo('<p><a href="'.get_admin_url().'edit.php?post_type='.$tooltipy_post_type_name.'">');
	echo(__('Manage KeyWords','bluet-kw').' >>');
	echo('</a></p>');
		echo('</div>');
	echo "</div>";
}

add_action('save_post',function(){
	global $tooltipy_post_type_name;
	//saving synonyms
	if(!empty($_POST['post_type']) and $_POST['post_type']==$tooltipy_post_type_name){
		//do sanitisation and validation
		
		//synonyms
		//editpost to prevent quick edit problems
		if($_POST['action'] =='editpost'){
			$syns_save=$_POST['bluet_synonyms_name'];		
			
			$kttg_case=$_POST['bluet_case_sensitive_name'];
			
			//replace ||||||| by only one
			$syns_save=preg_replace('(\|{2,100})','|',$syns_save);
			
			//eliminate spaces special caracters
			$syns_save=preg_replace('(^\||\|$|[\s]{2,100})','',$syns_save);
			update_post_meta($_POST['post_ID'],'bluet_synonyms_keywords',$syns_save);
			
			update_post_meta($_POST['post_ID'],'bluet_case_sensitive_word',$kttg_case);		
			
			//prefixes if exists
			if(function_exists('bluet_prefix_save')){
				bluet_prefix_save();
			}
			
			//prefixes if exists
			if(function_exists('bluet_video_save')){
				bluet_video_save();
			}
	
			// for a specific use icon extention
			if(function_exists('bluet_icon_save')){
				bluet_icon_save();
			}
			
		}
		
		
	}else{
		if(!empty($_POST['action']) and $_POST['action'] =='editpost'){

			$exclude_me=$_POST['bluet_exclude_post_from_matching_name'];
			$exclude_keywords_string=$_POST['bluet_exclude_keywords_from_matching_name'];

			// save exclude post from matching
			update_post_meta($_POST['post_ID'],'bluet_exclude_post_from_matching',$exclude_me);
			
			//get list if excluded posts
			$tooltipy_excluded_posts = get_option("tooltipy_excluded_posts_from_matching");

			// if the post is excluded
			if( $exclude_me == "on"){

				// insert excluded post into an option instead of a post meta
				// in order to get them much faster using the "bluet_kw_fetch_excluded_posts()" function
				// this will prevent it from seeking all the posts and pages meta boxes each time
				
				// this post info
				$tooltipy_this_post_info = array(
					'id' 	=> 	$_POST['post_ID'],
					'title' => 	$_POST['post_title'],
					'slug' 	=> 	$_POST['post_name'],
				);
				if( is_array($tooltipy_excluded_posts) ){
					// since option is there
					// add this post inf as excluded "update_option()" if not
					
					foreach ($tooltipy_excluded_posts as $key => $excluded_post) {
						// look if it is allready there
						if( $excluded_post['id'] == $_POST['post_ID'] ){
							// remove it from array so to puch it with new data later with " array_push() "
							unset($tooltipy_excluded_posts[$key]);
						}
					}

					array_push( $tooltipy_excluded_posts, $tooltipy_this_post_info );

					update_option( "tooltipy_excluded_posts_from_matching" , $tooltipy_excluded_posts );

				}else{
					// option is not yet created
					//	add it ( create it ) "add_option()"
					//	add this post inf as excluded
					$tooltipy_excluded_posts= 	array(
													$tooltipy_this_post_info
												);
					add_option( "tooltipy_excluded_posts_from_matching" , $tooltipy_excluded_posts );
				}
			}else{
				if( is_array($tooltipy_excluded_posts) ){
					// if not excluded remove it from the "tooltipy_excluded_posts_from_matching" option
					foreach ($tooltipy_excluded_posts as $key => $excluded_post) {
						// look if it is allready there
						if( $excluded_post['id'] == $_POST['post_ID'] ){
							// remove it from array
							unset($tooltipy_excluded_posts[$key]);
						}
					}

					update_option( "tooltipy_excluded_posts_from_matching" , $tooltipy_excluded_posts );
				}else{
					$tooltipy_excluded_posts= array();
					add_option( "tooltipy_excluded_posts_from_matching" , $tooltipy_excluded_posts );
				}
			}

			// 
			update_post_meta($_POST['post_ID'],'bluet_exclude_keywords_from_matching',$exclude_keywords_string);

			$matchable_keywords=$_POST['matchable_keywords'];
			$arr_match=array();

			if(!empty($matchable_keywords)){
				foreach($matchable_keywords as $k=>$matchable_kw_id){
					$arr_match[$matchable_kw_id]=$matchable_kw_id;
				}
			}else{
				//
			}
			update_post_meta($_POST['post_ID'],'bluet_matching_keywords_field',$arr_match);
		}	
	}
}); 

/*
add_action('save_post',function(){
	var_dump($_POST);
	die();
});*/