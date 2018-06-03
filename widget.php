<?php
defined('ABSPATH') or die("No script kiddies please!");

class bluet_keyword_widget extends wp_widget{
	function __construct(){
		$params=array(
			'description'=>'contains keywords used in the current single post.',
			'name'=>'My keywords (KTTG)'
		);
		parent::__construct('my_keywords_widget','',$params);
	}
	
	public function widget( $args, $instance ) {
		global $is_kttg_glossary_page;
		global $tooltip_post_types;
		global $tooltipy_cat_name;
		
		$options = get_option( 'bluet_kw_style' ); //to get the ['bt_kw_fetch_mode']
		
		//init added classes
		(!empty($options['bt_kw_add_css_classes']['keyword'])) 	? $css_classes_added_inline_keywords=$options['bt_kw_add_css_classes']['keyword'] 	: $css_classes_added_inline_keywords="";
		(!empty($options['bt_kw_add_css_classes']['popup'])) 	? $css_classes_added_popups=$options['bt_kw_add_css_classes']['popup'] 				: $css_classes_added_popups="";

		
		if(!(is_single() or is_page())) return;
		
		$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);

		//verifying custom postes to filter			
		if(function_exists('bluet_filter_this_custom_post_type')){
			$filter_this_custom_post_type=bluet_filter_this_custom_post_type(get_the_id());
		}else{
			$filter_this_custom_post_type=false;				
		}

		$settings=get_option('bluet_kw_settings');

		if(
			$exclude_me			
			/*or 	(is_single() and (((get_post_type(get_the_id())=='post') and !$settings['bt_kw_for_posts'])
										or ((get_post_type(get_the_id())!='post') and !$filter_this_custom_post_type))
					)
			or (is_page() and !$settings['bt_kw_for_pages'])*/
			or $is_kttg_glossary_page
		){
			return false;
		}

			
		// outputs the content of the widget
		extract($args);
		echo($before_widget);
			echo($before_title);
				echo $instance['title'];
			echo($after_title);
			echo('<ul>');
			//widget content process here
				$my_keywords_ids=tltpy_get_related_keywords(get_the_id());
				
				//if user specifies keywords to match
				$bluet_matching_keywords_field=get_post_meta(get_the_id(),'bluet_matching_keywords_field',true);
				if(!empty($bluet_matching_keywords_field)){
					$my_keywords_ids=$bluet_matching_keywords_field;
				}
				
				if(!empty($my_keywords_ids)){ //if have terms in concern
					foreach($my_keywords_ids as $term_id){
					
					// The Query
					$wk_args=array(
						'p'=>$term_id,
						'post_type'=> $tooltip_post_types
					);
					
						$the_wk_query = new WP_Query( $wk_args );

					// The Loop
					if ( $the_wk_query->have_posts() ) {

						while ( $the_wk_query->have_posts() ) {
							$the_wk_query->the_post();
							
								$trm=get_the_title();
								
								$kw_id=get_the_id();

							// adding &zwnj; (invisible character) to avoid tooltips overlapping 
								$trm=tltpy_elim_apostrophes($trm);

								$dfn=preg_replace('#('.$trm.')#i',$trm.'&zwnj;',get_the_content());
								
								$img=get_the_post_thumbnail($term_id,'medium');
								
								$trm=$trm.'&zwnj;';

								//categories or families
								$tooltipy_families_arr = wp_get_post_terms($kw_id, $tooltipy_cat_name,array("fields" => "ids"));
								foreach ($tooltipy_families_arr as $key => $value) {
								 	$tooltipy_families_arr[$key]="tooltipy-kw-cat-".$value;
								}
							  	$tooltipy_families_class=implode(" ",$tooltipy_families_arr);

							  	//youtube
							  	$tooltipy_video=get_post_meta(get_the_id(),'bluet_youtube_video_id',true);

							  	if(strlen($tooltipy_video)>5){
							  		$tooltipy_video_class="tooltipy-kw-youtube";							  		
								}else{
							  		$tooltipy_video_class="";
								}

						}
						
					}

					/* Restore original Post Data */
					wp_reset_postdata();
					
						$string_to_show='<li>
											<span class="bluet_tooltip tooltipy-kw tooltipy-kw-'.$kw_id.' '.$tooltipy_families_class.' '.$tooltipy_video_class.' '.$css_classes_added_inline_keywords.'" data-tooltip="'.$kw_id.'">
												'.$trm.'
											</span> 
										</li>';
						
						echo($string_to_show);
					}
				}else{
					_e('no terms found for this post','tooltipy-lang');
				}
			//
				echo('</ul>');

		echo($after_widget);
	}

	public function form( $instance ) {
	?>
		<label for="<?php echo $this->get_field_id('title'); ?>" >Title : </label>
		<input
			class="widefat"
			id="<?php echo $this->get_field_id('title'); ?>"
			name="<?php echo $this->get_field_name('title'); ?>"
			value="<?php if(isset($instance['title'])) echo esc_attr($instance['title']); ?>"
		/>
	<?php
	
		// outputs the options form on admin
	}
	
	public function register_widget(){
	}	
}

add_action('widgets_init',function(){
	register_widget('bluet_keyword_widget');
});

?>