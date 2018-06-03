<?php
defined('ABSPATH') or die("No script kiddies please!");

/*adding custom style*/
require_once 'add-style.php';
require_once 'functions.php';
require_once 'settings-glossary.php';

add_action('wp_head','bluet_kw_custom_style');
add_action('admin_head','bluet_kw_custom_style');

//actions after update or add setting option

// add_action('update_option_bluet_kw_settings','_________');
// add_action('add_option_bluet_kw_settings','____________');

function is_tooltipy_settings_page(){
	//global $current_screen;
	//$current_screen=get_current_screen();		
	global $tooltipy_post_type_name;
	// vars
	$return = false;
	//var_dump($GLOBALS);
	//in the Tooltipy settings page ?
	if(!empty($GLOBALS["_GET"])){
		if(
			!empty($GLOBALS["_GET"]['post_type'])
			and
			$GLOBALS["_GET"]['post_type'] == $tooltipy_post_type_name
			and
			
			!empty($GLOBALS["_GET"]['page'])
			and
			$GLOBALS["_GET"]['page'] == "my_keywords_settings"			
		){
			$return = true;
		}
	}
	
	// return
	return $return;
}

/* enqueue js functions  for test only*/
function bluet_kw_load_scripts() {
	$options = get_option( 'bluet_kw_settings' );

	if(!empty($options['bt_kw_animation_type']) and $options['bt_kw_animation_type']!="none"){
		wp_enqueue_style( 'kttg-tooltips-animations-styles', plugins_url('assets/animate.css',__FILE__), array(), false);
	}
	
	//
	wp_enqueue_script( 'kttg-admin-tooltips-functions-script', plugins_url('assets/kttg-tooltip-functions.js',__FILE__), array('jquery'), TOOLTIPY_VERSION, true );
	
	// Add the color picker css file       
    wp_enqueue_style( 'wp-color-picker' ); 
         
    // Include our custom jQuery file with WordPress Color Picker dependency
    wp_enqueue_script( 'kttg-colorpicker-custom-script', plugins_url( 'assets/colorpicker-custom-script.js', __FILE__ ), array( 'wp-color-picker','jquery'), TOOLTIPY_VERSION, true ); 
}

add_action( 'admin_enqueue_scripts', 'bluet_kw_add_settings_script' );

if(is_tooltipy_settings_page()){
	add_action( 'admin_head', 'bluet_kw_load_scripts' );	
}

function bluet_kw_add_settings_script() {
 
	wp_enqueue_script( 'kttg-settings-functions-script', plugins_url('assets/settings-functions.js',__FILE__), array('jquery'), TOOLTIPY_VERSION, true );
}

/**end ColorPicker Implementation**/

/*-----------------------------------------------*
 * Sections, Settings, and Fields
/*-----------------------------------------------*

/**
 * Registers a new settings field on the 'General Settings' page of the WordPress dashboard.
 */
add_action( 'admin_init',function () {

/******************* sections */
	// 1st section
	add_settings_section(
		'concern_section',					
		__('Tooltips settings :','tooltipy-lang'),					
		'bluet_kw_sttings_display',		
		'my_keywords_settings'				
	);
	
	// 2nd section
	add_settings_section(
		'style_section',					// The ID to use for this section in attribute tags
		__('Customise the tooltip style :','tooltipy-lang'),					// The title of the section rendered to the screen
		'bluet_kw_style_display',		// The function used to render the options for this section
		'my_keywords_style'				// The ID of the page on which this section is rendered
	);

	// 3rd section (Highlight fetch mode)
	add_settings_section(
		'highlight_fetch_mode_section',
		__('Highlight fetch mode :','tooltipy-lang'),
		'bluet_kw_highlight_fetch_mode_display',
		'my_highlight_fetch_mode'
	);
	
	
/******************* fields */
//kttg_tooltip_post_types	
	// Define the match all settings field
	add_settings_field( 
		'kttg_tooltip_post_types', 					
		__('Get tooltips from','tooltipy-lang'), 			
		'tltpy_tooltip_post_types_display', 		
		'my_keywords_settings',				
		'concern_section'					
	);

	// Define the match all settings field
	add_settings_field( 
		'bt_kw_match_all_field', 					
		__('Match once or all occurrences','tooltipy-lang'), 			
		'bt_kw_match_all_display', 		
		'my_keywords_settings',				
		'concern_section'					
	);

	// Define the hide title settings field
	add_settings_field( 
		'bt_kw_hide_title', 					
		__('Tooltip title','tooltipy-lang'), 			
		'bt_kw_hide_title_display', 		
		'my_keywords_settings',				
		'concern_section'					
	);
	
	// Define the position settings field
	add_settings_field( 
		'bt_kw_position', 					
		__('Tooltip position','tooltipy-lang'), 			
		'bt_kw_position_display', 		
		'my_keywords_settings',				
		'concern_section'					
	);
	// Define the anomation type
	add_settings_field( 
		'bt_kw_animation_type', 					
		__('Animation','tooltipy-lang'), 			
		'bt_kw_animation_type_display', 		
		'my_keywords_settings',				
		'concern_section'					
	);

	//fetch mode
	add_settings_field( 
		'bt_kw_fetch_mode', 
		__('Fetch mode','tooltipy-lang'), 			
		'bt_kw_fetch_mode_display', 		
		'my_keywords_style',				
		'style_section'					
	);
	// Define the settings field for tooltip font color
	add_settings_field( 
		'bt_kw_tt_colour', 					// The ID (or the name) of the field
		__('Keyword style','tooltipy-lang'), 			// The text used to label the field
		'bt_kw_tt_colour_display', 		// The callback function used to render the field
		'my_highlight_fetch_mode',				// The page on which we'll be rendering this field
		'highlight_fetch_mode_section'					// The section to which we're adding the setting
	);
	
	add_settings_field( 
		'bt_kw_desc_colour', 					
		__('Description tooltip style','tooltipy-lang'), 			
		'bt_kw_desc_colour_display', 		
		'my_highlight_fetch_mode',				
		'highlight_fetch_mode_section'					
	);

	add_settings_field( 
		'bt_kw_tooltip_width', 					
		__('Tooltip width','tooltipy-lang'),
		'bt_kw_tooltip_width_display',
		'my_keywords_style',			
		'style_section'					
	);
	
	add_settings_field( 
		'bt_kw_desc_font_size', 					
		__('Description tooltip Font size','tooltipy-lang'), 			
		'bt_kw_desc_font_size_display', 		
		'my_keywords_style',				
		'style_section'					
	);

	add_settings_field( 
		'bt_kw_alt_img', 					
		__('Activate tooltips for images ?','tooltipy-lang'),
		'bt_kw_alt_img_display',
		'my_keywords_style',			
		'style_section'					
	);
	
	/* add CSS classes */
	add_settings_field( 
		'bt_kw_add_css_classes', 					
		__('Add CSS classes','tooltipy-lang'),
		'bt_kw_add_css_classes_display',
		'my_keywords_style',			
		'style_section'					
	);
	
/******************* registrations */
	//for settings options
	register_setting(
		'settings_group',					// The name of the group of settings
		'bluet_kw_settings'					// The name of the actual option (or setting)
	);
	
	//for style options
	register_setting(
		'settings_group',					// The name of the group of settings
		'bluet_kw_style'					// The name of the actual option (or setting)
	);	

}); // end bluet_kw_settings_registration


//create submenu
add_action('admin_menu',function(){
	global $tooltipy_post_type_name;
	
	add_submenu_page(
		'edit.php?post_type='.$tooltipy_post_type_name,
		__('KeyWords Settings','tooltipy-lang'), 
		__('Settings'), 
		'manage_options', 
		'my_keywords_settings', 
		'bluet_kw_render_settings_page'
	);
	
});
/*-----------------------------------------------*
 * Callbacks
/*-----------------------------------------------*

/**
 * Renders the content of the options page for the 	
*/

function bt_kw_tooltip_width_display(){
	//width tooltip render function	
	$options = get_option( 'bluet_kw_style' );
	?>
	<input id="bt_kw_tooltip_width_id" type="number" min="1" max="5000" name="bluet_kw_style[bt_kw_tooltip_width]" value="<?php echo $options['bt_kw_tooltip_width']; ?>"> px<?php
}

function bt_kw_alt_img_display(){
	//img atl tooltip render function	
	$options = get_option( 'bluet_kw_style' );
	?>
	<input type="checkbox" 	id="bt_kw_alt_img" 	name="bluet_kw_style[bt_kw_alt_img]" <?php if(!empty($options['bt_kw_alt_img']) and $options['bt_kw_alt_img']=='on') echo 'checked'; ?>/><?php _e("alt property of the images will be displayed as a tooltip",'tooltipy-lang'); ?><br><?php
}

function bt_kw_add_css_classes_display(){
	//add css clsses function	
	$options = get_option( 'bluet_kw_style' );
	?>
	<p><label>
		<input id="bt_kw_keyword_classes_id" type="text" name="bluet_kw_style[bt_kw_add_css_classes][keyword]" value="<?php if(!empty($options['bt_kw_add_css_classes']['keyword'])) echo $options['bt_kw_add_css_classes']['keyword']; ?>"> <?php _e("To inline keywords",'tooltipy-lang'); ?>
	</label></p>

	<p><label>
		<input id="bt_kw_popup_classes_id" type="text" name="bluet_kw_style[bt_kw_add_css_classes][popup]" value="<?php if(!empty($options['bt_kw_add_css_classes']['popup'])) echo $options['bt_kw_add_css_classes']['popup']; ?>"> <?php _e("To tooltips",'tooltipy-lang'); ?>
	</label></p>

	<p><?php echo(" <i>".__("Separated with spaces, please don't use special characters",'tooltipy-lang')."</i>"); ?></p>
	
	<?php

}
function bt_kw_desc_font_size_display(){
//font size field render function	
	$options = get_option( 'bluet_kw_style' );
	?>
			<input id="bt_kw_desc_font_size_id" type="number" min="1" max="50" name="bluet_kw_style[bt_kw_desc_font_size]" value="<?php echo $options['bt_kw_desc_font_size']; ?>"> px
	<?php
}
function bt_kw_desc_colour_display(){
	//colour field render function	
	$options = get_option( 'bluet_kw_style' );
	?>
		<?php _e('Description Background Colour','tooltipy-lang'); ?> : <br>
			<input id="aaa" type="text" class="color-field" name="bluet_kw_style[bt_kw_desc_bg_color]" value="<?php echo $options['bt_kw_desc_bg_color']; ?>">
		<br><?php _e('Description font Colour','tooltipy-lang'); ?> :<br>
			<input type="text" class="color-field" name="bluet_kw_style[bt_kw_desc_color]" value="<?php echo $options['bt_kw_desc_color']; ?>">
	<?php
}

function bt_kw_fetch_mode_display(){
	//colour field render function	
	$options = get_option( 'bluet_kw_style' ); //to get the ['bt_kw_fetch_mode']
	?>
		<p>
			<input value="highlight" id="bt_kw_fetch_mode-highlight" type="radio" name="bluet_kw_style[bt_kw_fetch_mode]" <?php if(empty($options['bt_kw_fetch_mode']) or $options['bt_kw_fetch_mode']=='highlight') echo 'checked'; ?>/>
			<label for="bt_kw_fetch_mode-highlight"><?php _e('Highlight Mode','tooltipy-lang'); ?></label>
		</p>

		<p>
			<input value="icon" id="bt_kw_fetch_mode-icon" type="radio" name="bluet_kw_style[bt_kw_fetch_mode]" <?php if(!empty($options['bt_kw_fetch_mode']) and $options['bt_kw_fetch_mode']=='icon') echo 'checked'; ?>/>
			<label for="bt_kw_fetch_mode-icon"><?php _e('Icon Mode','tooltipy-lang'); ?></label>
		</p>
	<?php
}

function bt_kw_tt_colour_display(){
	//colour field render function	
	$options = get_option( 'bluet_kw_style' );
	?>
		<?php _e('Background Colour','tooltipy-lang'); ?> : <br><p><input id="bluet_kw_no_background" type="checkbox" name="bluet_kw_style[bt_kw_on_background]" <?php if(!empty($options['bt_kw_on_background']) and $options['bt_kw_on_background']) echo 'checked'; ?>/><label for="bluet_kw_no_background" style="border-bottom: black 1px dotted;"><?php _e('No background (Dotted style)','tooltipy-lang'); ?></label></p><div id="bluet_kw_bg_hide"><input  type="text" class="color-field" name="bluet_kw_style[bt_kw_tt_bg_color]" value="<?php echo $options['bt_kw_tt_bg_color']; ?>"></div>
		<br><?php _e('Font Colour','tooltipy-lang'); ?> : <br><input  type="text" class="color-field" name="bluet_kw_style[bt_kw_tt_color]" value="<?php echo $options['bt_kw_tt_color']; ?>">

	<?php
}

function bluet_kw_sttings_display(){
	echo('<div id="keywords-settings">'.__('General tooltips settings','tooltipy-lang').'.</div>');
}
function bluet_kw_style_display(){
	_e('Make your own style.','tooltipy-lang');
}
function bluet_kw_highlight_fetch_mode_display(){
	_e('Style for the highlight fetch mode.','tooltipy-lang');
}

function tltpy_tooltip_post_types_display(){
	global $tooltipy_post_type_name;
	
	$options = get_option('bluet_kw_settings');
?>
	<select multiple name="bluet_kw_settings[kttg_tooltip_post_types][]" size="10">
		<?php
			
			foreach (get_post_types() as $post_tipe_tt) {
		?>
				<option
					value="<?php echo($post_tipe_tt); ?>"
					<?php if(!empty($options['kttg_tooltip_post_types']) and in_array($post_tipe_tt,$options['kttg_tooltip_post_types'])){ echo 'selected'; } ?> ><?php echo($post_tipe_tt); ?>
				</option>
		<?php
			}
		?>
	</select>
	<div>
		<?php
		_e('Select post types from which you want to get tooltips (default post type : ','tooltipy-lang'); echo($tooltipy_post_type_name);
		?>
	</div>
<?php
		if(empty($options['kttg_tooltip_post_types']) or !in_array($tooltipy_post_type_name,$options['kttg_tooltip_post_types'])){
			?>
			<div style="color:red;">
				<?php _e('Worning');
				echo (" : "); 
				?>
				<b>
				  <?php echo($tooltipy_post_type_name); _e(' is not selected as a tooltip. (only selected post types will be considered as tooltips)','tooltipy-lang'); ?>
				</b>
			</div>
			<?php
		}
 }

function bt_kw_match_all_display(){
	$options = get_option( 'bluet_kw_settings' );
?>
	<input type="checkbox" 	id="bt_kw_match_all_id" 	name="bluet_kw_settings[bt_kw_match_all]" <?php if(!empty($options['bt_kw_match_all']) and $options['bt_kw_match_all']=='on') echo 'checked'; ?>/>
		<label for="bt_kw_match_all_id"><?php _e('Match all occurrences','tooltipy-lang'); ?></label><br>
<?php

 }

function bt_kw_hide_title_display(){
	$options = get_option( 'bluet_kw_settings' );
?>
	<input type="checkbox" 	id="bt_kw_hide_title_id" 	name="bluet_kw_settings[bt_kw_hide_title]" <?php if(!empty($options['bt_kw_hide_title']) and $options['bt_kw_hide_title']=='on') echo 'checked'; ?>/>
		<label for="bt_kw_hide_title_id"><?php _e('Hide the tooltips title','tooltipy-lang'); ?></label><br>
<?php
	 
}

function bt_kw_animation_type_display(){
	$options = get_option('bluet_kw_settings');
	
	$anim_type="none";
	if(!empty($options['bt_kw_animation_type'])){
		$anim_type=$options['bt_kw_animation_type'];
	}
	
	$anim_speed="normal";
	if(!empty($options['bt_kw_animation_speed'])){
		$anim_speed=$options['bt_kw_animation_speed'];
	}
	
	$anims=array(
			"bounce","bounceIn","bounceInLeft","bounceInRight","bounceInDown","bounceInUp",
			"fadeIn","fadeInLeft","fadeInLeftBig","fadeInRight","fadeInRightBig","fadeInUp","fadeInUpBig",
			"flash",
			"flip","flipInX","flipInY",
			"lightSpeedIn",
			"pulse",				
			"rollIn",
			"rotateIn","rotateInDownLeft","rotateInDownRight","rotateInUpLeft","rotateInUpRight",
			"slideInDown","slideInLeft","slideInRight","slideInUp",
			"swing","shake","tada",
			"wobble",
			"zoomIn","zoomInDown","zoomInLeft","zoomInRight","zoomInUp"
			);

?>
	<select id="select_anim" name="bluet_kw_settings[bt_kw_animation_type]" >	
        <optgroup label="Select an animation">
				<option value="none" <?php if("none"==$anim_type){ echo("selected");} ?> style="color: red;" ><?php _e("None",'tooltipy-lang'); ?></option>
			<?php
				foreach($anims as $anim){
					?>
					<option value="<?php echo($anim); ?>" <?php if($anim==$anim_type){ echo("selected");} ?>><?php echo($anim); ?></option>
					<?php
				}
			?>			  
        </optgroup>				
      </select>
  
		<label for='select_speed_fast'><?php _e('Fast','tooltipy-lang'); ?></label>
			<input onchange="select_speed.value=select_speed_fast.value" type="radio" id="select_speed_fast"	name="bluet_kw_settings[bt_kw_animation_speed]" value="kttg_fast" <?php if("kttg_fast"==$anim_speed) echo 'checked'; ?> />
		<label for='select_speed_normal'><?php _e('Normal','tooltipy-lang'); ?></label>
			<input onchange="select_speed.value=select_speed_normal.value" type="radio" id="select_speed_normal"	name="bluet_kw_settings[bt_kw_animation_speed]" value="kttg_normal" <?php if("kttg_normal"==$anim_speed) echo 'checked'; ?> />
		<label for='select_speed_slow'><?php _e('Slow','tooltipy-lang'); ?></label>
			<input onchange="select_speed.value=select_speed_slow.value" type="radio" id="select_speed_slow"	name="bluet_kw_settings[bt_kw_animation_speed]" value="kttg_slow" <?php if("kttg_slow"==$anim_speed) echo 'checked'; ?> />
		
		<input type="hidden" id="select_speed" value=""/>

	  <div id="demo_div" style="width: 200px; text-align: center; font-size: 30px;"><?php _e("click to see a DEMO",'tooltipy-lang'); ?></div>
<script type="text/javascript">
	jQuery("#select_speed").change(function(){
		jQuery("#demo_div").removeClass().addClass("animated "+jQuery(this).val()+" "+jQuery("#select_anim").val());
	});
	
	jQuery("#select_anim").change(function(){
		jQuery("#demo_div").removeClass().addClass("animated "+jQuery("#select_speed").val()+" "+jQuery(this).val());
	});
	
	jQuery("#demo_div").mouseenter(function(){
		jQuery("#demo_div").removeClass();
	});
	jQuery("#demo_div").click(function(){
		jQuery("#demo_div").removeClass().addClass("animated "+jQuery("#select_anim").val()+" "+jQuery("#select_speed").val());
	});
</script>

<?php
}
function bt_kw_position_display(){
	$options = get_option( 'bluet_kw_settings' );
?>
	<input type="radio"	name="bluet_kw_settings[bt_kw_position]" value="top" <?php if($options['bt_kw_position']=="top") echo 'checked'; ?>/><?php _e('Top','tooltipy-lang'); ?><br>
	<input type="radio"	name="bluet_kw_settings[bt_kw_position]" value="bottom" <?php if($options['bt_kw_position']=="bottom") echo 'checked'; ?>/><?php _e('Bottom','tooltipy-lang'); ?><br>
	<input type="radio"	name="bluet_kw_settings[bt_kw_position]" value="right" <?php if($options['bt_kw_position']=="right") echo 'checked'; ?>/><?php _e('Right','tooltipy-lang'); ?><br>
	<input type="radio"	name="bluet_kw_settings[bt_kw_position]" value="left" <?php if($options['bt_kw_position']=="left") echo 'checked'; ?>/><?php _e('Left','tooltipy-lang'); ?><br>
<?php
	 
 }
/************************/
function bluet_kw_render_settings_page() {
	?>
		<div id="bluet-general" class="wrap" >
			
			<?php
				$kttg_infos=get_plugin_data(dirname(__FILE__).'/index.php');
				$kttg_name=$kttg_infos['Name'];
				$kttg_version=$kttg_infos['Version'];
			?>
			<h2><?php _e('KeyWords Settings','tooltipy-lang'); ?></h2><span><?php echo('<b>'.$kttg_name.'</b> (v'.$kttg_version.')');?></span>
			
			<?php settings_errors();?>				
				<h2 class="nav-tab-wrapper">
					<a class="nav-tab" id="bluet_style_tab" data-tab="bluet-section-style" href="#style_tab"><?php _e('Style','tooltipy-lang'); ?></a>
					<a class="nav-tab" id="bluet_settings_tab" data-tab="bluet-section-settings" href="#options_tab"><?php _e('Options','tooltipy-lang'); ?></a>					
					<a class="nav-tab" id="bluet_glossary_tab" data-tab="bluet-section-glossary" href="#glossary_tab"><?php _e('Glossary','tooltipy-lang'); ?></a>
					<a class="nav-tab" id="bluet_advanced_tab" data-tab="bluet-section-advanced" href="#advanced_tab"><?php _e('Advanced','tooltipy-lang'); ?></a>					
 					<a class="nav-tab" id="bluet_excluded_tab" data-tab="bluet-section-excluded" href="#excluded_tab"><?php _e('Excluded posts','tooltipy-lang');?></a>
					
					<a class="nav-tab" target="_blank" style="background-color: antiquewhite;" href="https://wordpress.org/support/plugin/bluet-keywords-tooltip-generator" ><?php _e('Help ?','tooltipy-lang');?></a>
					<a class="nav-tab rate-tooltipy" target="_blank" style="background-color: aliceblue;" href="https://wordpress.org/support/view/plugin-reviews/bluet-keywords-tooltip-generator" ><?php _e('Rate','tooltipy-lang');?></a>
					<style>
						.rate-tooltipy:after{
							content: " \f155\f155\f155\f155\f155";
    						font-family: "dashicons";
    						color: #e6b800;
						}
					</style>
				</h2>
			<form method="post" action="options.php">
			<?php
				// Render the settings for the settings section identified as 'Footer Section'
				settings_fields( 'settings_group' );
				
				//render sections here	
			?><div id="bluet-sections-div"><?php
				//style section
				?><div class="bluet-section" id="bluet-section-style" name="style_tab"><?php
					tltpy_template("admin/style");						
				?></div><?php

				?><div class="bluet-section" id="bluet-section-settings" name="options_tab"><?php
						do_settings_sections( 'my_keywords_settings' );		
				?></div><?php

				//glossary settings
				?><div class="bluet-section" id="bluet-section-glossary" name="glossary_tab"><?php
						do_settings_sections( 'my_keywords_glossary_settings' );	
				?></div><?php

				?><div class="bluet-section" id="bluet-section-advanced" name="advanced_tab"><?php
						do_settings_sections( 'my_keywords_advanced_page' );
				?></div><?php				

				?><div class="bluet-section" id="bluet-section-excluded" name="excluded_tab"><?php
					tltpy_template("admin/exclude");
				?>
					</div>
				</div>
			</div> <!-- end  bluet-sections-div -->
			
				<?php submit_button( __('Save Settings','tooltipy-lang'), 'primary'); ?> 

			</form>	

		</div>
<?php 

}


function bluet_kw_fetch_excluded_posts(){
//returns the list of the posts being excluded from keywords matching

	
	//get list if excluded posts
	$tooltipy_excluded_posts = get_option("tooltipy_excluded_posts_from_matching");

	
	return $tooltipy_excluded_posts;
}