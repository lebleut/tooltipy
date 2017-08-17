<?php
defined('ABSPATH') or die("No script kiddies please!");

/*place metabox after the Title*/ 
add_action('edit_form_after_title', 'tooltipy_place_metabox_after_title_func');
/**/
function tooltipy_place_metabox_after_title_func() {
    global $post, $wp_meta_boxes,$post_type;
    
	do_meta_boxes($post_type,'after_title',$post);
	//echo("<pre>");print_r($post_type);echo("</pre>");
	
}
add_action('do_meta_boxes', 'tooltipy_edit_page_metaboxes_func');

function tooltipy_edit_page_metaboxes_func(){
//for keywords
		add_meta_box(
			'bluet_kw_settings_meta',
			__('Keyword Settings','bluet-kw'),
			'bluet_keyword_settings_render',
			'my_keywords',
			'after_title',
			'high'
		);
		
//for post types except my_keywords
	$screens = array();
	$all_post_types=get_post_types();
	foreach ($all_post_types as $key => $pt) {
		if($pt!='my_keywords'){
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
		
}

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
	
}

function bluet_keywords_related_render(){
//exclude checkbox to exclode the current post from being matched
	global $post;

	$current_post_id=$post->ID;
	$exclude_me = get_post_meta($current_post_id,'bluet_exclude_post_from_matching',true);
	$exclude_keywords_string = "";

	//get excluded terms and sanitize them
	/*begin*/
		$my_excluded_keywords=explode(',',$exclude_keywords_string);
		$my_excluded_keywords=array_map('trim',$my_excluded_keywords);
		$my_excluded_keywords=array_map('strtolower',$my_excluded_keywords);
		
		$my_excluded_keywords=array_filter($my_excluded_keywords,'tooltipy_remove_empty_string_func');
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
		<?php
			$kttg_pro_link="<a href='http://www.tooltipy.com/downloads/kttg-pro'>KTTG PRO</a>";
		?>
		This section is available only on <?php echo($kttg_pro_link); ?>		
	<!-- end -->
	<?php

	echo('<p><a href="'.get_admin_url().'edit.php?post_type=my_keywords">');
	echo(__('Manage KeyWords','bluet-kw').' >>');
	echo('</a></p>');
		echo('</div>');
	echo "</div>";
}


add_action('save_post', 'tooltipy_process_when_saving_func');

function tooltipy_process_when_saving_func(){
	//saving synonyms
	if(!empty($_POST['post_type']) and $_POST['post_type']=='my_keywords'){
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
		}
		
		
	}else{
		if(!empty($_POST['action']) and $_POST['action'] =='editpost'){
			$exclude_me=$_POST['bluet_exclude_post_from_matching_name'];
			$exclude_keywords_string=$_POST['bluet_exclude_keywords_from_matching_name'];
			
			//save exclude post from matching
			update_post_meta($_POST['post_ID'],'bluet_exclude_post_from_matching',$exclude_me);
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
}