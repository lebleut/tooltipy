<?php
defined('ABSPATH') or die("No script kiddies please!");

/* bbpress */
$adv_options = get_option('bluet_kw_advanced');
if(!empty($adv_options['bt_kw_supported_plugins'])){
	$options_supported_plgs=$adv_options['bt_kw_supported_plugins'];

	if($options_supported_plgs['bbpress']){//if bbpress option checked
		add_filter('bbp_get_reply_content','tltpy_filter_posttype',100);
	}
}else{
    $options_supported_plgs=array();
}

add_action('wp_head',function(){	
	//woocommerce support
	if(function_exists('tltpy_pro_addon')){//if pro addon activated
		$adv_options = get_option('bluet_kw_advanced');
		if(!empty($adv_options['bt_kw_supported_plugins'])){
			$options_supported_plgs=$adv_options['bt_kw_supported_plugins'];
			
			if($options_supported_plgs['wooc']){//if wooc option checked
				if(get_post_type()=='product'){
					add_filter('the_content','tltpy_filter_posttype',100);
				}		
				add_filter('woocommerce_short_description','tltpy_filter_posttype',100);
			}
		}
	}
});

