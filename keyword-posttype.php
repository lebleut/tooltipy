<?php
defined('ABSPATH') or die("No script kiddies please!");

class bluet_keyword{
	
	function __construct(){
		$this->register_my_post_type();
		
		$this->add_columns();
	}
	
	public function register_my_post_type(){
		global $tltpy_capability, $tooltipy_post_type_name, $tooltipy_cat_name;

		$args=array(
			'labels'=>array(
				'name'=>__('My KeyWords','tooltipy-lang'),
				'singular_name'=>__('KeyWord','tooltipy-lang'),
				'menu_name'=>__('Tooltipy','tooltipy-lang'),
				'name_admin_bar'=>__('My KeyWords','tooltipy-lang'),
				'all_items'=>__('My KeyWords','tooltipy-lang'),
				'add_new' =>__('Add'),
				'add_new_item'=>__('New').' '.__('KeyWord','tooltipy-lang'),
				'edit_item'=>__('Edit').' '.__('KeyWord','tooltipy-lang'),
				'new_item'=>__('New').' '.__('KeyWord','tooltipy-lang'),
				'view_item'=>__('View').' '.__('KeyWord','tooltipy-lang'),
				'search_items'=>__('Search for KeyWords','tooltipy-lang'),
				'not_found'=>__('KeyWords not found','tooltipy-lang'),
				'not_found_in_trash'=>__('KeyWords not found in trash','tooltipy-lang'),
				'parent_item_colon' =>__('Parent KeyWords colon','tooltipy-lang')
				),
			'public'=>true,
			'supports'=>array('title','editor','thumbnail','author'),
			'menu_icon'=>plugins_url('assets/ico_16x16.png',__FILE__),
			
		);
		
		//modify capabilities if bluet_kw_capability hook has been called
		
		
		if($tltpy_capability!='manage_options'){
		$args['capabilities']=array(
		        'edit_post' => $tltpy_capability,
				'edit_posts' => $tltpy_capability,
				'publish_posts' => $tltpy_capability,
				'delete_post' => $tltpy_capability,
			);
			
		}		
	
		register_post_type($tooltipy_post_type_name,$args);

		$fam_args=array(
			'labels'=>array(
				'name'=>__('Families','tooltipy-lang')
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

			$defaults['the_picture']=__('Picture','tooltipy-lang');
			$defaults['is_prefix'] =__('Is Prefix ?','tooltipy-lang');
			$defaults['is_video'] =__('Video tooltip','tooltipy-lang');
			
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