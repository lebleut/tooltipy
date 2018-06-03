<?php
defined('ABSPATH') or die("No script kiddies please!");

/* enqueue js functions  for settings admin only*/
function kttg_pro_load_admin_scripts() {
	//
}
add_action( 'admin_head', 'kttg_pro_load_admin_scripts' );

add_action( 'admin_init',function () {

/******************* sections */
	// 1st section
	add_settings_section(
		'advanced_section',					
		__('Advance settings for KTTG :','bluet-kw'),					
		'bluet_kw_advanced_display',		
		'my_keywords_advanced_page'				
	);
	
/******************* fields */
	//
    add_settings_field( 
        'kttg_cover_areas',  //classes to cover                
        __('Cover CSS classes','bluet-kw'),             
        'bluet_kw_cover_areas_display',      
        'my_keywords_advanced_page',                
        'advanced_section'                  
    );

    add_settings_field( 
        'kttg_cover_tags',  //tags to cover                
        __('Cover HTML TAGS','bluet-kw'),             
        'bluet_kw_cover_tags_display',      
        'my_keywords_advanced_page',                
        'advanced_section'                  
    );
	//
    add_settings_field( 
        'kttg_exclude_areas',  //classes to exclude                
        __('Exclude CSS classes','bluet-kw'),             
        'bluet_kw_exclude_areas_display',      
        'my_keywords_advanced_page',                
        'advanced_section'                  
    );

	//exclude links
	add_settings_field( 
		'kttg_exclude_anchor_tags', 					
		__('Exclude links','bluet-kw')." ?", 			
		'bluet_kw_exclude_anchor_tags_display', 		
		'my_keywords_advanced_page',				
		'advanced_section'					
	);

	//exclude headings
	add_settings_field( 
		'kttg_exclude_heading_tags', 					
		__('Exclude Headings','bluet-kw')." ?", 			
		'bluet_kw_exclude_heading_tags_display', 		
		'my_keywords_advanced_page',				
		'advanced_section'					
	);

	//exclude common tags
	add_settings_field( 
		'kttg_exclude_common_tags',
		__('Exclude Common Tags','bluet-kw')." ?",
		'bluet_kw_exclude_common_tags_display',
		'my_keywords_advanced_page',
		'advanced_section'
	);

	// Define custom style field
	add_settings_field( 
		'bt_kw_adv_style', 					
		__('Advanced Style','bluet-kw'), 			
		'bluet_kw_adv_style_display', 		
		'my_keywords_advanced_page',				
		'advanced_section'					
	);	

	//
    add_settings_field( 
        'kttg_fetch_all_keywords',                  
        __('Load all keywords','bluet-kw'),             
        'bluet_kw_fetch_all_keywords_display',      
        'my_keywords_advanced_page',                
        'advanced_section'                  
    );

    //kttg_custom_events
   add_settings_field( 
        'kttg_custom_events',                  
        __('Events to fetch','bluet-kw'),             
        'kttg_custom_events_display',      
        'my_keywords_advanced_page',                
        'advanced_section'                  
    );

	// tooltipy_prevent_plugins_filters
	add_settings_field(
		'tooltipy_prevent_plugins_filters',
		__('Prevent other plugins filters','bluet-kw'),
		'bt_tooltipy_prevent_plugins_filters_display',
		'my_keywords_advanced_page',
		'advanced_section'
	);
	
/******************* registrations */
	//for settings options
	register_setting(
		'settings_group',					// The name of the group of settings
		'bluet_kw_advanced'					// The name of the actual option (or setting)
	);
	
	
}); // end bluet_kw_settings_registration
/*-----------------------------------------------*
 * Callbacks
/*-----------------------------------------------*

/**
 * Renders the content of the options page for the 	
*/
/*function bluet_kw_render_advanced_page(){
	?>
		<div id="bluet-general" class="wrap" >
			<h2><?php _e('KeyWords Avanced','bluet-kw'); ?></h2>
			<?php settings_errors();?>				
			<form method="post" action="options.php">
			<div>
			<input type="hidden" name="action" value="bluet_saving_advanced_settings">
			<?php
				// Render the settings for the settings section identified as 'Footer Section'
				settings_fields( 'advanced_group' );
				
				//render sections here	
				do_settings_sections('my_keywords_advanced_page');		
			?>
			</div> <!-- end  bluet-sections-div -->
			
				<?php submit_button( __('Save Settings','bluet-kw'), 'primary'); ?> 

			</form>	

		</div>
	<?php 

}*/
function bluet_kw_advanced_display(){

	echo('<div id="keywords-advanced">'.__('Advanced space','bluet-kw').'.</div>');
}
/*
function bluet_kw_supported_plugins_display(){
	$options = get_option('bluet_kw_advanced');
    if(!empty($options['bt_kw_supported_plugins'])){
		$supported_plugins=$options['bt_kw_supported_plugins'];
	}
	?>
	<input id="bluet_support_bbpress" type="checkbox" name="bluet_kw_advanced[bt_kw_supported_plugins][bbpress]" <?php if(!empty($supported_plugins['bbpress']) and $supported_plugins['bbpress']=='on') echo "checked"; ?>><label for="bluet_support_bbpress">Support bbPress</label>
	<br><input id="bluet_support_woo" type="checkbox" name="bluet_kw_advanced[bt_kw_supported_plugins][wooc]" <?php if(!empty($supported_plugins['wooc']) and $supported_plugins['wooc']=='on') echo "checked"; ?>><label for="bluet_support_woo">Support WooCommerce</label>
	<?php
}
*/
function bluet_kw_adv_style_display(){
	$options = get_option('bluet_kw_advanced');

	$custom_style_sheet=$options['bt_kw_adv_style']['custom_style_sheet'];
	if(!empty($options['bt_kw_adv_style']['apply_custom_style_sheet'])){
		$apply_custom_style_sheet=$options['bt_kw_adv_style']['apply_custom_style_sheet'];
	}else{
		$apply_custom_style_sheet=false;
	}

	?>
	<input id="bluet_apply_custom_style_sheet" type="checkbox" name="bluet_kw_advanced[bt_kw_adv_style][apply_custom_style_sheet]" <?php if($apply_custom_style_sheet) echo "checked"; ?>>
	<label for="bluet_apply_custom_style_sheet"><?php _e('Apply custom style sheet','bluet-kw'); ?></label>
	<br><input style="min-width:250px;" id="bluet_custom_style_sheet" type="text" name="bluet_kw_advanced[bt_kw_adv_style][custom_style_sheet]" value="<?php echo($custom_style_sheet);?>" placeholder="CSS URL Here">
	<?php
}

function bluet_kw_exclude_anchor_tags_display(){
	//exclude links
	$options = get_option('bluet_kw_advanced');

	$exclude_anchor_tags=false;

	if(!empty($options['kttg_exclude_anchor_tags'])){
		$exclude_anchor_tags=$options['kttg_exclude_anchor_tags'];
	}

	?>
	<input id="bluet_exclude_anchor_tags" type="checkbox" name="bluet_kw_advanced[kttg_exclude_anchor_tags]" <?php if(!empty($exclude_anchor_tags) and $exclude_anchor_tags=="on" ) echo "checked"; ?>>
	<label for="bluet_exclude_anchor_tags"><?php _e('Links','bluet-kw'); ?></label>
	<?php

}

function bluet_kw_exclude_heading_tags_display(){
	//exclude headings
	$options = get_option('bluet_kw_advanced');

	$exclude_heading_tags=false;

	if(!empty($options['kttg_exclude_heading_tags'])){
		$exclude_heading_tags=$options['kttg_exclude_heading_tags'];
	}
	echo("<div id='kttg_exclude_headings_zone'>");
	for($i=1;$i<7;$i++){
	?>	
		<input id="bluet_exclude_heading_H<?php echo($i); ?>" type="checkbox" name="bluet_kw_advanced[kttg_exclude_heading_tags][h<?php echo($i); ?>]" <?php if(!empty($exclude_heading_tags["h".$i]) and $exclude_heading_tags["h".$i]=="on") echo "checked"; ?>>
		<label for="bluet_exclude_heading_H<?php echo($i); ?>"><h<?php echo($i); ?>>H<?php echo($i); ?></h<?php echo($i); ?>></label>
		<br>
	<?php
	}
	echo("</div>");
}

/**
* Excludes some common tags callback
*/
function bluet_kw_exclude_common_tags_display(){
	//exclude tags
	$options = get_option('bluet_kw_advanced');

	$exclude_common_tags = false;

	if(!empty($options['kttg_exclude_common_tags'])){
		$exclude_common_tags = $options['kttg_exclude_common_tags'];
	}
	echo("<div id='kttg_exclude_tags_zone'>");


	$tags = array( 'strong', 'b', 'abbr', 'button', 'dfn', 'em', 'i', 'label' );

	foreach ($tags as $tagName) {
		?>	
			<input id="bluet_exclude_tag_<?php echo($tagName); ?>" type="checkbox" name="bluet_kw_advanced[kttg_exclude_common_tags][<?php echo($tagName); ?>]" <?php if(!empty($exclude_common_tags[$tagName]) and $exclude_common_tags[$tagName]=="on") echo "checked"; ?>>
			<label for="bluet_exclude_tag_<?php echo($tagName); ?>">
				<&zwnj;<?php echo($tagName); ?>&zwnj;/>
			</label>
			<br>
		<?php
	}
	echo("</div>");
}


function bluet_kw_fetch_all_keywords_display(){
    $options = get_option('bluet_kw_advanced');
 
    if(!empty($options['kttg_fetch_all_keywords'])){
        $fetch_all_keywords=$options['kttg_fetch_all_keywords'];
    }
 
    ?>
        <input id="bluet_fetch_all_keywords" type="checkbox" name="bluet_kw_advanced[kttg_fetch_all_keywords]" <?php if(!empty($fetch_all_keywords) and $fetch_all_keywords=='on') echo "checked"; ?>>
        <label for="bluet_fetch_all_keywords">(<?php _e('use only if needed to load all keywords per page','bluet-kw');?>)</label>
    <?php
}

function kttg_custom_events_display(){
	    $options = get_option('bluet_kw_advanced');
 
    if(!empty($options['kttg_custom_events'])){
        $kttg_custom_events=$options['kttg_custom_events'];
    }else{
    	$kttg_custom_events="";
    }
 
    ?>
		<input type="text" id="kttg_custom_events_id" style="min-width:300px;" placeholder="<?php _e('Events names saparated with \',\'','bluet-kw'); ?>" name="bluet_kw_advanced[kttg_custom_events]" value="<?php echo($kttg_custom_events); ?>">
     <?php
}
function bluet_kw_cover_tags_display(){
	$options = get_option('bluet_kw_advanced');
	$kttg_cover_tags = '';
	if(!empty($options['kttg_cover_tags'])){
		$kttg_cover_tags = $options['kttg_cover_tags'];
	}


	?>
	<div class="easy_tags">		
		<div class="easy_tags-content" onclick="jQuery('#bluet_cover_tags_id').focus()"> <!-- content -->
			<div class="easy_tags-list tagchecklist" id="cover_tags_list" >	<!-- list before field -->
			</div>
			
			<input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_tags_id" placeholder="<?php _e('HTML tag ...','bluet-kw'); ?>"> <!-- field -->
				<input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_cover_tags]" id="cover_tags_send" value="<?php echo($kttg_cover_tags);?>" > <!-- hidden text to send -->
		</div>
		<input class="easy_tags-add button tagadd" type="button" value="<?php _e('Add'); ?>" id="cover_tag_add" > <!-- add button -->
	</div>
	
	<p style="color:green;"><?php _e('Choose HTML TAGS (like h1, h2, strong, p, ... ) to cover with tooltips','bluet-kw'); ?></p>

	<?php
}

function bluet_kw_cover_areas_display(){
	$options = get_option('bluet_kw_advanced');
	$kttg_cover_areas='';
	if(!empty($options['kttg_cover_areas'])){
		$kttg_cover_areas=$options['kttg_cover_areas'];
	}


	?>
	<div class="easy_tags">		
		<div class="easy_tags-content" onclick="jQuery('#bluet_cover_areas_id').focus()"> <!-- content -->
			<div class="easy_tags-list tagchecklist" id="cover_areas_list" >	<!-- list before field -->
			</div>
			
			<input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_cover_areas_id" placeholder="<?php _e('class ...','bluet-kw'); ?>"> <!-- field -->
				<input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_cover_areas]" id="cover_areas_send" value="<?php echo($kttg_cover_areas);?>" > <!-- hidden text to send -->
		</div>
		<input class="easy_tags-add button tagadd" type="button" value="<?php _e('Add'); ?>" id="cover_class_add" > <!-- add button -->
	</div>
	
	<p style="color:green;"><?php _e('Choose CSS classes to cover with tooltips','bluet-kw'); ?></p>

	<script>
	jQuery(document).ready(function(){

		field=easy_tags.construct(" ");

		field.init(".easy_tags");		
		field.fill_classes(".easy_tags");

	});
	</script>
	<p><b>NB : </b>
		<i>
		<?php _e('Please avoid overlapped classes !','bluet-kw'); ?><br> 
		<?php _e('If you leave Classes AND Tags blank the whole page will be affected','bluet-kw'); ?>
	</i></p>
	

	<?php
}
function bluet_kw_exclude_areas_display(){
	$options = get_option('bluet_kw_advanced');
	$kttg_exclude_areas='';
	if(!empty($options['kttg_exclude_areas'])){
		$kttg_exclude_areas=$options['kttg_exclude_areas'];
	}
	
	?>
	<div class="easy_tags">
		<div class="easy_tags-content" onclick="jQuery('#bluet_exclude_areas_id').focus()">
			<div class="easy_tags-list tagchecklist" id="exclude_areas_list" >
			</div>
			
			<input class="easy_tags-field" type="text" style="max-width:250px;" id="bluet_exclude_areas_id" placeholder="<?php _e('class ...','bluet-kw'); ?>" >
				<input class="easy_tags-to_send" type="hidden" name="bluet_kw_advanced[kttg_exclude_areas]" id="exclude_areas_send" value="<?php echo($kttg_exclude_areas);?>" >
		</div>
		<input class="easy_tags-add button tagadd" type="button" value="<?php _e('Add'); ?>" id="exclude_class_add" >
	</div>

	<p style="color:red;"><?php _e('Choose CSS classes to exclude','bluet-kw'); ?></p>

	<?php
}

function unset_elem_where($tab,$value){
	foreach($tab as $k=>$elem){
		if($elem==$value){
			unset($tab[$k]);
		}
	}
	return $tab;
}


function bt_tooltipy_prevent_plugins_filters_display(){
		$options = get_option( 'bluet_kw_advanced' ); //to get the ['bt_kw_fetch_mode']
	?>
	<input type="checkbox" name="bluet_kw_advanced[prevent_plugins_filters]" <?php if(!empty($options['prevent_plugins_filters']) and $options['prevent_plugins_filters']) echo 'checked'; ?> /> Prevent any 3rd party plugin to filter or change the keywords content
	<?php
}
