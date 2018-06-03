<?php
defined('ABSPATH') or die("No script kiddies please!");

add_action( 'admin_init',function () {

	// section
	add_settings_section(
		'glossary_section',					// The ID to use for this section in attribute tags
		__('Glossary settings :','tooltipy-lang'),					// The title of the section rendered to the screen
		'bluet_kw_glossary_display',		// The function used to render the options for this section
		'my_keywords_glossary_settings'				// The ID of the page on which this section is rendered
	);
	
	/* glossari fields*/
	add_settings_field( 
		'kttg_kws_per_page', 					// The ID (or the name) of the field
		__('Keywords per page','tooltipy-lang'), 			// The text used to label the field
		'tltpy_glossary_kws_per_page_display', 		// The callback function used to render the field
		'my_keywords_glossary_settings',				// The page on which we'll be rendering this field
		'glossary_section'					// The section to which we're adding the setting
	);
	
	add_settings_field( 
		'kttg_glossary_text', 					// The ID (or the name) of the field
		__('Glossary page labels','tooltipy-lang'), 			// The text used to label the field
		'tltpy_glossary_text_display', 		// The callback function used to render the field
		'my_keywords_glossary_settings',				// The page on which we'll be rendering this field
		'glossary_section'					// The section to which we're adding the setting
	);
	// Define view glossary thumbnail
    add_settings_field( 
        'tltpy_glossary_show_thumb',                    
        __('Thumbnails','tooltipy-lang'),            
        'bt_kw_show_glossary_show_thumb',         
        'my_keywords_glossary_settings',                
        'glossary_section'
    );      
 
		 // Define view glossary page field
    add_settings_field( 
        'bluet_kttg_show_glossary_link',                    
        __('Glossary link page','tooltipy-lang'),            
        'bt_kw_show_glossary_link_display',         
        'my_keywords_glossary_settings',                // The page on which we'll be rendering this field
        'glossary_section'                  // The section to which we're adding the setting                    
    );
	
	/*for glossary options*/
	register_setting(
		'settings_group',					// The name of the group of settings
		'bluet_glossary_options'					// The name of the actual option (or setting)
	);
	
});

function tltpy_glossary_kws_per_page_display(){
	$options = get_option( 'bluet_glossary_options' );
	?>
	<input id="bt_kw_glossary_kpp" type="number" min="1" max="900" name="bluet_glossary_options[kttg_kws_per_page]" value="<?php echo $options['kttg_kws_per_page']; ?>" placeholder="<?php _e('ALL','tooltipy-lang');?>"> Keywords Per Page (leave blank for unlimited keywords per page)<?php
}
function tltpy_glossary_text_display(){
	$options = get_option( 'bluet_glossary_options' );

	$label_select_a_family		= array_key_exists('kttg_glossary_text_select_a_family', $options['kttg_glossary_text']) ? $options['kttg_glossary_text']['kttg_glossary_text_select_a_family'] : "";
	$label_select_all_families 	= array_key_exists('kttg_glossary_text_select_all_families', $options['kttg_glossary_text']) ? $options['kttg_glossary_text']['kttg_glossary_text_select_all_families'] : "";

	_e('<b>ALL</b> label','tooltipy-lang'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_all]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_all']; ?>" placeholder="<?php _e('ALL','tooltipy-lang');?>"><br>
	<?php
	_e('<b>Previous</b> label','tooltipy-lang'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_previous]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_previous']; ?>" placeholder="<?php _e('Previous','tooltipy-lang');?>"><br>
	<?php
	_e('<b>Next</b> label','tooltipy-lang'); ?> : <input  type="text" name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_next]" value="<?php echo $options['kttg_glossary_text']['kttg_glossary_text_next']; ?>" placeholder="<?php _e('Next','tooltipy-lang');?>"><br>
	<?php

	_e('<b>Select a family</b> label','tooltipy-lang');
	?> : 
	<input  
		type="text"
		name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_select_a_family]"
		value="<?php echo $label_select_a_family; ?>"
		placeholder="<?php _e('Select a family','tooltipy-lang');?>"
	><br>
	<?php

	_e('<b>All families</b> label','tooltipy-lang'); ?> : 
	<input  
		type="text"
		name="bluet_glossary_options[kttg_glossary_text][kttg_glossary_text_select_all_families]"
		value="<?php echo $label_select_all_families; ?>"
		placeholder="<?php _e('All families','tooltipy-lang');?>"
	><br>
	<?php
	
}

function bluet_kw_glossary_display(){
	_e('Choose settings for your glossary.','tooltipy-lang');
}

function bt_kw_show_glossary_link_display(){
    //$options = get_option( 'bluet_kw_settings' );
    $glossary_options = get_option( 'bluet_glossary_options' );
 
    ?>
    <div>
        <label for="bt_kw_show_glossary_link_id"><?php _e('Add glossary link page in the tooltips footer','tooltipy-lang'); ?></label>
        <input type="checkbox"     id="bt_kw_show_glossary_link_id"  name="bluet_glossary_options[bluet_kttg_show_glossary_link]" <?php if(!empty($glossary_options['bluet_kttg_show_glossary_link']) and $glossary_options['bluet_kttg_show_glossary_link']=='on') echo 'checked'; ?> />
 
    </div>
    
    <div>
            <label for="bt_kw_glossary_page_link"><?php _e('Glossary page link','tooltipy-lang'); ?></label>
            <input  type="text" id="bt_kw_glossary_page_link" name="bluet_glossary_options[kttg_link_glossary_page_link]" value="<?php echo $glossary_options['kttg_link_glossary_page_link']; ?>" placeholder="http://...">
    </div>
    
    <div>
        <label for="bt_kw_glossary_link_label_id"><?php _e('Glossary link label','tooltipy-lang'); ?></label>        
        <input  type="text" id="bt_kw_glossary_link_label_id" name="bluet_glossary_options[kttg_link_glossary_label]" value="<?php echo $glossary_options['kttg_link_glossary_label']; ?>" placeholder="<?php _e('View glossary','tooltipy-lang');?>">
 
    </div>
 
    
    <?php
}

function bt_kw_show_glossary_show_thumb(){
    $glossary_options = get_option('bluet_glossary_options');
    ?>
    <div>
        <label for="bt_kw_show_glossary_thumb_id"><?php _e('Show thumbnails on the glossary page','tooltipy-lang'); ?></label>
        <input type="checkbox"     id="bt_kw_show_glossary_thumb_id"  name="bluet_glossary_options[tltpy_glossary_show_thumb]" <?php if(!empty($glossary_options['tltpy_glossary_show_thumb']) and $glossary_options['tltpy_glossary_show_thumb']=='on') echo 'checked'; ?> />
 
    </div>
 
 
    <?php
}
