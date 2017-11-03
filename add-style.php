<?php
defined('ABSPATH') or die("No script kiddies please!");

function bluet_kw_custom_style(){
	
	if(function_exists('bluet_kttg_pro_addon')){//if pro addon activated
	
		$adv_options=get_option('bluet_kw_advanced');
		if(!empty($adv_options['bt_kw_adv_style']['apply_custom_style_sheet'])){
			$apply_custom_style_sheet=$adv_options['bt_kw_adv_style']['apply_custom_style_sheet'];
		
			/*
				If apply custom sheet is activated so don't load this style file			
			*/
			if($apply_custom_style_sheet){
				return false;
			}
		}		
	}
	
	$style_options=get_option('bluet_kw_style');

	/**/
	$tooltip_color=$style_options['bt_kw_tt_color'];
	$tooltip_bg_color=$style_options['bt_kw_tt_bg_color'];

	if(!empty($style_options['bt_kw_on_background'])){
		$bt_kw_on_background=$style_options['bt_kw_on_background'];
	}else{
		$bt_kw_on_background=null;
	}

	/**/
	$desc_color=$style_options['bt_kw_desc_color'];
	$desc_bg_color=$style_options['bt_kw_desc_bg_color'];
	
	$desc_font_size=(empty($style_options['bt_kw_desc_font_size'])? 17 : $style_options['bt_kw_desc_font_size']);
	$desc_width=(empty($style_options['bt_kw_tooltip_width'])? 400 : $style_options['bt_kw_tooltip_width']);
	
	$is_important="";
	
	if(!is_admin()){ 
		$is_important=" !important";
	}
	?>
	<script>
		//apply keyword style only if keywords are Fetched
		jQuery(document).on("keywordsFetched",function(){
			jQuery(".bluet_tooltip").each(function(){

//console.log(jQuery(this).prop("tagName"));

				if(jQuery(this).prop("tagName")!="IMG"){
					jQuery(this).css({
						"text-decoration": "none",
						"color": "<?php echo $tooltip_color; ?>",
						
						<?php
							if(!$bt_kw_on_background){
								echo('"background": "'.$tooltip_bg_color.'",');
								
								echo('"padding": "1px 5px 3px 5px",');
								echo('"font-size": "1em"');
							}else{
								echo('"border-bottom": "1px dotted",');
								echo('"border-bottom-color": "'.$tooltip_color.'"');
							}
						?>
					});
				}

			});
		});
	</script>

	<style>
	/*for alt images tooltips*/
	.bluet_tooltip_alt{
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background-color: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
	}
	

	
	.bluet_block_to_show{
		max-width: <?php echo($desc_width); ?>px;
	}
	.bluet_block_container{		  
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
		box-shadow: 0px 0px 10px #717171 <?php echo($is_important)?>;
		font-size:<?php echo $desc_font_size; ?>px <?php echo($is_important)?>;
	}
	
	img.bluet_tooltip {
	  /*border: none;
	  width:<?php echo $desc_font_size; ?>px;*/
	}

	.kttg_arrow_show_bottom:after{
		border-bottom-color: <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_top:after{
		border-top-color: <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_right:after{
		border-top-color: <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_left:after{
		border-top-color: <?php echo $desc_bg_color; ?>;
	}

	@media screen and (max-width:400px){
		.bluet_hide_tooltip_button{
		    color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		    /*background-color: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;*/
		}
	}
	</style>
	<?php
}
