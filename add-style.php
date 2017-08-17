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
			});
		});
	</script>

	<style>

	/*for alt images tooltips*/
	.bluet_tooltip_alt{
		max-width: 250px;
		padding: 1px 5px;
		text-align: center;
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background-color: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
		position: absolute;
		border-radius: 4px;
		z-index:9999999999;
	}
	

	
	.bluet_block_to_show{
		display:none;
		opacity:0;		
		max-width: <?php echo($desc_width); ?>px;
		z-index:9999;
		padding:10px;
		
		position: absolute;
		height: auto;
	}
	.bluet_block_container{		  
		color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
		background: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;
		border-radius: 2px;
		box-shadow: 0px 0px 10px #717171 <?php echo($is_important)?>;
		font-size:<?php echo $desc_font_size; ?>px <?php echo($is_important)?>;
		font-weight: normal;
		display:inline-block;
		width:inherit;
	}
	
	.bluet_img_in_tooltip{	
		border-radius: inherit;
	}

	.bluet_img_in_tooltip img
	{
		float:left;
		margin-bottom:8px;
		border: none !important;
		border-radius: inherit;
		width:100%;
		height: auto;
		margin-bottom: 0px;
	}
	
	img.bluet_tooltip {
	  border: none;
	  width:<?php echo $desc_font_size; ?>px;
	}

	.bluet_text_content p:last-child {
	  margin-bottom: 0px;
	}
	
	.bluet_text_content{
		padding: 10px 15px 7px 15px;		
	}
	.bluet_block_to_show:after {
	  content: '';
	  position: absolute;
	  left: 50%;
	  margin-left: -8px;
	  width: 0;
	  height: 0;
	  border-right: 8px solid transparent;
	  border-left: 8px solid transparent;
	}
	
	.kttg_arrow_show_bottom:after{
		top:3px;
		border-bottom: 7px solid <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_top:after{
		bottom: 3px;
		border-top: 7px solid <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_right:after{
		bottom: 3px;
		border-top: 7px solid <?php echo $desc_bg_color; ?>;
	}
	
	.kttg_arrow_show_left:after{
		bottom: 3px;
		border-top: 7px solid <?php echo $desc_bg_color; ?>;
	}
	
	.bluet-hide-excluded{
		display:none;
	}
	
	.bluet_title_on_block{
		text-transform: capitalize;
		font-weight: bold;
	}
	
	/* Glossary style */
	span.bluet_glossary_letter a {
		text-decoration: none !important;
		padding: 3px;
		background-color: beige;
		border-radius: 3px;
	}
	span.bluet_glossary_letter a:hover {
		background-color: rgb(108, 108, 108) !important;
		color: white;
	}
	span.bluet_glossary_letter a:hover .bluet_glossary_letter_count{
		color: white;
	}
	.bluet_glossary_all a {
		text-decoration: none !important;
		padding: 3px;
		background-color: bisque;
		font-weight: bold;
		border-radius: 3px;
	}
	.bluet_glossary_letter_count {
		vertical-align: super;
		font-size: 70%;
		color: crimson;
		padding-left: 2px;
	}
	.bluet_glossary_found_letter{
		font-weight: bold;
	}
	span.kttg_glossary_nav {
		background-color: bisque;
		padding: 5px;
	}
	
	.bluet_glossary_current_letter a {
		background-color: rgb(108, 108, 108) !important;
		color: white;
		border-color: rgb(69, 69, 69);
		border-style: solid;
		border-width: 2px;
	}
	.bluet_glossary_current_letter .bluet_glossary_letter_count {
	  color: white;
	}

	.kttg_glossary_content {
	  padding: 15px 0px;
	}
	.kttg_glossary_content ul {
	  margin-bottom: 0px;
	  margin-left: 0px;
	}	
	.kttg_glossary_element_content {
	  margin-left: 15px;
	  padding-left: 10px;
	  margin-bottom: 20px;
	  border-left: 2px grey solid;
	}
	
	/* hide button */
	.bluet_hide_tooltip_button{
		display : none;
	}
	.kttg_fast{
		-webkit-animation-duration: 0.5s !important;
		-moz-animation-duration: 0.5s !important;
		-ms-animation-duration: 0.5s !important;
		-o-animation-duration: 0.5s !important;
		animation-duration: 0.5s !important;
	}
	
	.kttg_slow{
		-webkit-animation-duration: 2s !important;
		-moz-animation-duration: 2s !important;
		-ms-animation-duration: 2s !important;
		-o-animation-duration: 2s !important;
		animation-duration: 2s !important;
	}

	@media screen and (max-width:400px){
		.bluet_block_to_show{
			position: fixed;
			bottom: 0px;
			left: 0px;
			right: 0px;
			max-width: 100% !important;
			max-height: 95% !important;			
			padding: 0px !important;
			overflow: auto;
		}

		.bluet_block_container{			
			width: 100%;

		}
		.bluet_hide_tooltip_button{
			    opacity: 0.7;
		        position: absolute;
			    font-family: 'Open sans';
			    right: 2px;
			    top: 2px;
			    display: block;
			    color: <?php echo $desc_color; ?> <?php echo($is_important)?>;
			    /*background-color: <?php echo $desc_bg_color; ?> <?php echo($is_important)?>;*/
			    height: 26px;
			    font-size: 31px;
			    border-radius: 50%;
			    font-weight: bold;
			    line-height: 0px;
			    padding: 11px 5px;
			    cursor: pointer;
		}
	}
	
	/*admin*/
	span.class_val{
		  margin-right: 5px;
	}
	span.elem_class {
		color: white;
		margin-top: 3px;
		border-radius: 5px;
		padding-left: 15px;	
		margin-right: 10px !important;	  
	}
	#cover_areas_list .elem_class {
	  background-color: cornflowerblue;
	}
	#exclude_areas_list .elem_class {
	  background-color: indianred;
	}
	.easy_tags-list{
		display: inline-block;
		margin-left: 5px !important;
	}
	.easy_tags-content{
		border: 1px solid #dcdcdc;
		max-width: 500px;
		background-color: white;
		display: inline-block;
	}
	.easy_tags-add{
		display: inline-block;
	}
	.easy_tags-field{
		width: 100px;
		border: none !important;
		box-shadow: none !important;
		outline: none !important;
		background: transparent;
		vertical-align: top;
	}
	.easy_tags-field:focus{
		border: none !important;
  		box-shadow: none !important;
	}
	a.nav-tab{
	  cursor: pointer !important;
	}

	.kttg_glossary_element_title sub {
	  font-size: 50%;
	}
	#kttg_exclude_headings_zone label {
    	display: inline-block;
	}
	#kttg_exclude_headings_zone h1,
	#kttg_exclude_headings_zone h2,
	#kttg_exclude_headings_zone h3,
	#kttg_exclude_headings_zone h4,
	#kttg_exclude_headings_zone h5,
	#kttg_exclude_headings_zone h6 {
	    margin: 0px !important;
	    padding: 0px !important;
	}
	</style>
	<?php
}
