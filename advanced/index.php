<?php
/*
pro addon
*/

defined('ABSPATH') or die("No script kiddies please!");

require_once dirname( __FILE__ ) . '/settings-page.php'; // post
require_once dirname( __FILE__ ) . '/functions.php'; // post
require_once dirname( __FILE__ ) . '/supported-plugins.php'; // 
require_once dirname( __FILE__ ) . '/shortcodes.php'; // 
require_once dirname( __FILE__ ) . '/load-ajax.php';


register_activation_hook( __FILE__,'bluet_kw_pro_activation');

include_once(ABSPATH.'wp-admin/includes/plugin.php');

bluet_filter_imgs_content();
//enqueue functions
//enque custom css if enabled
add_action('wp_head','bluet_kw_adv_enqueue');

//enque pro scripts
add_action('wp_head','bluet_kw_adv_enqueue_scripts');

add_action( 'admin_init', 'bluet_buttons_mce' );

add_action('init',function(){

	//add metaboxes for custom post types

	add_action('do_meta_boxes',function(){

		foreach(bluet_get_post_types_to_filter() as $id=>$my_customposttype){
			//!in_array($my_customposttype,array('post','page')) to prevent double metaboxes in post and page posttypes
			if(post_type_exists($my_customposttype) and !in_array($my_customposttype,array('post','page'))){
				add_meta_box(
				'bluet_kw_posttypes_related_keywords_meta',
				__('Keywords related','bluet-kw').' (KTTG)',
				'bluet_keywords_related_render',
				$my_customposttype,
				'side',
				'high'
				);	
			}
		}

	});
	

	// /*add custom post types to match*/	
	add_filter('bluet_kttg_posttypes_to_match',function($cont){		
		$post_types_to_filter=bluet_get_post_types_to_filter();
	
		$cont=array(); //to eliminate page and post posttypes if pro is activated

		if(!empty($post_types_to_filter)){
			foreach($post_types_to_filter as $cpt){
					$cont[]=$cpt;				
			}
		}
		return $cont;
	});
	
	// /*add custom fields to match*/	
	add_filter('bluet_kttg_dustom_fields_hooks',function($cont){
		$custom_fields_to_filter=bluet_get_custom_fields_to_filter();
		$cont=array(); //to eliminate the_content filter hook

		if(!empty($custom_fields_to_filter)){
			foreach($custom_fields_to_filter as $cfd){
					$cont[]=$cfd;
			}			
		}
		return $cont;
	});
});

?>