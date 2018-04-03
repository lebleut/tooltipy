<?php
/*
Plugin Name: Tooltipy
Description: This plugin allows you automatically create tooltip boxes for your technical keywords in order to explain them for your site visitors making surfing more comfortable.
Author: Jamel Zarga
Version: 5.0.3
Author URI: http://www.tooltipy.com/about-us
*/
defined('ABSPATH') or die("No script kiddies please!");

//Tooltipy post_type name
$tooltipy_post_type_name = get_option("tooltipy_post_type_name");
$default_tooltipy_post_type_name="my_keywords";

$tooltipy_cat_name = $tooltipy_post_type_name."_cat";
if($tooltipy_post_type_name == $default_tooltipy_post_type_name){
	$tooltipy_cat_name="keywords_family";
}

require_once dirname( __FILE__ ) . '/pro-addon/pro-index.php'; //pro addon
require_once dirname( __FILE__ ) . '/keyword-posttype.php'; //contain the class that handles the new custom post
require_once dirname( __FILE__ ) . '/settings-page.php';
require_once dirname( __FILE__ ) . '/widget.php';
require_once dirname( __FILE__ ) . '/meta-boxes.php';
require_once dirname( __FILE__ ) . '/glossary-shortcode.php';
require_once dirname( __FILE__ ) . '/functions.php';

if( !defined('TOOLTIPY_PLUGIN_FILE_PATH') ){
    define('TOOLTIPY_PLUGIN_FILE_PATH', __FILE__);
}

$tooltipy_plugin_data = get_plugin_data(TOOLTIPY_PLUGIN_FILE_PATH);

if( !defined('TOOLTIPY_VERSION') ){
    define('TOOLTIPY_VERSION', $tooltipy_plugin_data["Version"]);
}

$bluet_kw_capability=apply_filters('bluet_kw_capability','manage_options');


/*init settings*/
register_activation_hook(__FILE__,'bluet_kw_activation');

//pour traiter les termes lors de l'activation de l'ajout d'un nouveau terme ou nouveau post (keyword) publish_{my_keywords}
add_action( 'save_post', 'tooltipy_delete_transient_when_post_saved' );

function tooltipy_delete_transient_when_post_saved(){
	/*
		Delete cached query once a post or page is saved or edited
	*/
	delete_transient( "tooltipy_keywords_titles_ids" );
}
add_action('init',function(){	
	//Tooltipy post_type name get from options
	global $tooltipy_post_type_name, $default_tooltipy_post_type_name;

	if(empty($tooltipy_post_type_name) || $tooltipy_post_type_name=="") {		

		update_option('tooltipy_post_type_name', $default_tooltipy_post_type_name); 

		$tooltipy_post_type_name=$default_tooltipy_post_type_name;

		$tooltipy_cat_name=$tooltipy_post_type_name."_cat";
		if($tooltipy_post_type_name==$default_tooltipy_post_type_name){
			$tooltipy_cat_name="keywords_family";
		}

	}elseif($tooltipy_post_type_name==false){
		
		add_option('tooltipy_post_type_name'); 
		update_option('tooltipy_post_type_name', $default_tooltipy_post_type_name); 

		$tooltipy_post_type_name=$default_tooltipy_post_type_name;

		$tooltipy_cat_name=$tooltipy_post_type_name."_cat";
		if($tooltipy_post_type_name==$default_tooltipy_post_type_name){
			$tooltipy_cat_name="keywords_family";
		}
	}
	/**** localization ****/
	load_plugin_textdomain('bluet-kw', false, dirname( plugin_basename( __FILE__ ) ).'/languages/');

	//post types from which we get the tooltips
	global $tooltip_post_types;

	//$tooltip_post_types=array(get_option('kttg_tooltip_post_types'));
	$options=get_option('bluet_kw_settings');
	
	$tooltip_post_types=null;

	if(!empty($options['kttg_tooltip_post_types'])){
		$tooltip_post_types=$options['kttg_tooltip_post_types'];
	}


	if(empty($tooltip_post_types) OR !is_array($tooltip_post_types) OR count($tooltip_post_types)<1){
		$tooltip_post_types=array($tooltipy_post_type_name);
	}
	//create posttype for keywords	
	new bluet_keyword();

});

add_action('wp_enqueue_scripts', 'bluet_kw_load_scripts_front' );

add_action('wp_footer','bluet_kttg_place_tooltips');
add_action('admin_footer','bluet_kttg_place_tooltips');
add_action('wp_head',function(){
	//if(function_exists('bluet_kttg_pro_addon')){
		//$pro_addon_dir=plugins_url("bluet_kttg_pro_addon");
		echo('<script type="text/javascript" src="'.plugins_url('pro-addon/assets/findandreplacedomtext.js',__FILE__).'"></script>');
	//}
});
	
function bluet_kttg_place_tooltips(){
	global $tooltip_post_types, $tooltipy_cat_name;

	$exclude_me = get_post_meta(get_the_id(),'bluet_exclude_post_from_matching',true);			
	//exclusions
	if(is_singular() and $exclude_me == 'on'){
		return;
	}
	
	if(is_admin()){
		return;
	}
	
	$my_keywords_terms=array();
	$my_excluded_keywords=array();

	if(is_singular()){
		$exclude_keywords_string = get_post_meta(get_the_id(),'bluet_exclude_keywords_from_matching',true);

		//get excluded terms and sanitize them
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
	}


	
	$kttg_sttings_options=get_option('bluet_kw_settings');
	$kttg_tooltip_position=$kttg_sttings_options["bt_kw_position"];
	if(!empty($kttg_sttings_options["bt_kw_animation_type"])){
		$animation_type=$kttg_sttings_options["bt_kw_animation_type"];
	}else{
		$animation_type="flipInX";
	}
	
	if(!empty($kttg_sttings_options["bt_kw_animation_speed"])){
		$animation_speed=$kttg_sttings_options["bt_kw_animation_speed"];
	}else{
		$animation_speed="";
	}

	if(false === ( $the_wk_query = get_transient( "tooltipy_keywords_titles_ids" ) ) ){
	//get the keywords title and ids
	// The Query                                                                          
		$wk_args=array(
			'post_type'=>$tooltip_post_types,
			'posts_per_page'=> -1	//to retrieve all keywords
		);
		
		$the_wk_query = new WP_Query( $wk_args );
		
		set_transient( "tooltipy_keywords_titles_ids", $the_wk_query, 24 * HOUR_IN_SECONDS );
		//don't forget to delete the transient when a new keyword is added or edited
	}
		// The Loop
		if ( $the_wk_query->have_posts() ) {

			while ( $the_wk_query->have_posts() ) {
				$the_wk_query->the_post();
				$kw_title=get_the_title();

				if($kw_title!="" and !in_array(strtolower(trim($kw_title)),$my_excluded_keywords)){ //to prevent untitled keywords
					$tmp_array_kw=array(
						'kw_id'=>get_the_id(),
						'term'=>get_the_title(),
						'case'=>false,
						'pref'=>false,
						'syns'=>get_post_meta(get_the_id(),'bluet_synonyms_keywords',true),
						'youtube'=>get_post_meta(get_the_id(),'bluet_youtube_video_id',true),
						/*'dfn'=>get_the_content(),*/
						'img'=>get_the_post_thumbnail(get_the_id(),'medium')
					);
					
					if(get_post_meta(get_the_id(),'bluet_case_sensitive_word',true)=="on"){
						$tmp_array_kw['case']=true;
					}
					//categories or families
                    $tooltipy_families_arr = wp_get_post_terms(get_the_id(),$tooltipy_cat_name,array("fields" => "ids"));
                    foreach ($tooltipy_families_arr as $key => $value) {
                        $tooltipy_families_arr[$key]="tooltipy-kw-cat-".$value;
                    }
                    $tooltipy_families_class=implode(" ",$tooltipy_families_arr);
                    $tmp_array_kw['families_class']=$tooltipy_families_class;

					//if prefix addon activated
					if(function_exists('bluet_prefix_metabox')){
						if(get_post_meta(get_the_id(),'bluet_prefix_keywords',true)=="on"){
							$tmp_array_kw['pref']=true;
						}
					}
					
					/* icon ext */

					//choose
					$choose_icon_type_name=get_post_meta( get_the_id(), 'kttg_choose_icon_type', true );

					//url image
					$kttg_icon_url = get_post_meta( get_the_id(), 'kttg_icon_url', true );
					if(empty($kttg_icon_url)){		
						$kttg_icon_url="";
					}
					$kttg_icon_id=get_post_meta(get_the_id(),'kttg_icon_id',true);
					if(empty($kttg_icon_id)){		
						$kttg_icon_id="";
					}

					if(!empty($choose_icon_type_name)){
						if($choose_icon_type_name=='url'){
							$tmp_array_kw['icon']=$kttg_icon_url;
						}else{
							$tmp_img=wp_get_attachment_image_src($kttg_icon_id,"full");
							$tmp_array_kw['icon']=$tmp_img[0];
						}
					}else{
						$tmp_array_kw['icon']="";
					}

					$my_keywords_terms[]=$tmp_array_kw;
				}							
				
			}
			
		}
		wp_reset_postdata();
		?>	
		<script type="text/javascript">
			/*test*/
		function kttg_fetch_kws(){
			/*
			<?php	var_dump($my_excluded_keywords); ?>
			*/
			window.kttg_tab=[
			<?php foreach($my_keywords_terms as $my_kw){ 

				//for apostrophe issues :)
				$my_kw['term']=preg_replace('/\&\#8217;/','’',$my_kw['term']);
				$my_kw['syns']=preg_replace('/\&\#8217;/','’',$my_kw['syns']);

					echo("[");
					
						//term
						echo('"'.preg_replace('/([-[\]{}()*+?.,\\/^$|#\s])/','\\\\\\\\$1',$my_kw['term']));
                        if(!empty($my_kw['syns'])){
                            echo('|'.preg_replace('/([-[\]{}()*+?.,\\/^$#\s])/','\\\\\\\\$1',$my_kw['syns']).'"');
                        }else{
							echo('"');
						}
						
						//case sensitive
						if($my_kw['case']){
							echo(",true");
						}else{
							echo(",false");
						}				
						
						//prefix
						if($my_kw['pref']){
							echo(",true");
						}else{
							echo(",false");
						}

						//categories class
                        echo(",'".$my_kw['families_class']."'");
 
                        //if there is a video put a video class
                        if(strlen($my_kw['youtube'])>5){
                            echo(",'tooltipy-kw-youtube'");
                        }else{
                            echo(",''");
                        }

						//icon	
						echo(",'".$my_kw['icon']."'");

                        //number of times the keyword is fetched
						echo(",0");
						
					echo("]");
				?>,
			<?php } ?>
			];
			tooltipIds=[
			<?php foreach($my_keywords_terms as $my_kw){ ?>
				"<?php echo($my_kw['kw_id']) ?>",
			<?php } ?>
			];
			
			//include or fetch zone
			<?php
			$settings= get_option('bluet_kw_settings');
			
			$options = get_option('bluet_kw_advanced');
			
			$kttg_cover_class='';
			$kttg_exclude_areas='';
		
			if(!empty($options['kttg_cover_areas'])){
				$kttg_cover_class=$options['kttg_cover_areas'];
				$kttg_cover_class=explode(" ",$kttg_cover_class);
			}

			$kttg_cover_tags = '';
		
			if(!empty($options['kttg_cover_tags'])){
				$kttg_cover_tags = $options['kttg_cover_tags'];
				$kttg_cover_tags = explode(" ",$kttg_cover_tags);
			}

			if(!empty($options['kttg_exclude_areas'])){
				$kttg_exclude_areas=$options['kttg_exclude_areas'];
				$kttg_exclude_areas=explode(" ",$kttg_exclude_areas);
				
			}
			?>
			var class_to_cover=[
						<?php
						if(!empty($kttg_cover_class)){
							foreach($kttg_cover_class as $cover_area){
								if($cover_area!=""){
									echo('".'.$cover_area.'",');
								}
							}
						}
						?>];
			var tags_to_cover=[
						<?php
						if(!empty($kttg_cover_tags)){
							foreach($kttg_cover_tags as $cover_tag){
								if($cover_tag!=""){
									echo( '"'.$cover_tag.'",');
								}
							}
						}
						?>];
			var areas_to_cover = class_to_cover.concat( tags_to_cover );

			if(areas_to_cover.length==0){//if no classes mentioned
				areas_to_cover.push("body");
			}

			fetch_all="<?php if(!empty($settings["bt_kw_match_all"]) and $settings["bt_kw_match_all"]=='on'){
					echo('g');
			}?>";


			//exclude zone block			
			{
				var zones_to_exclude=[
							".kttg_glossary_content", //remove tooltips from inside the glossary content
							"#tooltip_blocks_to_show", //remove tooltips from inside the tooltips
							<?php
							if(!empty($kttg_exclude_areas)){
								foreach($kttg_exclude_areas as $exclude_area){
									if($exclude_area!=""){
										echo('".'.$exclude_area.'",');
									}
								}
							}
							?>];
				<?php
				$kttg_exclude_anchor_tags = false;
				
				$kttg_exclude_heading_tags = array(false,false,false,false,false,false);

				$kttg_exclude_common_tags = array();

				$adv_options = get_option('bluet_kw_advanced');

				if(!empty($adv_options['kttg_exclude_anchor_tags']) and $adv_options['kttg_exclude_anchor_tags']=="on"){
					$kttg_exclude_anchor_tags=true;
				}

				if(!empty($adv_options['kttg_exclude_heading_tags'])){
					//heding h1 to h6
					$kttg_exclude_heading_tags=$adv_options['kttg_exclude_heading_tags'];
				}

				if(!empty($adv_options['kttg_exclude_common_tags'])){
					//heding h1 to h6
					$kttg_exclude_common_tags = $adv_options['kttg_exclude_common_tags'];
				}

				//if exclude anchor tags			
				if($kttg_exclude_anchor_tags){
					?>
						zones_to_exclude.push("a");
					<?php
				}

				for($i=1;$i<7;$i++){
					if(!empty($kttg_exclude_heading_tags["h".$i]) and $kttg_exclude_heading_tags["h".$i]=="on"){
					?>
						zones_to_exclude.push("h"+<?php echo($i); ?>);
					<?php 
					}
				}

				foreach ($kttg_exclude_common_tags as $tag => $val) {
				?>
					zones_to_exclude.push("<?php echo($tag); ?>");
				<?php 
				}
				?>
			}

				for(var j=0 ; j<areas_to_cover.length ; j++){					
					/*test overlapping classes*/
					var tmp_classes=areas_to_cover.slice(); //affectation par valeur
					//remove current elem from tmp tab
					tmp_classes.splice(j,1);

					//if have parents (to avoid overlapping zones)
						if(
							tmp_classes.length>0
							&&
							jQuery(areas_to_cover[j]).parents(tmp_classes.join(",")).length>0
						){
							continue;
						}
					/*end : test overlapping classes*/


					for(var cls=0 ; cls<jQuery(areas_to_cover[j]).length ; cls++){	
						zone=jQuery(areas_to_cover[j])[cls];
						//to prevent errors in unfound classes
						if (zone==undefined) {
							continue;
						}
					
						for(var i=0;i<kttg_tab.length;i++){

							suffix='';
							if(kttg_tab[i][2]==true){//if is prefix
								suffix='\\w*';
							}
							txt_to_find=kttg_tab[i][0];
							var text_sep='[\\s<>,;:!$^*=\\-()\'"&?.\\/§%£¨+°~#{}\\[\\]|`\\\^@¤]'; //text separator							
							
							//families for class
                            tooltipy_families_class=kttg_tab[i][3];
 
                            //video class
                            tooltipy_video_class=kttg_tab[i][4];

							/*test japanese and chinese*/
							var japanese_chinese=/[\u3000-\u303F]|[\u3040-\u309F]|[\u30A0-\u30FF]|[\uFF00-\uFFEF]|[\u4E00-\u9FAF]|[\u2605-\u2606]|[\u2190-\u2195]|\u203B/;
						    var jc_reg = new RegExp(japanese_chinese);
    						
							if(jc_reg.test(txt_to_find)){
								//change pattern if japanese or chinese text
								text_sep=""; //no separator for japanese and chinese
							}

							pattern=text_sep+"("+txt_to_find+")"+suffix+""+text_sep+"|^("+txt_to_find+")"+suffix+"$|"+text_sep+"("+txt_to_find+")"+suffix+"$|^("+txt_to_find+")"+suffix+text_sep;

							iscase='';
							if(kttg_tab[i][1]==false){
								iscase='i';
							}						
							var reg=new RegExp(pattern,fetch_all+iscase);

							if (typeof findAndReplaceDOMText == 'function') { //if function exists
							  findAndReplaceDOMText(zone, {
									<?php
										echo("preset: 'prose',");
										?>							
									find: reg,
									replace: function(portion) {

										splitted=portion.text.split(new RegExp(txt_to_find,'i'));
										txt_to_display=portion.text.match(new RegExp(txt_to_find,'i'));
										/*exclude zones_to_exclude*/
										zones_to_exclude_string=zones_to_exclude.join(", ");
										if(
											jQuery(portion.node.parentNode).parents(zones_to_exclude_string).length>0
											||
											jQuery(portion.node.parentNode).is(zones_to_exclude_string)
										){
											return portion.text;
										}
										/*avoid overlaped keywords*/
										if(
											jQuery(portion.node.parentNode).parents(".bluet_tooltip").length>0
											||
											jQuery(portion.node.parentNode).is(".bluet_tooltip")
										){
											return portion.text;
										}
										//number of appearence
										<?php
							if(!(!empty($settings["bt_kw_match_all"]) and $settings['bt_kw_match_all']=='on')){
										?>
										if(kttg_tab[i][6]==1){
											return portion.text;
										}
										<?php
							}
										?>
										
										kttg_tab[i][6]++;

										if(splitted[0]!=undefined){ before_kw = " "; }else{before_kw="";}
										if(splitted[1]!=undefined){ after_kw = " "; }else{after_kw="";}
										
										if(portion.text!="" && portion.text!=" " && portion.text!="\t" && portion.text!="\n" ){
											//console.log(i+" : ("+splitted[0]+"-["+txt_to_find+"]-"+splitted[1]+"-"+splitted[2]+"-"+splitted[3]+")");
											<?php 
												$options = get_option( 'bluet_kw_style' ); //to get the ['bt_kw_fetch_mode']
												
												//init added classes
												(!empty($options['bt_kw_add_css_classes']['keyword'])) 	? $css_classes_added_inline_keywords=$options['bt_kw_add_css_classes']['keyword'] 	: $css_classes_added_inline_keywords="";
												(!empty($options['bt_kw_add_css_classes']['popup'])) 	? $css_classes_added_popups=$options['bt_kw_add_css_classes']['popup'] 				: $css_classes_added_popups="";

												if(empty($options['bt_kw_fetch_mode']) or $options['bt_kw_fetch_mode']=='highlight'){
													//highlight
											?>
													var elem = document.createElement("span");

													if(before_kw==undefined || before_kw==null){
															before_kw="";
													}

													//extract icon if present
													kttg_icon='';

													if(kttg_tab[i][5]!=""){
														kttg_icon='<img src="'+kttg_tab[i][5]+'" >';
													}																					

													if(suffix!=""){														
														var reg=new RegExp(suffix,"");
														suff_after_kw=after_kw.split(reg)[0];
														
														if(after_kw.split(reg)[0]=="" && after_kw.split(reg)[1]!=undefined){
															suff_after_kw=after_kw.split(reg)[1];
														}

														if(suff_after_kw==undefined){
															suff_after_kw="";
														}														

														just_after_kw=after_kw.match(reg);
														if(just_after_kw==undefined || just_after_kw==null){
															just_after_kw="";
														}
														
														if(suff_after_kw==" "){
                                                            suff_after_kw="  ";
                                                        }

                                                        if(before_kw==" "){
                                                            before_kw="  ";
                                                        }
                                        /*console.log('('+suffix+')('+after_kw.split(reg)[1]+')');
  										console.log('['+after_kw+'] -'+suff_after_kw+'-');*/

  										            //with prefix
														elem.innerHTML=(txt_to_display==undefined || txt_to_display==null) ? before_kw+just_after_kw+suff_after_kw : before_kw+"<span class='bluet_tooltip tooltipy-kw-prefix' data-tooltip="+tooltipIds[i]+">"+kttg_icon+txt_to_display+""+just_after_kw+"</span>"+suff_after_kw;
                                                	}else{                                                          
                                                        if(after_kw==" "){
                                                            after_kw="  ";
                                                        }

                                                        if(before_kw==" "){
                                                            before_kw="  ";
                                                        }  
                                                        //without prefix                                              
                                                        elem.innerHTML=(txt_to_display==undefined || txt_to_display==null) ? before_kw+after_kw : before_kw+"<span class='bluet_tooltip' data-tooltip="+tooltipIds[i]+">"+kttg_icon+txt_to_display+"</span>"+after_kw;
                                                    }
													//add classes to keywords
                                                    jQuery(jQuery(elem).children(".bluet_tooltip")[0]).addClass("tooltipy-kw tooltipy-kw-"+tooltipIds[i]+" "+tooltipy_families_class+" "+tooltipy_video_class+" <?php echo($css_classes_added_inline_keywords); ?>");

													return elem;
												
											<?php
												}else{
													//icon
											?>
													var elem = document.createElement('span');
													if(suffix!=""){
														var reg=new RegExp(suffix,"");
														suff_after_kw=after_kw.split(reg)[1];
														if(suff_after_kw==undefined){
															suff_after_kw="";
														}
														//icon with prefix
														elem.innerHTML=(txt_to_display==undefined || txt_to_display==null)?before_kw+after_kw.match(reg)+suff_after_kw:before_kw+txt_to_display+after_kw.match(reg)+"<img src='<?php echo(plugins_url('/assets/qst-mark-1.png',__FILE__)); ?>' class='bluet_tooltip tooltipy-kw-prefix tooltipy-kw-icon' data-tooltip="+tooltipIds[i]+" />"+suff_after_kw;
                                                    }else{
                                                    	//icon without prefix
                                                        elem.innerHTML=(txt_to_display==undefined || txt_to_display==null)?before_kw+after_kw:before_kw+txt_to_display+"<img src='<?php echo(plugins_url('/assets/qst-mark-1.png',__FILE__)); ?>' class='bluet_tooltip tooltipy-kw-icon' data-tooltip="+tooltipIds[i]+" />"+after_kw;
                                                    }

                                                    //add classes to keywords
                                                    jQuery(jQuery(elem).children(".bluet_tooltip")[0]).addClass("tooltipy-kw tooltipy-kw-"+tooltipIds[i]+" "+tooltipy_families_class+" "+tooltipy_video_class+" <?php echo($css_classes_added_inline_keywords); ?>");

													return elem;
												
											<?php
													}
											?>	
										}else{
												return "";
										}																			
									}
								});
							}

						}		
					}
				}
			//trigger event sying that keywords are fetched
			jQuery.event.trigger("keywordsFetched");
		}
			/*end test*/
			
			jQuery(document).ready(function(){
				kttg_fetch_kws();
				
				bluet_placeTooltips(".bluet_tooltip, .bluet_img_tooltip","<?php echo($kttg_tooltip_position); ?>",true);	 
				animation_type="<?php echo($animation_type);?>";
				animation_speed="<?php echo($animation_speed);?>";
				moveTooltipElementsTop(".bluet_block_to_show");
			});
			
			jQuery(document).on("keywordsLoaded",function(){
				bluet_placeTooltips(".bluet_tooltip, .bluet_img_tooltip","<?php echo($kttg_tooltip_position); ?>",false);
			});

			/*	Lanch keywords fetching for a chosen event triggered - pro feature	*/
			<?php 
			$custom_events = ( array_key_exists('kttg_custom_events', $adv_options) ? $adv_options['kttg_custom_events'] : "");
			$custom_events_array = explode(",", $custom_events);

			foreach ($custom_events_array as $custom_event) {
				if($custom_event==""){
					continue;
				}
			?>
				jQuery("body").on('<?php echo($custom_event); ?>',function(){
					kttg_fetch_kws();
				});
			<?php 
			}
			?>

		</script>
				<?php
		if(!is_admin()){
			//if not in admin page
			?>
			<script>
				jQuery(document).ready(function(){				
						/*test begin*/
					load_tooltip="<span id='loading_tooltip' class='bluet_block_to_show' data-tooltip='0'>";
						load_tooltip+="<div class='bluet_block_container'>";									
							load_tooltip+="<div class='bluet_text_content'>";							
									load_tooltip+="<img width='15px' src='<?php echo plugins_url('/assets/loading.gif',__FILE__); ?>' />";
							load_tooltip+="</div>";						
						load_tooltip+="</div>";
					load_tooltip+="</span>";

					jQuery("#tooltip_blocks_to_show").append(load_tooltip);
					/*test end*/
				});
			</script>
			<?php
		}
}
	//call add filter for all hooks in need
	//you can pass cutom hooks you've done
	//(### do something here to support custom fields)
add_action('wp_head',function(){
	
	$contents_to_filter=array(
							array('the_content'),	//contents to filter to the post
							array('the_content')	//contents to filter to the page
						);
	
	/*get all posts (but not post type keywords)*/	
	$posttypes_to_match=array();//initial posttypes to match	
	$option_settings=get_option('bluet_kw_settings');
	
	 if(!empty($option_settings['bt_kw_for_posts']) and $option_settings['bt_kw_for_posts']=='on'){
        $posttypes_to_match[]='post';
    }
    
    if(!empty($option_settings['bt_kw_for_pages']) and $option_settings['bt_kw_for_pages']=='on'){
        $posttypes_to_match[]='page';
    }
	
	if(function_exists('bluet_kttg_pro_addon')){//if pro addon activated
		$contents_to_filter=apply_filters('bluet_kttg_dustom_fields_hooks',$contents_to_filter);
		$posttypes_to_match=apply_filters('bluet_kttg_posttypes_to_match',$posttypes_to_match);
	}

	foreach($posttypes_to_match as $k=>$the_posttype_to_match){
		if(!empty($contents_to_filter[$k]) and $contents_to_filter[$k]!=null){
			bluet_kttg_filter_any_content($the_posttype_to_match,$contents_to_filter[$k]);
		}
	}

}); //'other content hook' if needed


//Functions


/* enqueue js functions for the front side*/
function bluet_kw_load_scripts_front() {
	$options = get_option( 'bluet_kw_settings' );
	$anim_type=$options['bt_kw_animation_type'];

	if(!empty($anim_type) and $anim_type!="none"){
		wp_enqueue_style( 'kttg-tooltips-animations-styles', plugins_url('assets/animate.css',__FILE__), array(), false);
	}

    $frontend_style_file = apply_filters('tooltipy_stylesheet_url', plugins_url('assets/style.css',__FILE__) );
	wp_enqueue_style( 'tooltipy-default-style', $frontend_style_file, array(), false);

	//load jQuery once to avoid conflict
	wp_enqueue_script('kttg-tooltips-functions-script', plugins_url('assets/kttg-tooltip-functions.js',__FILE__), array('jquery'), TOOLTIPY_VERSION, true );
	
	//load mediaelement.js for audio and video shortcodes
	//change this to make it load only when shortcodes are loaded with keywords
	wp_enqueue_script('wp-mediaelement');
	wp_enqueue_style('wp-mediaelement');
	
	$opt_tmp=get_option('bluet_kw_style');
	if(!empty($opt_tmp['bt_kw_alt_img']) and $opt_tmp['bt_kw_alt_img']=='on'){
		//
		wp_enqueue_script( 'kttg-functions-alt-img-script', plugins_url('assets/img-alt-tooltip.js',__FILE__), array('jquery'), TOOLTIPY_VERSION, true );
	}
}

function bluet_kw_activation(){
	$style_options=array();
	
	//initialise style option if bluet_kw_style is empty
	$style_options=array(
		'bt_kw_tt_color'=>'inherit',
		'bt_kw_tt_bg_color'=>'#0D45AA',
		
		'bt_kw_desc_color'=>'#ffffff',
		'bt_kw_desc_bg_color'=>'#5eaa0d',
		
		'bt_kw_desc_font_size'=>'14',
		
		'bt_kw_on_background' =>'on'
	);
	
	if(!get_option('bluet_kw_style')){
		add_option('bluet_kw_style',$style_options);
	}
	
	$settings_options=array();
	//initialise settings option if empty
	$settings_options=array(
		'bt_kw_for_posts'=>'on',
		'bt_kw_match_all'=>'on',
		'bt_kw_position'=>'bottom'		
	);
	
	if(!get_option('bluet_kw_settings')){
		add_option('bluet_kw_settings',$settings_options);
	}
}

function bluet_kttg_filter_any_content($post_type_to_filter,$filter_hooks_to_filter){
	//this function filters a specific posttype with specific filter hooks
	$my_post_id=get_the_id();
	$exclude_me = get_post_meta($my_post_id,'bluet_exclude_post_from_matching',true);			

	//if the current post tells us to exclude from fetch
	//or the post type is not appropriate
	
	if($post_type_to_filter!=get_post_type($my_post_id)){
		return false;
	}
	if($post_type_to_filter=='post' and !is_single($my_post_id)){
		return false;
	}
	foreach($filter_hooks_to_filter as $hook){
		add_filter($hook,'kttg_filter_posttype',100000);//priority to 100 000 to avoid filters after it		
	}
}

function kttg_specific_plugins($cont){
	//specific modification so it can work for fields of "WooCommerce Product Addons"
	foreach($cont as $k=>$c_arr){
		//for description field
		$cont[$k]['description']=kttg_filter_posttype($c_arr['description']);
	}	
	return $cont;
}
		
function kttg_filter_posttype($cont){
	global $tooltip_post_types;
	/*28-05-2015*/
	//specific modification so it can work for "WooCommerce Product Addons" and other addons
	if(is_array($cont)){
		$cont=kttg_specific_plugins($cont);		
		return $cont;
	}				
	/*28-05-2015 end*/

	$my_post_id=get_the_id();
	$exclude_me = get_post_meta($my_post_id,'bluet_exclude_post_from_matching',true);			
	
	global $is_kttg_glossary_page;
	if($exclude_me OR $is_kttg_glossary_page){
		return $cont;
	}

	//glossary settings
	$bluet_kttg_show_glossary_link=get_option('bluet_kw_settings');		
	if(!empty($bluet_kttg_show_glossary_link['bluet_kttg_show_glossary_link'])){
		$bluet_kttg_show_glossary_link=$bluet_kttg_show_glossary_link['bluet_kttg_show_glossary_link'];
	}else{
		$bluet_kttg_show_glossary_link=false;
	}
	

	$option_settings=get_option('bluet_kw_settings');

	//var dans la quelle on cache les tooltips a afficher
	$html_tooltips_to_add='<div class="my_tooltips_in_block">';		

	$my_keywords_ids=kttg_get_related_keywords($my_post_id);
	
	//if user specifies keywords to match
	$bluet_matching_keywords_field=get_post_meta($my_post_id,'bluet_matching_keywords_field',true);
	if(!empty($bluet_matching_keywords_field)){
		$my_keywords_ids=$bluet_matching_keywords_field;
	}                                    
					
	   $options=get_option('bluet_kw_advanced');    
 
    if(empty($options['kttg_fetch_all_keywords'])){
        $kttg_fetch_all_keywords=false; 
    }else if($options['kttg_fetch_all_keywords']=="on"){
        $kttg_fetch_all_keywords=true;
    }

	if(!empty($my_keywords_ids) OR $kttg_fetch_all_keywords){
		$my_keywords_terms=array(); 
							
		$post_in=$my_keywords_ids;

		// The Query                                                                          
		$wk_args=array(
			'post_type'=>$tooltip_post_types,
			'posts_per_page'=>-1	//to retrieve all keywords
		);
		
		if(!$kttg_fetch_all_keywords){
			$wk_args['post__in']=$post_in;
		}

		$the_wk_query = new WP_Query($wk_args);

		// The Loop
		if ( $the_wk_query->have_posts() ) {

			while ( $the_wk_query->have_posts() ) {
				$the_wk_query->the_post();
				
				if(get_the_title()!=""){ //to prevent untitled keywords
					$tmp_array_kw=array(
						'kw_id'=>get_the_id(),
						'term'=>get_the_title(),
						'case'=>false,
						'pref'=>false,
						'syns'=>get_post_meta(get_the_id(),'bluet_synonyms_keywords',true),
						'youtube'=>get_post_meta(get_the_id(),'bluet_youtube_video_id',true),
						'dfn'=>get_the_content(),
						'img'=>get_the_post_thumbnail(get_the_id(),'medium')
					);
					
					if(get_post_meta(get_the_id(),'bluet_case_sensitive_word',true)=="on"){
						$tmp_array_kw['case']=true;
					}
					
					//if prefix addon activated
					if(function_exists('bluet_prefix_metabox')){
						if(get_post_meta(get_the_id(),'bluet_prefix_keywords',true)=="on"){
							$tmp_array_kw['pref']=true;
						}
					}

					$my_keywords_terms[]=$tmp_array_kw;
				}							
				
			}
			
		}
		
		/* Restore original Post Data */
		wp_reset_postdata();
			
			// first preg replace to eliminate html tags 						
				$regex='<\/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)\/?>';							
				$out=array();
				preg_match_all('#('.$regex.')#iu',$cont,$out);
				
				if(!function_exists('bluet_kttg_pro_addon')){
					$cont=preg_replace('#('.$regex.')#i','**T_A_G**',$cont); //replace tags by **T_A_G**							
				}
			//end
			
            $limit_match=((!empty($option_settings['bt_kw_match_all']) and $option_settings['bt_kw_match_all']=='on')? -1 : 1);
			
			/*tow loops montioned here to avoid overlapping (chevauchement) */
			foreach($my_keywords_terms as $id=>$arr){
				$term=$arr['term'];
				
				//concat synonyms if they are not empty
				if($arr['syns']!=""){
					$term.='|'.$arr['syns'];
				}

				$is_prefix=$arr['pref'];

				if(function_exists('bluet_prefix_metabox') and $is_prefix){
						$kw_after='\w*';
				}else{
					$kw_after='';
				}
				
				$term_and_syns_array=explode('|',$term);

				//sort keywords by string length in the array (to match them properly)
				usort($term_and_syns_array,'kttg_length_compare');
				
				//verify if case sensitive
				if($arr['case']){
					$kttg_case_sensitive='';
				}else{
					$kttg_case_sensitive='i';
				}							
				foreach($term_and_syns_array as $temr_occ){
					$temr_occ=elim_apostrophes($temr_occ);
					$cont=elim_apostrophes($cont);
					
					if(!function_exists('bluet_kttg_pro_addon')){
						$cont=preg_replace('#((\W)('.$temr_occ.''.$kw_after.')(\W))#u'.$kttg_case_sensitive,'$2__$3__$4',$cont,$limit_match);
					}
				}					

			}

			foreach($my_keywords_terms as $id=>$arr){
				$term=$arr['term'];
				
				//concat synonyms if they are not empty
				if($arr['syns']!=""){
					$term.='|'.$arr['syns'];
				}

				$img=$arr['img'];
				$dfn=$arr['dfn'];
				$is_prefix=$arr['pref'];
				$video=$arr['youtube'];

				if(function_exists('bluet_prefix_metabox') and $is_prefix){
						$kw_after='\w*';
				}else{
					$kw_after='';
				}		
				
				if($dfn!=""){
					$dfn=$arr['dfn'];
				}
				
				$html_to_replace='<span class="bluet_tooltip" data-tooltip="'.$arr["kw_id"].'">$2</span>';
				
				$term_and_syns_array=explode('|',$term);

				$kttg_term_title=$term_and_syns_array[0];
				if($video!="" and function_exists('bluet_kttg_all_tooltips_layout')){
					$html_tooltips_to_add.=bluet_kttg_all_tooltips_layout(
			/*text=*/	$dfn,
			/*image=*/	'',
						$video,
						$arr["kw_id"]
					);
				}else{
					$html_tooltips_to_add.=bluet_kttg_tooltip_layout(
						$kttg_term_title 	//title
						,$dfn				//content def
						,$img				//image
						,$arr["kw_id"]		//id
						,$bluet_kttg_show_glossary_link	//show glossary link y/n
						);
				}

				
				//verify if case sensitive
				if($arr['case']){
					$kttg_case_sensitive='';
				}else{
					$kttg_case_sensitive='i';
				}								
				foreach($term_and_syns_array as $temr_occ){
					$temr_occ=elim_apostrophes($temr_occ);
					$cont=elim_apostrophes($cont);

					if(!function_exists('bluet_kttg_pro_addon')){
						$cont=preg_replace('#(__('.$temr_occ.''.$kw_after.')__)#u'.$kttg_case_sensitive,$html_to_replace,$cont,-1);
					}
				}
			}
			
			//Reinsert tag HTML elements
			if(!function_exists('bluet_kttg_pro_addon')){
				foreach($out[0] as $id=>$tag){						
					$cont=preg_replace('#(\*\*T_A_G\*\*)#',$tag,$cont,1);
				}
			}
			//prevent HTML Headings (h1 h2 h3) to be matched
			$regH='(<h[1-3]+>.*)(class="bluet_tooltip")(.*<\/h[1-3]+>)';						

			if(!function_exists('bluet_kttg_pro_addon')){
				$cont=preg_replace('#('.$regH.')#iu','$2$4',$cont);					
			}
	}			

	$html_tooltips_to_add=apply_filters('kttg_another_tooltip_in_block',$html_tooltips_to_add);
	$html_tooltips_to_add.="</div>";

	$cont=$html_tooltips_to_add.$cont;
	return do_shortcode($cont);//do_shortcode to return content after executing shortcodes
}


/**
*	tooltipy_remove_plugins_filters : Removes the filters applied on the tooltip contents (advanced feature)
*/
function tooltipy_remove_plugins_filters() {
    global $post, $default_tooltipy_post_type_name;

    $tooltipy_post_type_name = get_option("tooltipy_post_type_name", $default_tooltipy_post_type_name);
    $options = get_option( 'bluet_kw_advanced' );
	
    $prevent_plugins_filters_option = (!empty($options['prevent_plugins_filters']) ? $options['prevent_plugins_filters'] : false );

    if( $prevent_plugins_filters_option == "on" ){
	    if ( $tooltipy_post_type_name == $post->post_type ){
			remove_all_filters( 'the_content', 10 );
	    }
    }
}
add_action( 'the_post', 'tooltipy_remove_plugins_filters' );