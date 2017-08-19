<?php
defined('ABSPATH') or die("No script kiddies please!");


function bluet_kttg_pro_addon(){
	return true;
}

function bluet_kw_pro_activation(){
	$advanced_options=array();

	//initialise style option if bluet_kw_advanced is empty
	$advanced_options=array(
		'bt_kw_supported_plugins' =>array(
			'bbpress'=>'on',
			'wooc'=>'on'
		),
		'bt_kw_in_concern_custom_posts'=>array(
			'custom_fields_hooks'=>array(
									'pt0'=>array('the_content'),
									'pt1'=>array('the_content'),
									),
			'post_types'=>array('post','page')
			)
	);
	
	$adv_options=get_option('bluet_kw_advanced');
	
	/*repaire this*/
	//if(!array_key_exists('bt_kw_in_concern_custom_posts', $adv_options)){
		delete_option('bluet_kw_advanced');
		add_option('bluet_kw_advanced',$advanced_options);
	//}
}
function bluet_kttg_add_meta_to_check($id){
	//call this function in the loop
	//add the title
	$ret=' ';
	global $post;
	
	//browse post meta to add it to $ret
	if(get_post_custom()){
		foreach(get_post_custom() as $field_key => $field_values) {	
			foreach($field_values as $key => $value){
				$ret.=' '.$value.'.';
			}
		}
	}

	//this is useful in the WooCommerce case to get the product short description

	$ret.=' '.$post->post_excerpt.'.';
	
	return $ret;
}

function bluet_get_post_types_to_filter(){
//returns the custom post types to filter
	$options=get_option('bluet_kw_advanced');
	$bluet_post_types_to_filter=array();

	if(!empty($options['bt_kw_in_concern_custom_posts'])){
		$bluet_post_types_to_filter=$options['bt_kw_in_concern_custom_posts']['post_types'];
		
		$options_supported_plgs=$options['bt_kw_supported_plugins'];

		if($options_supported_plgs['bbpress']){//if bbpress option checked
			$bluet_post_types_to_filter[]='topic';
		}

		if($options_supported_plgs['wooc']){//if wooc option checked
				$bluet_post_types_to_filter[]='product';
		}

		if(!empty($bluet_post_types_to_filter)){
			foreach($bluet_post_types_to_filter as $k=>$val){
				if($val==""){
					unset($bluet_post_types_to_filter[$k]);
				}
			}
		}
	}
	return $bluet_post_types_to_filter;
}

function bluet_get_custom_fields_to_filter(){
//returns the custom fields to filter
	$options=get_option('bluet_kw_advanced');
	$bluet_custom_fields_to_filter=array();
	if(!empty($options['bt_kw_in_concern_custom_posts'])){
		$bluet_custom_fields_to_filter=$options['bt_kw_in_concern_custom_posts']['custom_fields_hooks'];
		if(!empty($options['bt_kw_in_concern_custom_posts']['is_acf_field'])){
			$bluet_is_acf_filter=$options['bt_kw_in_concern_custom_posts']['is_acf_field'];
		}else{
			$bluet_is_acf_filter=false;
		}	

		if(!empty($bluet_custom_fields_to_filter)){
			foreach($bluet_custom_fields_to_filter as $k=>$val){
				if(gettype($val)=="array"){
					foreach($val as $ind=>$v){
						if($v==""){
							unset($bluet_custom_fields_to_filter[$k][$ind]);
						}else{
							if($bluet_is_acf_filter[$k][$ind]){
								//if if acf field name
								$bluet_custom_fields_to_filter[$k][$ind]="acf/load_value/name=".$bluet_custom_fields_to_filter[$k][$ind];
							}
						}
					}
				}
			}
			foreach($bluet_custom_fields_to_filter as $k=>$val){
				if(empty($val)){
					unset($bluet_custom_fields_to_filter[$k]);
				}
			}
		}			
	}

	return $bluet_custom_fields_to_filter;
}

function bluet_filter_this_custom_post_type($my_id){				
	$filter_this_custom_post_type=false;
	
	$kttg_current_post_type=get_post_type($my_id);
	
	foreach(bluet_get_post_types_to_filter() as $id=>$posttype_name){
		if($kttg_current_post_type==$posttype_name){
			$filter_this_custom_post_type=true;
			break;
		}
	}
	return $filter_this_custom_post_type;
}


function bluet_get_posttypes_list(){
//returns the post types except (post, page and my_keywords) in an array 
	$ret=array();
	foreach(get_post_types() as $id=>$ptn){
		if(!in_array($ptn,array('my_keywords'))){
			$ret[]=$ptn;
		}
	}
	return $ret;
}

/*video tooltips functions - begin*/
function bluet_kttg_all_tooltips_layout($kttg_text,$kttg_image,$kttg_youtube_id,$id){
	
	$button_prop='none';
	$html_text='';
	$html_media='';

	if(is_i_device()){
		$button_prop='block';
	}
	
	if($kttg_text!="" and $kttg_text!=null){
		$html_text='<div class="bluet_text_content">'.$kttg_text.'</div>';
	}
	
	$html_kttg_image=$kttg_image;
	$html_kttg_youtube_id='<div class="bluet_img_in_tooltip" id="iframe_'.$id.'">'
							.'<iframe width="300" height="200" src="https://www.youtube.com/embed/'.$kttg_youtube_id.'?rel=0&amp;showinfo=0&amp;enablejsapi=1" frameborder="0"></iframe>'
						.'</div>';
							
	if($kttg_image!="" and $kttg_image!=null){
		$html_media='<div class="bluet_img_in_tooltip"><img src="'.$html_kttg_image.'"></div>';
	}

	if($kttg_youtube_id!="" and $kttg_youtube_id!=null){
		$html_media=$html_kttg_youtube_id;
	}
	
	$layout_ret='<span class="bluet_block_to_show" data-tooltip-id="'.$id.'" onmouseover="callPlayer(\'iframe_'.$id.'\',\'playVideo\')" onmouseleave="callPlayer(\'iframe_'.$id.'\',\'pauseVideo\')">'
					.'<img src="'.plugin_dir_url(__FILE__).'assets/close_button.png" class="bluet_hide_tooltip_button" style="display:'.$button_prop.';"/>'
					.'<div class="bluet_block_container">'
						.$html_media
						.$html_text
					.'</div>'
				.'</span>';

	return $layout_ret;
}

function bluet_video_metabox(){
		?>
	<p>
		<label for='bluet_video_id'><b><?php _e('Youtube video ID','bluet-kw');?></b></label><br>
		WWW.Youtube.com/watch?v=<input id="bluet_video_id" name="bluet_video_id_name" type="text" value="<?php echo(get_post_meta(get_the_id(),'bluet_youtube_video_id',true));?>" /><img src="<?php echo(plugin_dir_url(__FILE__).'assets/youtube_play.png');?>" style="position: relative; top: 5px;">
	</p>
	<?php
}

function bluet_video_save(){
	if($_POST['action'] =='editpost'){
		$kttg_youtube_save=trim($_POST['bluet_video_id_name']); //trim to delete spaces
		update_post_meta($_POST['post_ID'],'bluet_youtube_video_id',$kttg_youtube_save);
	}
}

function bluet_show_video_in_column(){
	
	$kttg_you_video=get_post_meta(get_the_id(),'bluet_youtube_video_id',true);
	if($kttg_you_video!=""){
		?><span style="color:green;"><?php _e('Video :','bluet-kw'); echo('<a href="http://WWW.Youtube.com/watch?v='.$kttg_you_video.'">'.$kttg_you_video.'</a>');?></span>
		<?php
	}else{
		_e('-','bluet-kw');
	}
}
/*video tooltips functions - end*/
function bluet_kw_adv_enqueue_scripts() {
	wp_enqueue_script( 'kttg-pro-tooltip-scripts', plugins_url('assets/kttg-pro-functions.js',__FILE__), array('jquery'), TOOLTIPY_VERSION, true );
	
	//test
	//wp_enqueue_script( 'kttg-pro-findandreplacedomtext', plugins_url('assets/findandreplacedomtext.js',__FILE__), array(),TOOLTIPY_VERSION , false );
}
function bluet_kw_adv_enqueue() {
	$adv_opt_tmp=get_option('bluet_kw_advanced');
	$kttg_custom_style=$adv_opt_tmp['bt_kw_adv_style']['custom_style_sheet'];
	if(!empty($adv_opt_tmp['bt_kw_adv_style']['apply_custom_style_sheet'])){
		$kttg_apply_custom_style=$adv_opt_tmp['bt_kw_adv_style']['apply_custom_style_sheet'];
	}else{
		$kttg_apply_custom_style=false;
	}
		
	if($kttg_apply_custom_style and $kttg_custom_style!=''){
		//
		wp_enqueue_style( 'kttg-custom-style-sheet', $kttg_custom_style);
	}
}

/* prefix feature - begin*/
function bluet_prefix_metabox(){
	?>
	<p>
		<label for='bluet_prefix_id'><?php _e('This Keyword is a <b>Prefix</b>','bluet-kw');?>  </label>
		<input id="bluet_prefix_id" name="bluet_prefix_name" type="checkbox" <?php if(get_post_meta(get_the_id(),'bluet_prefix_keywords',true)) echo('checked');?> />
	</p>
	<?php
}

function bluet_prefix_save(){
	if($_POST['action'] =='editpost'){
		$pref_save=$_POST['bluet_prefix_name'];
		update_post_meta($_POST['post_ID'],'bluet_prefix_keywords',$pref_save);
	}
}
function bluet_show_prefix_in_column(){
	if(get_post_meta(get_the_id(),'bluet_prefix_keywords',true)){
		?><span style="color:red;"><?php _e('Prefix','bluet-kw');?></span>
		<?php
	}else{
		_e('No','bluet-kw');
	}
}
/* prefix feature - end*/

/*pro images -begin*/
function bluet_buttons_mce() {
	add_filter("mce_external_plugins", "bluet_buttons_mce_add");
    add_filter('mce_buttons', 'bluet_buttons_mce_register');	
	
	add_editor_style(plugins_url('assets/kttg-mce.css',__FILE__));
}	

function bluet_buttons_mce_add($plugin_array) {
	$plugin_array['bluetKFI'] = plugins_url('assets/bluetkfi-plugin.js',__FILE__);
	return $plugin_array;
}

function bluet_buttons_mce_register($buttons) {
	array_push( $buttons, 'bluet_kttg_img'); 
	return $buttons;
}

function bluet_filter_imgs_content(){
	global $tooltip_post_types;
		add_filter('the_content', 'tooltipy_filter_images_content_func',101);
}
function tooltipy_filter_images_content_func($cont){
			
	$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);
	//if the current post tells us to exclude from fetch
	if($exclude_me) return $cont;
	
	$settings=get_option('bluet_kw_settings');

	if(((!empty($settings['bt_kw_match_excerpts']) and $settings['bt_kw_match_excerpts'] ? true : is_single()) and $settings['bt_kw_for_posts']) or (is_page() and !empty($settings['bt_kw_for_pages']) and $settings['bt_kw_for_pages']=="on")){ 
		$my_keywords_ids=kttg_get_related_keywords(get_the_id());
		
		//if user specifies keywords to match
		$bluet_matching_keywords_field=get_post_meta(get_the_id(),'bluet_matching_keywords_field',true);
		if(!empty($bluet_matching_keywords_field)){
			$my_keywords_ids=$bluet_matching_keywords_field;
		}

		if(!empty($my_keywords_ids)){
			
			$my_keywords_terms=array();
			
			// The Query
			$wk_args=array(
				'post__in'=>$my_keywords_ids,
				'post_type'=> $tooltip_post_types
			);
			
			$the_wk_query = new WP_Query( $wk_args );

			// The Loop
			if ( $the_wk_query->have_posts() ) {

				while ( $the_wk_query->have_posts() ) {
					$the_wk_query->the_post();
					
					$my_keywords_terms[]=array(
						'kw_id'=>get_the_id(),
						'term'=>get_the_title(),
						'syns'=>get_post_meta(get_the_id(),'bluet_synonyms_keywords',true),
						'dfn'=>get_the_content(),
						'img'=>get_the_post_thumbnail(get_the_id(),'medium')
						);	
				}
				
			} else {
				// no posts found
			}
			/* Restore original Post Data */
			wp_reset_postdata();
				
				$limit_match=((!empty($settings['bt_kw_match_all']) and $settings['bt_kw_match_all']=='on')? -1 : 1);
				
				foreach($my_keywords_terms as $id=>$arr){
					$term=$arr['term'];
					
					//concat synonyms if they are not empty
					if($arr['syns']!=""){
						$term.='|'.$arr['syns'];
					}

					$img=$arr['img'];
					$dfn=$arr['dfn'];
					
					if($dfn!=""){
						$dfn=" : ".$arr['dfn'];
					}
					
					$term_1=explode('|',$term);
					$term_1=$term_1[0];
					
					$html_to_replace='$1
									<span class="bluet_block_to_show" data-tooltip-id="'.$arr["kw_id"].'">
										<span class="bluet_block_container">
											'.$img.'
											<span class="bluet_title_on_block">'.$term_1.'</span>  
											'.$dfn.'
										</span>
									</span>';
					$cont=preg_replace('#(<img\s([^>]*\s)?alt="KTTG: '.$term_1.'"(.*?)>)#i',$html_to_replace,$cont,$limit_match);
					
					//to add data-tooltip-id attrib
					$cont=preg_replace('#((<img)(\s([^>]*\s)?alt="KTTG: '.$term_1.'"(.*?)>))#i','<img class="bluet_img_tooltip" data-tooltip-id="'.$arr["kw_id"].'" $3',$cont,$limit_match);
					
				}
		}
	}
	return $cont;
}
/*pro images -end*/

?>