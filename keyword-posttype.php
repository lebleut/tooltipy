<?php
defined('ABSPATH') or die("No script kiddies please!");

class bluet_keyword{
	
	function __construct(){
		$this->register_my_post_type();
		
		$this->add_columns();
	}
	
	public function register_my_post_type(){
		global $bluet_kw_capability, $tooltipy_post_type_name, $tooltipy_cat_name;

		$args=array(
			'labels'=>array(
				'name'=>__('My KeyWords','bluet-kw'),
				'singular_name'=>__('KeyWord','bluet-kw'),
				'menu_name'=>__('Tooltipy','bluet-kw'),
				'name_admin_bar'=>__('My KeyWords','bluet-kw'),
				'all_items'=>__('My KeyWords','bluet-kw'),
				'add_new' =>__('Add'),
				'add_new_item'=>__('New').' '.__('KeyWord','bluet-kw'),
				'edit_item'=>__('Edit').' '.__('KeyWord','bluet-kw'),
				'new_item'=>__('New').' '.__('KeyWord','bluet-kw'),
				'view_item'=>__('View').' '.__('KeyWord','bluet-kw'),
				'search_items'=>__('Search for KeyWords','bluet-kw'),
				'not_found'=>__('KeyWords not found','bluet-kw'),
				'not_found_in_trash'=>__('KeyWords not found in trash','bluet-kw'),
				'parent_item_colon' =>__('Parent KeyWords colon','bluet-kw')
				),
			'public'=>true,
			'supports'=>array('title','editor','thumbnail','author'),
			'menu_icon'=>plugins_url('assets/ico_16x16.png',__FILE__),
			
		);
		
		//modify capabilities if bluet_kw_capability hook has been called
		
		
		if($bluet_kw_capability!='manage_options'){
		$args['capabilities']=array(
		        'edit_post' => $bluet_kw_capability,
				'edit_posts' => $bluet_kw_capability,
				'publish_posts' => $bluet_kw_capability,
				'delete_post' => $bluet_kw_capability,
			);
			
		}		
	
		register_post_type($tooltipy_post_type_name,$args);

		$fam_args=array(
			'labels'=>array(
				'name'=>__('Families','bluet-kw')
			),
			'hierarchical'=> true,			
    		'show_ui' => 'radio',
			'show_admin_column' => true,
		);

		register_taxonomy(
				$tooltipy_cat_name,
				$tooltipy_post_type_name,
				$fam_args);
	}
	public function add_columns(){
		
		// add the picture among columns
		add_filter('manage_my_keywords_posts_columns', function($defaults){		

			$defaults['the_picture']=__('Picture','bluet-kw');
			$defaults['is_prefix'] =__('Is Prefix ?','bluet-kw');
			$defaults['is_video'] =__('Video tooltip','bluet-kw');
			
			//we want to rearrange the columns apearance
			$reArr['cb']=$defaults['cb']; //checkBox column
			$reArr['the_picture']=$defaults['the_picture'];
			$reArr['title']=$defaults['title'];
			
			//is prefix ? if appropriate addon is activated
			if(function_exists('bluet_prefix_metabox')){
				$reArr['is_prefix']=$defaults['is_prefix'];
			}
			
			if(function_exists('bluet_video_metabox')){
				$reArr['is_video']=$defaults['is_video'];
			}
			//
			$reArr['date']=$defaults['date'];
			
			//return the rearranged array
			return $reArr;
		});
		
		add_action('manage_my_keywords_posts_custom_column', function($column_name,$post_id){

			if ($column_name == 'the_picture') {
				// show content of 'directors_name' column
				the_post_thumbnail(array(75,75));
			}elseif($column_name == 'is_prefix'){
				//if appropriate addon is activated
				if(function_exists('bluet_show_prefix_in_column')){
					bluet_show_prefix_in_column();
				}
			}elseif($column_name == 'is_video'){
				//if appropriate addon is activated
				if(function_exists('bluet_show_video_in_column')){
					bluet_show_video_in_column();
				}
			}
		},10,2); //10 priority, 2 arguments

	}
}

?>