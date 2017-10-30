<?php
defined('ABSPATH') or die("No script kiddies please!");

# Adds a shortcode called 'kttg_glossary'.

remove_shortcode('tooltip', 'tooltip_shortcode');

add_action('init','kttg_pro_shortcodes');

function kttg_pro_shortcodes(){
	add_shortcode('tooltip', 'bluet_kttg_shortcode');	
}

function bluet_kttg_shortcode($atts,$content=null){
	extract(shortcode_atts(array(
		"text" => '',
		"image"=>'',
		"youtube"=>''
	   ), $atts));
	
	$my_post_id=get_the_id();
	$exclude_me = get_post_meta($my_post_id,'bluet_exclude_post_from_matching',true);			

	$kttg_youtube_id=$youtube;	
	$kttg_image=$image;	
	$kttg_text=$text;
	$kttg_tooltip_id=rand(11111,55555);
	$kttg_content=$content;
	
	//get out if excluded
	if($exclude_me){
		return $kttg_content;
	}	

	if(!$kttg_content){
		$kttg_content='<b>tooltip</b>';
	}
	
	$ret='<span class="bluet_tooltip" data-tooltip="'.$kttg_tooltip_id.'">'.$kttg_content.'</span>';
	
	add_filter('kttg_another_tooltip_in_block',function($cont) use ($kttg_tooltip_id,$kttg_youtube_id,$kttg_image,$kttg_text){ //"use" to pass external parameters to add_filter
	
		$add_to_block_of_tooltips=bluet_kttg_all_tooltips_layout($kttg_text,$kttg_image,$kttg_youtube_id,$kttg_tooltip_id);		

		$cont.=$add_to_block_of_tooltips;
		
		return $cont;
	});
	
	return $ret;
}

